<?php
// MODO DE DEPURACIÓN ACTIVADO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// --- Dependencias existentes ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

require __DIR__ . '/vendor/autoload.php';

require_once 'config.php';

// --- Usar todas las clases necesarias del SDK de PayPal ---
use PayPalHttp\HttpClient;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: login.php');
    exit;
}

// Tu lógica original para calcular el carrito y descuentos se mantiene intacta
$usuario_id = $_SESSION['user_id'];
$conn = conectar_db();
$subtotal = 0;
$productos_para_pedido_db = [];

$normal_product_ids = [];
$custom_cakes = [];
foreach ($_SESSION['cart'] as $id => $details) {
    if (isset($details['is_custom']) && $details['is_custom']) {
        $custom_cakes[$id] = $details;
    } else if (is_numeric($id)) {
        $normal_product_ids[] = (int)$id;
    }
}
if (!empty($normal_product_ids)) {
    $ids_string = implode(',', $normal_product_ids);
    $sql = "SELECT id, nombre, precio, puntos_generados FROM productos WHERE id IN ($ids_string)";
    $result_productos = $conn->query($sql);
    while ($producto = $result_productos->fetch_assoc()) {
        $item_info = $_SESSION['cart'][$producto['id']];
        $cantidad = $item_info['cantidad'];
        $subtotal += $cantidad * $producto['precio'];
        $productos_para_pedido_db[] = ['id' => $producto['id'], 'cantidad' => $cantidad, 'precio_unitario' => $producto['precio'], 'instrucciones' => $item_info['instrucciones']];
    }
}
foreach($custom_cakes as $id => $details) {
    $subtotal += $details['precio'] * $details['cantidad'];
    $productos_para_pedido_db[] = ['id' => null, 'cantidad' => $details['cantidad'], 'precio_unitario' => $details['precio'], 'instrucciones' => $details['instrucciones']];
}
$descuento = 0;
if (isset($_SESSION['cupon_aplicado'])) {
    $descuento = (float)$_SESSION['cupon_aplicado']['descuento'];
}
$total_final = max(0, $subtotal - $descuento);

// Tu lógica para crear el pedido en la base de datos se mantiene intacta
$pedido_id = null;
$conn->begin_transaction();
try {
    $estado_inicial = ($total_final == 0) ? 'Completado' : 'Pendiente';
    $stmt_pedido = $conn->prepare("INSERT INTO pedidos (usuario_id, total, estado) VALUES (?, ?, ?)");
    $stmt_pedido->bind_param("ids", $usuario_id, $total_final, $estado_inicial);
    $stmt_pedido->execute();
    $pedido_id = $conn->insert_id;
    $stmt_pedido->close();

    $stmt_items = $conn->prepare("INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, precio_unitario, instrucciones) VALUES (?, ?, ?, ?, ?)");
    foreach ($productos_para_pedido_db as $item) {
        $stmt_items->bind_param("iiids", $pedido_id, $item['id'], $item['cantidad'], $item['precio_unitario'], $item['instrucciones']);
        $stmt_items->execute();
    }
    $stmt_items->close();

    // =========================================================================
    // === SISTEMA DE PUNTOS - Sumar puntos por compra ===
    // =========================================================================
    $puntos_totales = 0;
    
    // Calcular puntos para productos normales
    if (!empty($normal_product_ids)) {
        $ids_string = implode(',', $normal_product_ids);
        $sql_puntos = "SELECT id, puntos_generados FROM productos WHERE id IN ($ids_string)";
        $result_puntos = $conn->query($sql_puntos);
        
        while ($producto_puntos = $result_puntos->fetch_assoc()) {
            $item_info = $_SESSION['cart'][$producto_puntos['id']];
            $puntos_totales += $producto_puntos['puntos_generados'] * $item_info['cantidad'];
        }
    }
    
    // Calcular puntos para pasteles personalizados (1 punto por cada $10 MXN)
    foreach ($custom_cakes as $id => $details) {
        $puntos_totales += floor(($details['precio'] * $details['cantidad']) / 10);
    }
    
    // Bonus por compra grande (adicional al sistema de recompensas)
    if ($subtotal >= 500) {
        $puntos_totales += 25; // Bonus extra por compra grande
    }
    
    // Actualizar puntos del usuario
    if ($puntos_totales > 0) {
        $stmt_puntos = $conn->prepare("UPDATE usuarios SET puntos_acumulados = COALESCE(puntos_acumulados, 0) + ? WHERE id = ?");
        $stmt_puntos->bind_param("ii", $puntos_totales, $usuario_id);
        $stmt_puntos->execute();
        $stmt_puntos->close();
        
        error_log("Puntos asignados: {$puntos_totales} puntos para usuario {$usuario_id}, pedido {$pedido_id}");
    }
    // =========================================================================

    if ($total_final == 0) {
        if (isset($_SESSION['cupon_aplicado'])) {
            $cupon_id_a_usar = $_SESSION['cupon_aplicado']['id'];
            $stmt_usar_cupon = $conn->prepare("UPDATE recompensas SET utilizado = 1 WHERE id = ?");
            $stmt_usar_cupon->bind_param("i", $cupon_id_a_usar);
            $stmt_usar_cupon->execute();
            $stmt_usar_cupon->close();
        }
        $conn->commit();
        $conn->close();

        // =========================================================================
        // === SISTEMA DE RECOMPENSAS AUTOMÁTICO PARA COMPRAS GRATUITAS ===
        // =========================================================================
        if ($subtotal >= 500) {
            generarRecompensa($usuario_id, $subtotal, $pedido_id);
        }
        // =========================================================================

        header("Location: pago_exitoso.php?token=free_order&orderID=" . $pedido_id); 
        exit();
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error al crear pedido: " . $e->getMessage());
    die("Hubo un error al registrar tu pedido. Por favor, inténtalo de nuevo.");
} finally {
    if($conn) $conn->close();
}


// --- Crear la Orden en PayPal ---
$environment = new SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);
$client = new PayPalHttpClient($environment);

$request = new OrdersCreateRequest();
$request->prefer('return=representation');
$request->body = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "reference_id" => "PEDIDO_" . $pedido_id,
        "amount" => [
            "value" => number_format($total_final, 2, '.', ''),
            "currency_code" => "MXN"
        ]
    ]],
    "application_context" => [
        "cancel_url" => "https://saboresdecristal.site/pago_fallido.php",
        "return_url" => "https://saboresdecristal.site/pago_exitoso.php"
    ]
];

try {
    $response = $client->execute($request);
    $approvalLink = "";
    foreach ($response->result->links as $link) {
        if ($link->rel == 'approve') {
            $approvalLink = $link->href;
            break;
        }
    }
    header("Location: " . $approvalLink);
    exit();

} catch (HttpException $e) {
    echo "<h1>Error de Conexión con PayPal</h1>";
    echo "<p>El servidor de PayPal respondió con un error. Esto usualmente significa que las credenciales son incorrectas.</p>";
    echo "<hr>";
    echo "<h3>Detalles Técnicos del Error:</h3>";
    echo "<pre style='background-color:#f5f5f5; padding:15px; border-radius:5px; border:1px solid #ccc;'>";
    echo "<strong>Status Code:</strong> " . htmlspecialchars($e->statusCode) . "\n\n";
    echo "<strong>Mensaje:</strong>\n";
    $message = json_decode($e->getMessage());
    echo json_encode($message, JSON_PRETTY_PRINT);
    echo "</pre>";
    echo "<hr>";
    echo "<p><strong>Acción recomendada:</strong> Verifica que el `PAYPAL_CLIENT_ID` y `PAYPAL_CLIENT_SECRET` en tu archivo `config.php` sean los correctos para la aplicación 'Sabores de Cristal Tienda' en modo **Sandbox**.</p>";
    echo "<a href='cart.php'>Volver al carrito</a>";

} catch (Exception $e) {
    die("Error general al procesar la compra: " . $e->getMessage());
}

// =========================================================================
// === FUNCIONES DEL SISTEMA DE RECOMPENSAS ===
// =========================================================================

function generarRecompensa($usuario_id, $subtotal, $pedido_id) {
    $conn = conectar_db();
    
    // Calcular 10% del subtotal
    $descuento = $subtotal * 0.10;
    
    // Generar código único
    $codigo_cupon = 'RECOMPENSA-' . strtoupper(bin2hex(random_bytes(4)));
    $descripcion = "Cupón del 10% de descuento por tu compra de $" . number_format($subtotal, 2);
    
    // Guardar en base de datos
    $stmt = $conn->prepare("INSERT INTO recompensas (usuario_id, codigo_cupon, descripcion, valor_descuento, utilizado) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("issd", $usuario_id, $codigo_cupon, $descripcion, $descuento);
    
    if ($stmt->execute()) {
        // Obtener información del usuario para el correo
        $stmt_user = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
        $stmt_user->bind_param("i", $usuario_id);
        $stmt_user->execute();
        $user_info = $stmt_user->get_result()->fetch_assoc();
        $stmt_user->close();
        
        // Enviar correo
        enviarCorreoRecompensa($user_info['email'], $user_info['nombre'], $codigo_cupon, $descripcion, $descuento);
    }
    
    $stmt->close();
    $conn->close();
}

function enviarCorreoRecompensa($email_destinatario, $nombre_usuario, $cupon, $descripcion, $descuento) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emmaramirezvalencia@gmail.com';
        $mail->Password   = 'owpqxryjmqasvtyr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('emmaramirezvalencia@gmail.com', 'Sabores de Cristal');
        $mail->addAddress($email_destinatario, $nombre_usuario);
        $mail->isHTML(true);
        $mail->Subject = '¡Tienes una recompensa de Sabores de Cristal!';
        
        $mail->Body = "
            <h1>¡Felicidades, {$nombre_usuario}!</h1>
            <p>Por tu compra superior a $500 MXN, te hemos otorgado un cupón de descuento para tu próxima compra.</p>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 5px solid #CBA135; margin: 20px 0;'>
                <h3 style='color: #7B2D26; margin-top: 0;'>Tu Código de Descuento</h3>
                <p style='font-size: 24px; font-weight: bold; color: #CBA135;'>{$cupon}</p>
                <p><strong>Descuento:</strong> $" . number_format($descuento, 2) . " MXN (10% de tu compra)</p>
                <p><strong>Descripción:</strong> {$descripcion}</p>
            </div>
            
            <p><strong>¿Cómo usar tu cupón?</strong></p>
            <ol>
                <li>Agrega productos a tu carrito en nuestra tienda</li>
                <li>En la página del carrito, ingresa el código: <strong>{$cupon}</strong></li>
                <li>¡El descuento se aplicará automáticamente!</li>
            </ol>
            
            <p style='margin-top: 20px;'>¡Gracias por tu preferencia!</p>
            <p><strong>Equipo Sabores de Cristal</strong></p>
        ";
        
        $mail->send();
        error_log("Correo de recompensa enviado a: {$email_destinatario}");
    } catch (Exception $e) {
        error_log("Error enviando correo de recompensa: " . $mail->ErrorInfo);
    }
}
?>