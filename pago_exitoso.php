<?php
session_start();

// --- INCLUIR TODAS LAS DEPENDENCIAS NECESARIAS ---
require __DIR__ . '/vendor/autoload.php';
require_once 'config.php';
require_once 'fpdf/fpdf.php'; // Incluimos la librería FPDF

// --- INCLUIR PHPMailer EXPLÍCITAMENTE ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalHttp\HttpException;

// --- Función para generar el PDF (DISEÑO FINAL OPTIMIZADO) ---
/**
 * Genera un comprobante de pago en formato PDF con el diseño de marca de Sabores de Cristal.
 * @param int $pedido_id ID del pedido.
 * @param string $paypal_id ID de la transacción de PayPal.
 * @param float $total Monto total pagado.
 * @param array $usuario_info Datos del usuario (nombre, email).
 * @param array $items_pedido Lista de productos del pedido.
 */
function generarComprobantePDF($pedido_id, $paypal_id, $total, $usuario_info, $items_pedido) {
    // Definición de Colores de la paleta
    $negro = [28, 28, 28]; // #1C1C1C
    $dorado = [203, 161, 53]; // #CBA135
    $crema = [250, 243, 224]; // #FAF3E0
    $burdeos = [123, 45, 38]; // #7B2D26
    $gris_oscuro_texto = [42, 42, 42]; // #2a2a2a
    $borde_gris_claro = [220, 220, 220]; // Para bordes sutiles

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);

    // 1. ENCABEZADO: LOGO, Nombre de la Empresa y Borde Superior
    // Borde superior dorado
    $pdf->SetDrawColor($dorado[0], $dorado[1], $dorado[2]);
    $pdf->SetLineWidth(0.8); 
    $pdf->Line(15, 15, 195, 15); 
    $pdf->Ln(5);

    // --- Colocar el logo y el nombre de la empresa sin solapamientos ---
    // Posición para el logo: X=15, Y=20, Ancho=40mm
    $pdf->Image('Images/LOGO-min.png', 15, 15, 20); 
    
    // Posición para el nombre de la empresa: Alineado a la derecha, más abajo
    $pdf->SetFont('Arial', 'B', 24); 
    $pdf->SetTextColor($burdeos[0], $burdeos[1], $burdeos[2]);
    // El 0, 0 en Cell significa que no avanza la X ni la Y, lo posicionamos con SetXY
    $pdf->SetXY(150, 25); // Ajuste manual de la posición X, Y para evitar solapamiento
    $pdf->Cell(45, 10, utf8_decode('Sabores de Cristal'), 0, 0, 'R');
    $pdf->Ln(25); // Espacio después del encabezado


    // 2. TÍTULO DEL DOCUMENTO
    $pdf->SetFont('Arial', 'B', 28); 
    $pdf->SetTextColor($negro[0], $negro[1], $negro[2]);
    $pdf->Cell(0, 15, utf8_decode('COMPROBANTE DE PAGO'), 0, 1, 'C'); 
    $pdf->Ln(10);
    
    // Línea divisoria elegante bajo el título
    $pdf->SetDrawColor($dorado[0], $dorado[1], $dorado[2]);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(15); // Más espacio después de la línea divisoria


    // 3. SECCIÓN: DETALLES DE LA TRANSACCIÓN
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor($dorado[0], $dorado[1], $dorado[2]);
    $pdf->Cell(0, 10, utf8_decode('Detalles de la Transacción'), 0, 1, 'L');
    $pdf->SetTextColor($gris_oscuro_texto[0], $gris_oscuro_texto[1], $gris_oscuro_texto[2]);
    
    // --- Tabla para los detalles de la transacción ---
    $pdf->SetFillColor($crema[0], $crema[1], $crema[2]); // Fondo crema para la fila de etiquetas
    $pdf->SetDrawColor($borde_gris_claro[0], $borde_gris_claro[1], $borde_gris_claro[2]); // Borde sutil
    $pdf->SetLineWidth(0.1);

    // Fila 1: No. de Pedido y Fecha
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(45, 9, utf8_decode('  No. de Pedido:'), 'LTB', 0, 'L', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 9, '#' . $pedido_id, 'RTB', 0, 'L');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 9, '  Fecha:', 'LTB', 0, 'L', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(65, 9, date('d/m/Y H:i:s'), 'RTB', 1, 'L');
    
    // Fila 2: ID de PayPal
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(45, 9, utf8_decode('  ID de PayPal:'), 'LTB', 0, 'L', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(145, 9, $paypal_id, 'RTB', 1, 'L');
    $pdf->Ln(15); // Espacio después de la sección


    // 4. SECCIÓN: INFORMACIÓN DEL CLIENTE
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor($dorado[0], $dorado[1], $dorado[2]);
    $pdf->Cell(0, 10, utf8_decode('Información del Cliente'), 0, 1, 'L');
    
    // --- Tabla para los detalles del cliente ---
    $pdf->SetTextColor($gris_oscuro_texto[0], $gris_oscuro_texto[1], $gris_oscuro_texto[2]);
    
    // Fila 1: Nombre
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(45, 8, utf8_decode('  Nombre:'), 'LTB', 0, 'L', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(145, 8, utf8_decode($usuario_info['nombre']), 'RTB', 1, 'L');
    
    // Fila 2: Email
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(45, 8, '  Email:', 'LTB', 0, 'L', true);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(145, 8, $usuario_info['email'], 'RTB', 1, 'L');
    $pdf->Ln(15); // Espacio después de la sección
    
    
    // 5. SECCIÓN: ARTÍCULOS ADQUIRIDOS (TABLA MEJORADA)
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor($dorado[0], $dorado[1], $dorado[2]);
    $pdf->Cell(0, 10, utf8_decode('Artículos Adquiridos'), 0, 1, 'L');
    
    // Encabezados de la tabla de artículos
    $pdf->SetFillColor($burdeos[0], $burdeos[1], $burdeos[2]); // Fondo burdeos oscuro
    $pdf->SetTextColor(255, 255, 255); // Texto blanco
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(95, 8, utf8_decode('Descripción'), 1, 0, 'L', true);
    $pdf->Cell(25, 8, utf8_decode('Cantidad'), 1, 0, 'C', true);
    $pdf->Cell(35, 8, utf8_decode('P. Unitario'), 1, 0, 'R', true);
    $pdf->Cell(35, 8, 'Subtotal', 1, 1, 'R', true);

    // Filas de la tabla de artículos
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor($negro[0], $negro[1], $negro[2]);
    $subtotal_calculado_items = 0;
    
    foreach ($items_pedido as $item) {
        $line_height = 7;
        $total_linea = $item['precio_unitario'] * $item['cantidad'];
        $subtotal_calculado_items += $total_linea;
        
        // Bordes de celda: 'LR' para izquierda y derecha, el borde inferior se pone en el último Cell del loop
        $pdf->Cell(95, $line_height, utf8_decode($item['nombre_producto'] ?? 'Pastel Personalizado'), 'LR', 0, 'L');
        $pdf->Cell(25, $line_height, $item['cantidad'], 'R', 0, 'C');
        $pdf->Cell(35, $line_height, '$' . number_format($item['precio_unitario'], 2), 'R', 0, 'R');
        $pdf->Cell(35, $line_height, '$' . number_format($total_linea, 2), 'R', 1, 'R'); // 'R', 1 para cerrar la fila y añadir borde derecho
    }
    // Línea de cierre de la tabla de ítems (borde inferior de toda la tabla)
    $pdf->Cell(190, 0.1, '', 'T', 1, 'L'); // Solo borde superior (que actúa como el inferior de la tabla)
    $pdf->Ln(10); // Más espacio después de la tabla de artículos


    // 6. SECCIÓN: TOTALES
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor($negro[0], $negro[1], $negro[2]);
    
    // Subtotal de los items listados
    // Alineamos a la derecha ocupando la mayor parte del ancho
    $pdf->Cell(130, 8, '', 0, 0); 
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 8, 'Subtotal:', 0, 0, 'R');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(30, 8, '$' . number_format($subtotal_calculado_items, 2), 0, 1, 'R');
    
    // TOTAL PAGADO (Destacado y alineado con los items)
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetFillColor($dorado[0], $dorado[1], $dorado[2]); // Fondo dorado para el total
    $pdf->SetTextColor(255, 255, 255); // Texto blanco
    $pdf->Cell(200, 10, '', 0, 0); // Celda vacía para alinear
    $pdf->Cell(30, 10, 'TOTAL:', 1, 0, 'R', true); // Etiqueta TOTAL
    $pdf->Cell(30, 10, '$' . number_format($total, 2) . ' MXN', 1, 1, 'R', true); // Valor total
    $pdf->Ln(40); // Mucho espacio después del total

    // 7. MENSAJE DE AGRADECIMIENTO Y PIE DE PÁGINA
    $pdf->SetFont('Arial', 'I', 11);
    $pdf->SetTextColor($gris_oscuro_texto[0], $gris_oscuro_texto[1], $gris_oscuro_texto[2]);
    $pdf->Cell(0, 8, utf8_decode('¡Gracias por tu compra! Conserva este comprobante para cualquier aclaración.'), 0, 1, 'C');
    $pdf->Cell(0, 8, utf8_decode('Sabores de Cristal - Contacto: emmaramirezvalencia@gmail.com'), 0, 1, 'C');
    
    // Línea inferior corporativa
    $pdf->SetDrawColor($burdeos[0], $burdeos[1], $burdeos[2]);
    $pdf->SetLineWidth(0.8);
    $pdf->Line(15, 285, 195, 285);

    // Salida del PDF
    $pdf->Output('D', 'Comprobante_Pedido_' . $pedido_id . '.pdf');
    exit;
}
// =========================================================================
// === FUNCIONES DEL SISTEMA DE RECOMPENSAS Y PUNTOS ===
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
        
        // Enviar correo (MEJORADO)
        enviarCorreoRecompensa($user_info['email'], $user_info['nombre'], $codigo_cupon, $descripcion, $descuento);
    }
    
    $stmt->close();
    $conn->close();
}

function enviarCorreoRecompensa($email_destinatario, $nombre_usuario, $cupon, $descripcion, $descuento) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host        = 'smtp.gmail.com';
        $mail->SMTPAuth    = true;
        // !!! ASEGÚRATE DE USAR CREDENCIALES SEGURAS AQUÍ !!!
        $mail->Username    = 'emmaramirezvalencia@gmail.com';
        $mail->Password    = 'owpqxryjmqasvtyr'; 
        // --------------------------------------------------
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port        = 465;
        $mail->CharSet     = 'UTF-8';

        $mail->setFrom('emmaramirezvalencia@gmail.com', utf8_decode('Sabores de Cristal'));
        $mail->addAddress($email_destinatario, utf8_decode($nombre_usuario));
        $mail->isHTML(true);
        $mail->Subject = utf8_decode('🎉 ¡Tienes una Recompensa de Sabores de Cristal!');
        
        // Contenido del Email (HTML MEJORADO con estilos en línea)
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px;'>
                <div style='background-color: #7B2D26; color: #ffffff; padding: 20px; border-top-left-radius: 8px; border-top-right-radius: 8px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>🎉 ¡Felicidades, " . utf8_decode($nombre_usuario) . "!</h1>
                </div>
                
                <div style='padding: 30px;'>
                    <p style='font-size: 16px;'>Por tu reciente compra, te hemos otorgado un cupón de descuento como agradecimiento por tu lealtad.</p>
                    
                    <div style='background: #FAF3E0; padding: 25px; border-radius: 10px; border: 1px dashed #CBA135; text-align: center; margin: 30px 0;'>
                        <h3 style='color: #7B2D26; margin-top: 0; font-size: 18px;'>Tu Código de Descuento Especial</h3>
                        <p style='font-size: 32px; font-weight: bold; color: #CBA135; letter-spacing: 2px; margin: 10px 0;'>{$cupon}</p>
                        <p style='font-size: 14px;'><strong>Valor del Descuento:</strong> $" . number_format($descuento, 2) . " MXN (10% de tu compra anterior)</p>
                        <p style='font-style: italic; color: #555; font-size: 14px; margin-top: 5px;'>" . utf8_decode($descripcion) . "</p>
                    </div>
                    
                    <h4 style='color: #7B2D26; border-bottom: 1px solid #CBA135; padding-bottom: 5px;'>¿Cómo usar tu cupón?</h4>
                    <ol style='font-size: 15px; padding-left: 20px;'>
                        <li>Agrega tus productos favoritos a tu carrito.</li>
                        <li>En el carrito de compras, ingresa el código: <strong>{$cupon}</strong>.</li>
                        <li>¡El descuento se aplicará automáticamente en el total!</li>
                    </ol>
                    
                    <p style='margin-top: 40px; font-size: 15px;'>¡Esperamos verte pronto!</p>
                </div>
                
                <div style='background-color: #1C1C1C; color: #FAF3E0; padding: 15px; text-align: center; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; font-size: 12px;'>
                    <p style='margin: 0;'>" . utf8_decode('Equipo Sabores de Cristal') . " | Visita nuestra tienda en <a href='[ENLACE_A_TU_TIENDA]' style='color: #CBA135; text-decoration: none;'>SaboresdeCristal.com</a></p>
                </div>
            </div>
        ";
        
        $mail->send();
        error_log("Correo de recompensa enviado a: {$email_destinatario}");
    } catch (PHPMailerException $e) {
        error_log("Error enviando correo de recompensa: " . $mail->ErrorInfo);
    } catch (Exception $e) {
         error_log("Error general en enviarCorreoRecompensa: " . $e->getMessage());
    }
}

function procesarSistemaPuntos($pedido_id_db, $conn) {
    $puntos_totales = 0;

    // Obtener los productos del pedido para calcular puntos
    $stmt_items_puntos = $conn->prepare("SELECT producto_id, cantidad, precio_unitario FROM pedido_productos WHERE pedido_id = ?");
    $stmt_items_puntos->bind_param("i", $pedido_id_db);
    $stmt_items_puntos->execute();
    $result_items_puntos = $stmt_items_puntos->get_result();
    $items_pedido_puntos = $result_items_puntos->fetch_all(MYSQLI_ASSOC);
    $stmt_items_puntos->close();

    foreach ($items_pedido_puntos as $item) {
        if ($item['producto_id'] !== null) {
            // Producto normal: obtener puntos de la base de datos
            $stmt_producto_puntos = $conn->prepare("SELECT puntos_generados FROM productos WHERE id = ?");
            $stmt_producto_puntos->bind_param("i", $item['producto_id']);
            $stmt_producto_puntos->execute();
            $result_producto_puntos = $stmt_producto_puntos->get_result();
            if ($producto_puntos = $result_producto_puntos->fetch_assoc()) {
                $puntos_totales += $producto_puntos['puntos_generados'] * $item['cantidad'];
            }
            $stmt_producto_puntos->close();
        } else {
            // Pastel personalizado: 1 punto por cada $10 MXN
            $puntos_totales += floor(($item['precio_unitario'] * $item['cantidad']) / 10);
        }
    }

    // Obtener el total del pedido para el bonus
    $stmt_total = $conn->prepare("SELECT total, usuario_id FROM pedidos WHERE id = ?");
    $stmt_total->bind_param("i", $pedido_id_db);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $pedido_data = $result_total->fetch_assoc();
    $stmt_total->close();

    // Bonus por compra grande
    if ($pedido_data && $pedido_data['total'] >= 500) {
        $puntos_totales += 25;
    }

    // Actualizar puntos del usuario
    if ($puntos_totales > 0 && $pedido_data) {
        $stmt_update_puntos = $conn->prepare("UPDATE usuarios SET puntos_acumulados = COALESCE(puntos_acumulados, 0) + ? WHERE id = ?");
        $stmt_update_puntos->bind_param("ii", $puntos_totales, $pedido_data['usuario_id']);
        $stmt_update_puntos->execute();
        $stmt_update_puntos->close();
        
        error_log("Puntos PayPal asignados: {$puntos_totales} puntos para usuario {$pedido_data['usuario_id']}, pedido {$pedido_id_db}");
        
        return $puntos_totales;
    }
    
    return 0;
}

// --- Inicio del procesamiento ---

// Si se solicita la descarga del PDF (MEJORADA PARA INCLUIR ITEMS)
if (isset($_GET['download_pdf']) && isset($_GET['order_id'])) {
    $conn = conectar_db();
    $order_id_pdf = (int)$_GET['order_id'];
    
    // 1. Buscamos los datos del pedido y del usuario para el PDF
    $stmt = $conn->prepare("SELECT p.id, p.total, p.paypal_order_id, u.nombre, u.email FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = ?");
    $stmt->bind_param("i", $order_id_pdf);
    $stmt->execute();
    $pedido_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // 2. Buscamos los items del pedido
    // ASUMIMOS que la tabla `pedido_productos` puede no tener el nombre, por lo que se hace un JOIN a `productos`.
    $stmt_items = $conn->prepare("SELECT pp.cantidad, pp.precio_unitario, COALESCE(pr.nombre, 'Pastel Personalizado') as nombre_producto FROM pedido_productos pp LEFT JOIN productos pr ON pp.producto_id = pr.id WHERE pp.pedido_id = ?");
    $stmt_items->bind_param("i", $order_id_pdf);
    $stmt_items->execute();
    $items_pedido = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    if ($pedido_data && $items_pedido) {
        generarComprobantePDF(
            $pedido_data['id'], 
            $pedido_data['paypal_order_id'], 
            $pedido_data['total'], 
            ['nombre' => $pedido_data['nombre'], 'email' => $pedido_data['email']],
            $items_pedido
        );
    } else {
        error_log("Error al generar PDF: Pedido o items no encontrados para ID: {$order_id_pdf}");
    }
    $conn->close();
    exit;
}

// Si es la redirección desde PayPal
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    // La configuración de credenciales de PayPal debe estar en config.php
    $environment = new SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET);
    $client = new PayPalHttpClient($environment);
    $request = new OrdersCaptureRequest($token);
    $request->prefer('return=representation');
    
    $pedido_id_db = 0; // Inicializar para evitar errores en la redirección

    try {
        $response = $client->execute($request);

        if (($response->statusCode == 201 || $response->statusCode == 200) && $response->result->status == 'COMPLETED') {
            $orderID = $response->result->id;
            $reference_id = $response->result->purchase_units[0]->reference_id;
            $pedido_id_db = (int) str_replace('PEDIDO_', '', $reference_id);
            $total_pagado = $response->result->purchase_units[0]->amount->value;

            $conn = conectar_db();
            $stmt = $conn->prepare("UPDATE pedidos SET estado = 'Completado', paypal_order_id = ? WHERE id = ?");
            $stmt->bind_param("si", $orderID, $pedido_id_db);
            $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();

            if ($affected_rows > 0) {
                // Lógica de Recompensas
                $stmt_pedido = $conn->prepare("SELECT total, usuario_id FROM pedidos WHERE id = ?");
                $stmt_pedido->bind_param("i", $pedido_id_db);
                $stmt_pedido->execute();
                $pedido_data = $stmt_pedido->get_result()->fetch_assoc();
                $stmt_pedido->close();
                
                if ($pedido_data && $pedido_data['total'] >= 500) {
                    generarRecompensa($pedido_data['usuario_id'], $pedido_data['total'], $pedido_id_db);
                }
                
                // Lógica de Puntos
                procesarSistemaPuntos($pedido_id_db, $conn);
                
                // Limpiar sesión
                unset($_SESSION['cart']);
                if (isset($_SESSION['cupon_aplicado'])) {
                    unset($_SESSION['cupon_aplicado']);
                }
                $status_message = "success";
            } else {
                $status_message = "sync_error";
            }
            $conn->close();
        } else {
             $status_message = "pending_error";
        }
    } catch (HttpException $e) {
        $message = json_decode($e->getMessage());
        if (isset($message->name) && $message->name === 'UNPROCESSABLE_ENTITY' && isset($message->details[0]->issue) && $message->details[0]->issue === 'ORDER_ALREADY_CAPTURED') {
            $status_message = "already_processed";
        } else {
            // Registrar error detallado para depuración
            error_log("Error de captura de PayPal: " . $e->getMessage());
            $status_message = "capture_error";
        }
    } catch (Exception $e) {
        error_log("Error general en el proceso de pago: " . $e->getMessage());
        $status_message = "general_error";
    }
    
    // Redirigir para mostrar el resultado final
    header("Location: pago_exitoso.php?status={$status_message}&order_id={$pedido_id_db}");
    exit;
}

// --- Lógica para mostrar la página de estado ---
$status = $_GET['status'] ?? 'unknown';
$order_id = $_GET['order_id'] ?? 0;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Pago - Sabores de Cristal</title>
    <link rel="stylesheet" href="estilo-comprobante.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="receipt-container">
        <?php if ($status === 'success' || $status === 'already_processed'): ?>
            <div class="header">
                <div class="company-logo">
                    <img src="Images/LOGO-min.png" alt="Sabores de Cristal Logo">
                </div>
                <div class="receipt-info">
                    <p><strong>Comprobante de Pedido</strong></p>
                    <p>Fecha: <?php echo date('d/m/Y H:i'); ?></p>
                </div>
            </div>
            
            <div class="receipt-icon"><i class="fas fa-check-circle"></i></div>
            <h1>¡Pago Recibido!</h1>
            <p>Tu pedido ha sido procesado exitosamente. Gracias por confiar en Sabores de Cristal. En breve recibirás detalles sobre la entrega.</p>
            
            <div class="order-details">
                <div><strong>Número de Pedido:</strong> <span>#<?php echo htmlspecialchars($order_id); ?></span></div>
                </div>
            
            <a href="pago_exitoso.php?download_pdf=1&order_id=<?php echo htmlspecialchars($order_id); ?>" class="download-button">
                <i class="fas fa-file-pdf"></i> Descargar Comprobante PDF
            </a>
            
            <div class="footer">
                <p>¡Gracias por tu preferencia!</p>
            </div>
            
        <?php else: ?>
            <div class="receipt-icon error" style="color: #A53D34;"><i class="fas fa-times-circle"></i></div>
            <h1 style="color: #7B2D26;">¡Hubo un Problema!</h1>
            <?php
                $error_message = "Ocurrió un error inesperado. Por favor, contacta a soporte.";
                if ($status === 'sync_error') {
                    $error_message = "Tu pago en PayPal fue exitoso, pero no pudimos registrarlo automáticamente en nuestro sistema. Por favor, contacta a soporte con tu comprobante de PayPal.";
                } elseif ($status === 'pending_error') {
                    $error_message = "El estado de tu pago en PayPal no es 'COMPLETADO'. Por favor, verifica tu cuenta de PayPal o contacta a soporte.";
                } elseif ($status === 'capture_error') {
                    $error_message = "No se pudo finalizar el pago. Intenta de nuevo o contacta a soporte.";
                } elseif ($status === 'already_processed') {
                    $error_message = "Este pago ya había sido procesado. Si no recibiste tu confirmación, contacta a soporte.";
                }
            ?>
            <p><?php echo $error_message; ?></p>
            <div class="order-details">
                <div><strong>Número de Pedido:</strong> <span>#<?php echo htmlspecialchars($order_id); ?></span></div>
                <p>Si el problema persiste, por favor proporciona este número a nuestro equipo de soporte.</p>
            </div>
        <?php endif; ?>
        
        <div class="back-to-shop">
            <a href="tienda.php"><i class="fas fa-store"></i> Volver a la tienda</a>
        </div>
    </div>

</body>
</html>