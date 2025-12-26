<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Habilitar errores para debug (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar carrito si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- Lógica para añadir productos al carrito (Petición POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // Debug: Log de los datos recibidos
        error_log("Datos POST recibidos: " . print_r($_POST, true));
        
        $producto_nombre = 'Producto';
        $response_message = '';
        $is_valid_request = false;

        // 1. Si es un pastel personalizado
        if (isset($_POST['is_custom_cake']) && $_POST['is_custom_cake'] == 'true') {
            $producto_id = 'custom_' . time();
            $_SESSION['cart'][$producto_id] = [
                'is_custom' => true,
                'nombre' => $_POST['nombre'] ?? 'Pastel Personalizado',
                'precio' => (float)($_POST['precio'] ?? 0),
                'cantidad' => 1,
                'instrucciones' => $_POST['instrucciones'] ?? '',
                'custom_details' => $_POST['custom_details'] ?? ''
            ];
            $producto_nombre = $_POST['nombre'] ?? 'Pastel Personalizado';
            $response_message = 'Pastel personalizado añadido con éxito';
            $is_valid_request = true;
        } 
        // 2. Si es un producto normal de la tienda
        else if (isset($_POST['producto_id'])) {
            $producto_id = (int)$_POST['producto_id'];
            $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
            $instrucciones = isset($_POST['instrucciones']) ? trim($_POST['instrucciones']) : '';

            if ($cantidad <= 0) $cantidad = 1;

            // Verificar si el producto ya está en el carrito
            if (isset($_SESSION['cart'][$producto_id])) {
                // Producto ya en el carrito: actualizar cantidad
                $_SESSION['cart'][$producto_id]['cantidad'] += $cantidad;
                
                // Actualizar instrucciones si se proporcionan nuevas
                if (!empty($instrucciones)) {
                    $_SESSION['cart'][$producto_id]['instrucciones'] = $instrucciones;
                }
                
                $producto_nombre = $_SESSION['cart'][$producto_id]['nombre'];
                $response_message = 'Cantidad actualizada';
                $is_valid_request = true;
                
            } else {
                // Nuevo producto: obtener info de la DB
                $conn = conectar_db();
                
                if (!$conn) {
                    throw new Exception('No se pudo conectar a la base de datos');
                }
                
                $stmt = $conn->prepare("SELECT id, nombre, precio, imagen FROM productos WHERE id = ?");
                
                if (!$stmt) {
                    throw new Exception('Error preparando la consulta: ' . $conn->error);
                }
                
                $stmt->bind_param("i", $producto_id);
                
                if (!$stmt->execute()) {
                    throw new Exception('Error ejecutando la consulta: ' . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $producto = $result->fetch_assoc();
                $stmt->close();
                $conn->close();

                if ($producto) {
                    $_SESSION['cart'][$producto_id] = [
                        'nombre' => $producto['nombre'],
                        'precio' => (float)$producto['precio'],
                        'imagen' => $producto['imagen'],
                        'cantidad' => $cantidad,
                        'instrucciones' => $instrucciones
                    ];
                    $producto_nombre = $producto['nombre'];
                    $response_message = 'Producto añadido con éxito';
                    $is_valid_request = true;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado en la base de datos. ID: ' . $producto_id]);
                    exit;
                }
            }
        } 
        
        // RESPUESTA DE ÉXITO FINAL Y SALIDA (para solicitudes POST)
        if ($is_valid_request) {
            // Calcular el total de items en el carrito
            $total_items = array_sum(array_column($_SESSION['cart'], 'cantidad'));
            
            echo json_encode([
                'status' => 'success',
                'message' => $response_message,
                'nombre' => $producto_nombre,
                'total_items' => $total_items 
            ]);
            exit;
        }

        // Si llegó aquí y es POST pero no fue una petición válida.
        echo json_encode(['status' => 'error', 'message' => 'Solicitud POST inválida o incompleta.']);
        exit;
        
    } catch (Exception $e) {
        error_log("Error en ajax-cart.php: " . $e->getMessage());
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error del servidor: ' . $e->getMessage()
        ]);
        exit;
    }
}

// ========================================================================================
// --- Lógica para obtener el carrito (Se ejecuta si NO es una petición POST) ---
// ========================================================================================
try {
    $productos_carrito = [];
    $subtotal = 0;
    $total_items = 0;

    if (!empty($_SESSION['cart'])) {
        $conn = conectar_db();
        
        if (!$conn) {
            throw new Exception('No se pudo conectar a la base de datos');
        }
        
        // Filtramos los IDs de productos normales (solo enteros)
        $product_ids = array_filter(array_keys($_SESSION['cart']), 'is_numeric');

        if (!empty($product_ids)) {
            // Preparamos la consulta para obtener los detalles de los productos normales
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $conn->prepare("SELECT id, nombre, precio, imagen FROM productos WHERE id IN ($placeholders)");
            
            if (!$stmt) {
                throw new Exception('Error preparando consulta: ' . $conn->error);
            }
            
            // Creamos la cadena de tipos de parámetros (solo 'i' para enteros)
            $types = str_repeat('i', count($product_ids));
            
            // Llamamos a bind_param dinámicamente
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $productos_db = [];
            while ($row = $result->fetch_assoc()) {
                $productos_db[$row['id']] = $row;
            }
            $stmt->close();

            // Recorrer el carrito para construir el resultado
            foreach ($product_ids as $id) {
                if (isset($_SESSION['cart'][$id]) && isset($productos_db[$id])) {
                    $item_en_carrito = $_SESSION['cart'][$id];
                    $producto = $productos_db[$id];
                    
                    $subtotal += $producto['precio'] * $item_en_carrito['cantidad'];
                    $total_items += $item_en_carrito['cantidad'];
                    
                    $productos_carrito[] = [
                        'id' => $producto['id'],
                        'nombre' => $producto['nombre'],
                        'precio' => $producto['precio'],
                        'imagen' => $producto['imagen'],
                        'cantidad' => $item_en_carrito['cantidad'],
                        'instrucciones' => $item_en_carrito['instrucciones'] ?? ''
                    ];
                }
            }
        }
        
        // Añadir pasteles personalizados al resultado
        foreach ($_SESSION['cart'] as $id => $details) {
            if (!is_numeric($id) && isset($details['is_custom']) && $details['is_custom']) {
                $subtotal += $details['precio'] * $details['cantidad'];
                $total_items += $details['cantidad'];
                $productos_carrito[] = [
                    'id' => $id,
                    'nombre' => $details['nombre'],
                    'precio' => $details['precio'],
                    'imagen' => $details['imagen'] ?? 'Images/placeholder.png',
                    'cantidad' => $details['cantidad'],
                    'instrucciones' => $details['instrucciones'] ?? '',
                    'is_custom' => true
                ];
            }
        }

        $conn->close();
    }

    // Respuesta JSON para el carrito completo (peticiones GET)
    echo json_encode([
        'items' => $productos_carrito,
        'subtotal' => number_format($subtotal, 2, '.', ''),
        'subtotal_raw' => $subtotal,
        'total_items' => $total_items,
        'is_empty' => empty($productos_carrito)
    ]);
    
} catch (Exception $e) {
    error_log("Error en ajax-cart.php (GET): " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error cargando el carrito: ' . $e->getMessage(),
        'items' => [],
        'subtotal' => '0.00',
        'subtotal_raw' => 0,
        'total_items' => 0,
        'is_empty' => true
    ]);
}
?>