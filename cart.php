<?php
// PHP LOGIC (NO MODIFICADO, SOLO COPIADO PARA COMPLETITUD)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

$conn = conectar_db();

// --- LÓGICA DE ACTUALIZACIÓN Y ELIMINACIÓN (TU CÓDIGO ORIGINAL) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    $producto_id = $_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];
    if (isset($_SESSION['cart'][$producto_id])) {
        if ($cantidad > 0) {
            $_SESSION['cart'][$producto_id]['cantidad'] = $cantidad;
        } else {
            unset($_SESSION['cart'][$producto_id]);
        }
    }
    header('Location: cart.php');
    exit;
}

if (isset($_GET['remove'])) {
    $producto_id_a_eliminar = $_GET['remove'];
    unset($_SESSION['cart'][$producto_id_a_eliminar]);
    header('Location: cart.php');
    exit;
}

// --- LÓGICA PARA CONSTRUIR LA VISTA DEL CARRITO (TU CÓDIGO ORIGINAL) ---
$productos_carrito = [];
$subtotal = 0;
if (!empty($_SESSION['cart'])) {
    $normal_product_ids = [];
    $custom_cakes = [];
    foreach ($_SESSION['cart'] as $id => $details) {
        if (isset($details['is_custom']) && $details['is_custom']) {
            $custom_cakes[$id] = $details;
        } else {
            if (is_numeric($id)) {
                $normal_product_ids[] = (int)$id;
            }
        }
    }

    if (!empty($normal_product_ids)) {
        // Mejor práctica: usar sentencias preparadas para prevenir inyección SQL, aunque aquí se asume que los IDs son números.
        $ids_string = implode(',', $normal_product_ids);
        $sql = "SELECT id, nombre, precio, imagen FROM productos WHERE id IN ($ids_string)";
        $result_db = $conn->query($sql);
        if ($result_db) {
            while ($producto_db = $result_db->fetch_assoc()) {
                $item_info = $_SESSION['cart'][$producto_db['id']];
                $productos_carrito[] = [
                    'id' => $producto_db['id'], 'nombre' => $producto_db['nombre'], 'precio' => $producto_db['precio'],
                    'imagen' => $producto_db['imagen'], 'cantidad' => $item_info['cantidad'], 'instrucciones' => $item_info['instrucciones'],
                    'is_custom' => false
                ];
                $subtotal += $producto_db['precio'] * $item_info['cantidad'];
            }
        }
    }

    foreach ($custom_cakes as $id => $details) {
        $productos_carrito[] = [
            'id' => $id, 'nombre' => $details['nombre'], 'precio' => $details['precio'], 'imagen' => $details['imagen'] ?? 'Images/default-cake.png', // Asumiendo una imagen por defecto para pasteles custom si no la tienen
            'cantidad' => $details['cantidad'], 'instrucciones' => $details['instrucciones'],
            'is_custom' => true
        ];
        $subtotal += $details['precio'] * $details['cantidad'];
    }
}

// --- LÓGICA DE CUPONES Y TOTALES (TU CÓDIGO ORIGINAL) ---
$mensaje_cupon = ''; $tipo_mensaje = ''; $descuento = 0;
$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_cupon'])) {
    $codigo_cupon = trim($_POST['codigo_cupon']);
    if ($user_id > 0) {
        $stmt = $conn->prepare("SELECT id, valor_descuento, utilizado FROM recompensas WHERE codigo_cupon = ? AND usuario_id = ? LIMIT 1");
        $stmt->bind_param("si", $codigo_cupon, $user_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($cupon = $resultado->fetch_assoc()) {
            if ($cupon['utilizado'] == 1) { $mensaje_cupon = 'Este cupón ya ha sido utilizado.'; $tipo_mensaje = 'danger'; unset($_SESSION['cupon_aplicado']); }
            else { $_SESSION['cupon_aplicado'] = ['id' => $cupon['id'], 'codigo' => $codigo_cupon, 'descuento' => $cupon['valor_descuento']]; $mensaje_cupon = '¡Cupón aplicado con éxito!'; $tipo_mensaje = 'success'; }
        } else { $mensaje_cupon = 'El código del cupón no es válido.'; $tipo_mensaje = 'danger'; unset($_SESSION['cupon_aplicado']); }
        $stmt->close();
    } else { $mensaje_cupon = 'Debes iniciar sesión para usar un cupón.'; $tipo_mensaje = 'warning'; }
}

if (isset($_POST['quitar_cupon'])) { unset($_SESSION['cupon_aplicado']); $mensaje_cupon = 'Cupón eliminado.'; $tipo_mensaje = 'info'; }
if (isset($_SESSION['cupon_aplicado'])) { $descuento = $_SESSION['cupon_aplicado']['descuento']; }
$total = $subtotal - $descuento;
if ($total < 0) $total = 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Sabores de Cristal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" sizes="16x16" href="Images/LOGO-min.png">
    </head>
<body class="dashboard">
    <div class="swanky">
        <div class="swanky_wrapper">
            <input type="radio" name="radio" id="mi-perfil"><label for="mi-perfil"><i class="fas fa-user-circle"></i><span>Mi Perfil</span><div class="lil_arrow"></div><div class="bar"></div><div class="swanky_wrapper__content"><ul><li><a href="profile.php">Ver mi Perfil</a></li><li><a href="edit-profile.php">Editar Información</a>
             <li><a href="historial.php">Historial de Pedidos</a></li>
                </li></ul></div></label>
            
            <input type="radio" name="radio" id="mis-aplicaciones">
            <label for="mis-aplicaciones">
                <i class="fas fa-code"></i>
                <span>Mis Aplicaciones</span>
                <div class="lil_arrow"></div>
                <div class="bar"></div>
                <div class="swanky_wrapper__content">
                    <ul>
                        <li><a href="personalizador/pasteles.php">Personalizador de Pasteles</a></li>
                        <li><a href="Galeria3D/index.php">Galería 3D</a></li>
                        <li><a href="Mesas3D/index.html">Mesas 3D</a></li>

                    </ul>
                </div>
            </label>

            <input type="radio" name="radio" id="mis-recompensas"><label for="mis-recompensas"><i class="fas fa-gift"></i><span>Mis Recompensas</span><div class="lil_arrow"></div><div class="bar"></div><div class="swanky_wrapper__content"><ul><li><a href="orders.php">Ver mis Cupones</a></li></ul></div></label>
            <input type="radio" name="radio" id="carrito" checked><label for="carrito"><i class="fas fa-shopping-cart"></i><span>Carrito</span><div class="lil_arrow"></div><div class="bar"></div><div class="swanky_wrapper__content"><ul><li><a href="cart.php">Ir al Carrito</a></li></ul></div></label>
            <input type="radio" name="radio" id="tienda"><label for="tienda"><i class="fas fa-store"></i><span>Tienda</span><div class="lil_arrow"></div><div class="bar"></div><div class="swanky_wrapper__content"><ul><li><a href="index.php">Página Principal</a></li><li><a href="tienda.php">Ver Productos</a></li><li></li></ul></div></label>
            <input type="radio" name="radio" id="sesion"><label for="sesion"><i class="fas fa-sign-out-alt"></i><span>Sesión</span><div class="lil_arrow"></div><div class="bar"></div><div class="swanky_wrapper__content"><ul><li><a href="logout.php">Cerrar Sesión</a></li></ul></div></label>
        </div>

        <div class="main-dashboard-content">
            <div class="cart-header-section mb-5">
                <a href="tienda.php" class="back-to-shop-link">
                    <i class="fas fa-arrow-left me-2"></i> 
                    <span>Seguir comprando</span>
                </a>
                <div class="text-center mt-3">
                    <h1 class="cart-main-title">Tu Carrito de Compras</h1>
                    <p class="cart-subtitle text-white-50">Revisa y gestiona tus productos seleccionados</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="cart-section-enhanced">
                        <?php if (empty($productos_carrito)): ?>
                            <div class="empty-cart-state text-center py-5">
                                <div class="empty-cart-icon mb-4">
                                    <i class="fas fa-shopping-basket"></i>
                                </div>
                                <h3 class="text-white-50 mb-3">Tu carrito está vacío</h3>
                                <p class="text-white-50 mb-4">Descubre nuestros deliciosos productos y añade algo especial</p>
                                <a href="tienda.php" class="aurora-button" style="text-decoration: none;">
                                    <div><span>Explorar Productos</span></div>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="cart-items-header mb-4">
                                <h4 class="text-light">Tus Productos (<?php echo count($productos_carrito); ?>)</h4>
                            </div>
                            
                            <?php foreach ($productos_carrito as $producto): ?>
                                <div class="cart-item-card <?php echo $producto['is_custom'] ? 'custom-cake-item' : ''; ?>">
                                    <?php if ($producto['is_custom']): ?>
                                        <div class="custom-cake-header">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="custom-badge"><i class="fas fa-star me-1"></i> Personalizado</div>
                                                    <h5 class="product-name mb-2"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                                    <div class="product-price-main">$<?php echo number_format($producto['precio'], 2); ?></div>
                                                </div>
                                                <div class="product-actions">
                                                    <a href="cart.php?remove=<?php echo htmlspecialchars($producto['id']); ?>" class="btn-remove-item" title="Eliminar pastel">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($producto['instrucciones'])): ?>
                                            <div class="custom-instructions mt-3">
                                                <div class="instructions-label">
                                                    <i class="fas fa-clipboard-list me-2"></i>
                                                    <strong>Detalles de tu creación:</strong>
                                                </div>
                                                <div class="instructions-content">
                                                    <?php echo nl2br(htmlspecialchars($producto['instrucciones'])); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="item-main-content">
                                            <div class="product-image-container">
                                                <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" class="product-image" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                            </div>
                                            <div class="product-info-section">
                                                <div class="product-header">
                                                    <h5 class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                                    <div class="product-price-display">
                                                        <span class="price-unit">$<?php echo number_format($producto['precio'], 2); ?> c/u</span>
                                                        <span class="price-total">$<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="item-controls-section">
                                            <div class="quantity-controls">
                                                <div class="quantity-selector-wrapper">
                                                    <label class="quantity-label">Cantidad:</label>
                                                    <div class="quantity-selector">
                                                        <button type="button" class="quantity-btn minus" onclick="updateQuantity(this, -1)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" data-product-id="<?php echo $producto['id']; ?>" value="<?php echo $producto['cantidad']; ?>" min="1" class="quantity-input" readonly>
                                                        <button type="button" class="quantity-btn plus" onclick="updateQuantity(this, 1)">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                </div>
                                            
                                            <div class="action-controls">
                                                <a href="cart.php?remove=<?php echo $producto['id']; ?>" class="btn-remove-item" title="Eliminar producto" onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                                    <i class="fas fa-trash"></i>
                                                    <span>Eliminar</span>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary-enhanced">
                        <div class="summary-header">
                            <h4 class="summary-title">
                                <i class="fas fa-receipt me-2"></i>
                                Resumen de Compra
                            </h4>
                        </div>
                        
                        <div class="summary-content">
                            <?php if (isset($_SESSION['cupon_aplicado'])): ?>
                                <div class="coupon-applied-alert">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-tag me-2"></i>
                                            <span>Cupón aplicado: **<?php echo htmlspecialchars($_SESSION['cupon_aplicado']['codigo']); ?>**</span>
                                        </div>
                                        <form action="cart.php" method="POST" class="d-inline">
                                            <button type="submit" name="quitar_cupon" class="btn-remove-cupon" title="Quitar cupón">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="coupon-section">
                                    <label class="form-label coupon-label">
                                        <i class="fas fa-ticket-alt me-2"></i>
                                        ¿Tienes un cupón?
                                    </label>
                                    <form action="cart.php" method="POST" class="coupon-form">
                                        <div class="input-group">
                                            <input type="text" name="codigo_cupon" class="form-control coupon-input" placeholder="Ingresa tu código">
                                            <button class="aurora-button coupon-btn" type="submit">
                                                <div><span>Aplicar</span></div>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($mensaje_cupon): ?>
                                <div class="alert alert-<?php echo $tipo_mensaje; ?> mt-3 coupon-message">
                                    <?php echo htmlspecialchars($mensaje_cupon); ?>
                                </div>
                            <?php endif; ?>

                            <div class="price-breakdown mt-4">
                                <div class="price-row">
                                    <span class="price-label">Subtotal:</span>
                                    <span class="price-value">$<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                
                                <?php if ($descuento > 0): ?>
                                <div class="price-row discount-row">
                                    <span class="price-label">Descuento aplicado:</span>
                                    <span class="price-value discount-value">-$<?php echo number_format($descuento, 2); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="price-divider"></div>
                                
                                <div class="price-row total-row">
                                    <span class="price-label">Total:</span>
                                    <span class="price-value total-value">$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="checkout-section mt-4">
                                <a href="procesar_compra.php" class="aurora-button checkout-btn <?php echo empty($productos_carrito) ? 'disabled' : ''; ?>" style="text-decoration: none;">
                                    <div>
                                        <span>
                                            <i class="fas fa-lock me-2"></i>
                                            Proceder al Pago
                                        </span>
                                    </div>
                                </a>
                                
                                <?php if (!empty($productos_carrito)): ?>
                                <div class="security-notice mt-3">
                                    <small class="text-white-50">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Pago seguro con PayPal y tarjetas de crédito/débito
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function updateQuantity(button, change) {
            const input = button.closest('.quantity-selector').querySelector('.quantity-input');
            let currentValue = parseInt(input.value);
            let newValue = currentValue + change;
            const productId = input.dataset.productId;

            if (newValue < 1) {
                newValue = 1; // Mínimo 1 unidad
            }

            if (newValue !== currentValue) {
                // Actualiza el valor en el input
                input.value = newValue;

                // Crea un formulario dinámico para enviar la actualización (simulando el POST de tu PHP)
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'cart.php'; // Recarga la página para actualizar la sesión y el precio total

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'producto_id';
                idInput.value = productId;

                const qtyInput = document.createElement('input');
                qtyInput.type = 'hidden';
                qtyInput.name = 'cantidad';
                qtyInput.value = newValue;

                const updateInput = document.createElement('input');
                updateInput.type = 'hidden';
                updateInput.name = 'update_cart';
                updateInput.value = '1';

                form.appendChild(idInput);
                form.appendChild(qtyInput);
                form.appendChild(updateInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="script.js"></script>
</body>
</html>