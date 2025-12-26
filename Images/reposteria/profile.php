<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { 
    header('Location: login.php'); 
    exit; 
}

$db_config = [ 
    'servername' => "localhost", 
    'username' => "u686732311_Sabores", 
    'password' => "Cuchit@Pichichis9901221512", 
    'dbname' => "u686732311_saboresdc" 
];

$user_id = $_SESSION['user_id'];
$user_info = null;
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);

if (!$conn->connect_error) {
    // Obtener información del usuario
    $stmt = $conn->prepare("SELECT nombre, email, auth_secret, profile_picture, puntos_acumulados FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_info = $result->fetch_assoc();
    $stmt->close();
    
    // Obtener estadísticas de pedidos
    $stmt_pedidos = $conn->prepare("SELECT COUNT(*) as total_pedidos, SUM(total) as total_gastado FROM pedidos WHERE usuario_id = ? AND estado = 'Completado'");
    $stmt_pedidos->bind_param("i", $user_id);
    $stmt_pedidos->execute();
    $result_pedidos = $stmt_pedidos->get_result();
    $estadisticas = $result_pedidos->fetch_assoc();
    $stmt_pedidos->close();
    
    // Obtener recompensas disponibles
    $stmt_recompensas = $conn->prepare("SELECT COUNT(*) as recompensas_disponibles FROM recompensas WHERE usuario_id = ? AND utilizado = 0");
    $stmt_recompensas->bind_param("i", $user_id);
    $stmt_recompensas->execute();
    $result_recompensas = $stmt_recompensas->get_result();
    $recompensas_data = $result_recompensas->fetch_assoc();
    $stmt_recompensas->close();
    
    // Obtener historial de puntos recientes
    $stmt_puntos = $conn->prepare("SELECT pp.pedido_id, SUM(pr.puntos_generados * pp.cantidad) as puntos_obtenidos, p.fecha_pedido 
                                  FROM pedido_productos pp 
                                  JOIN productos pr ON pp.producto_id = pr.id 
                                  JOIN pedidos p ON pp.pedido_id = p.id 
                                  WHERE p.usuario_id = ? AND p.estado = 'Completado'
                                  GROUP BY pp.pedido_id 
                                  ORDER BY p.fecha_pedido DESC 
                                  LIMIT 5");
    $stmt_puntos->bind_param("i", $user_id);
    $stmt_puntos->execute();
    $result_puntos = $stmt_puntos->get_result();
    $historial_puntos = [];
    while ($row = $result_puntos->fetch_assoc()) {
        $historial_puntos[] = $row;
    }
    $stmt_puntos->close();
    
    $conn->close();
}

$avatar_path = 'uploads/avatars/' . ($user_info['profile_picture'] ?? 'default-avatar.png');
$puntos_acumulados = $user_info['puntos_acumulados'] ?? 0;
$total_pedidos = $estadisticas['total_pedidos'] ?? 0;
$total_gastado = $estadisticas['total_gastado'] ?? 0;
$recompensas_disponibles = $recompensas_data['recompensas_disponibles'] ?? 0;

// Sistema de niveles mejorado - Progresión balanceada
function calcularNivel($puntos) {
    $niveles = [
        ['nombre' => 'Principiante', 'min' => 0, 'max' => 99, 'color' => '#CD7F32', 'siguiente' => 'Bronce'],
        ['nombre' => 'Bronce', 'min' => 100, 'max' => 299, 'color' => '#CD7F32', 'siguiente' => 'Plata'],
        ['nombre' => 'Plata', 'min' => 300, 'max' => 599, 'color' => '#C0C0C0', 'siguiente' => 'Oro'],
        ['nombre' => 'Oro', 'min' => 600, 'max' => 999, 'color' => '#FFD700', 'siguiente' => 'Diamante'],
        ['nombre' => 'Diamante', 'min' => 1000, 'max' => 9999, 'color' => '#B9F2FF', 'siguiente' => 'Máximo']
    ];
    
    foreach ($niveles as $nivel) {
        if ($puntos >= $nivel['min'] && $puntos <= $nivel['max']) {
            $progreso = (($puntos - $nivel['min']) / ($nivel['max'] - $nivel['min'])) * 100;
            $puntos_restantes = $nivel['max'] - $puntos + 1;
            
            return [
                'nombre' => $nivel['nombre'],
                'color' => $nivel['color'],
                'progreso' => min(100, max(0, $progreso)),
                'siguiente' => $nivel['siguiente'],
                'puntos_restantes' => $puntos_restantes,
                'rango_actual' => "{$nivel['min']}-{$nivel['max']} puntos"
            ];
        }
    }
    
    // Si supera el máximo nivel
    return [
        'nombre' => 'Diamante',
        'color' => '#B9F2FF',
        'progreso' => 100,
        'siguiente' => 'Máximo',
        'puntos_restantes' => 0,
        'rango_actual' => '1000+ puntos'
    ];
}

$nivel_usuario = calcularNivel($puntos_acumulados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Sabores de Cristal</title>
    <link rel="icon" type="image/png" sizes="16x16" href="Images/LOGO.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Images/LOGO.png">    <link rel="apple-touch-icon" sizes="180x180" href="Images/LOGO-min.png">
    <link rel="shortcut icon" href="Images/LOGO.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

   <style>
   </style>
</head>
<body class="dashboard">
    <div class="swanky">
        <div class="swanky_wrapper">
            <input type="radio" name="radio" id="mi-perfil" checked>
            <label for="mi-perfil">
                <i class="fas fa-user-circle"></i>
                <span>Mi Perfil</span>
                <div class="lil_arrow"></div>
                <div class="bar"></div>
                <div class="swanky_wrapper__content">
                    <ul>
                        <li><a href="edit-profile.php">Editar Información</a></li>
                        <li><a href="setup-2fa.php">Seguridad (2FA)</a></li>
                        <li><a href="historial.php">Historial de Pedidos</a></li>
                    </ul>
                </div>
            </label>

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
                        <li><a href="juego/juego.php">Juego de Repostería</a></li>    
                    </ul>
                </div>
            </label>

            <input type="radio" name="radio" id="mis-recompensas">
            <label for="mis-recompensas">
                 <i class="fas fa-gift"></i>
                <span>Mis Recompensas</span>
                <div class="lil_arrow"></div>
                <div class="bar"></div>
                <div class="swanky_wrapper__content">
                    <ul>
                         <li><a href="orders.php">Ver mis Cupones</a></li>
                    </ul>
                </div>
            </label>

            <input type="radio" name="radio" id="carrito">
            <label for="carrito">
                <i class="fas fa-shopping-cart"></i>
                <span>Carrito</span>
                <div class="lil_arrow"></div>
                <div class="bar"></div>
                <div class="swanky_wrapper__content">
                    <ul>
                        <li><a href="cart.php">Ir al Carrito</a></li>
                    </ul>
                </div>
            </label>

            <input type="radio" name="radio" id="galeria3d">

            
            <input type="radio" name="radio" id="tienda">
            <label for="tienda">
                <i class="fas fa-store"></i>
                <span>Tienda</span>
                <div class="lil_arrow"></div>
                <div class="bar"></div>
                <div class="swanky_wrapper__content">
                    <ul>
                        <li><a href="index.php">Página Principal</a></li>
                        <li><a href="tienda.php">Ver Productos</a></li>
                    </ul>
                </div>
            </label>

            <input type="radio" name="radio" id="sesion">
            <label for="sesion">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sesión</span>
                <div class="lil_arrow"></div>
                <div class="bar"></div>
                <div class="swanky_wrapper__content">
                    <ul>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </label>
        </div>

        <div class="main-dashboard-content">
            <header class="mb-4">
                <h1>BIENVENIDO</h1>
                <p class="text-white-50 mb-0">¡Hola de nuevo!, <?php echo htmlspecialchars($user_info['nombre'] ?? 'Usuario'); ?></p>
            </header>
            
            <!-- Mensaje de Bienvenida -->
            <div class="welcome-message">
                <h4><i class="fas fa-star text-warning me-2"></i>¡Este es tu espacio personal!</h4>
                <p class="mb-0">Gana puntos con cada compra y sube de nivel para desbloquear beneficios exclusivos.</p>
            </div>
            
            <div class="row g-4">
                <!-- Columna Izquierda - Perfil -->
                <div class="col-lg-4">
                    <div class="profile-card">
                        <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Foto de Perfil" class="profile-avatar">
                        <h3><?php echo htmlspecialchars($user_info['nombre'] ?? 'Usuario'); ?></h3>
                        <p class="text-white-50"><?php echo htmlspecialchars($user_info['email'] ?? ''); ?></p>
                        
                        <!-- Nivel del Usuario -->
                        <div class="mt-3 mb-3">
                            <div class="level-badge" style="background: <?php echo $nivel_usuario['color']; ?>; color: <?php echo $nivel_usuario['nombre'] == 'Oro' || $nivel_usuario['nombre'] == 'Plata' ? '#000' : '#fff'; ?>;">
                                Nivel <?php echo $nivel_usuario['nombre']; ?>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $nivel_usuario['progreso']; ?>%" 
                                     aria-valuenow="<?php echo $nivel_usuario['progreso']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-white-50">
                                <?php if ($nivel_usuario['puntos_restantes'] > 0): ?>
                                    <i class="fas fa-bullseye me-1"></i>
                                    Faltan <?php echo $nivel_usuario['puntos_restantes']; ?> puntos para <?php echo $nivel_usuario['siguiente']; ?>
                                <?php else: ?>
                                    <i class="fas fa-trophy me-1"></i>
                                    ¡Has alcanzado el nivel máximo!
                                <?php endif; ?>
                            </small>
                        </div>
                        
                    </div>
                    
                    <!-- Información del Nivel Actual -->
                    <div class="content-box content-box-medium mt-4">
                        <div class="section-header">
                            <i class="fas fa-crown"></i>
                            <h5 class="mb-0">Beneficios Nivel <?php echo $nivel_usuario['nombre']; ?></h5>
                        </div>
                        <div class="compact-content">
                            <ul class="benefits-list">
                                <?php if ($nivel_usuario['nombre'] == 'Principiante'): ?>
                                    <li>5% descuento primera compra</li>
                                    <li>Promociones básicas</li>
                                <?php elseif ($nivel_usuario['nombre'] == 'Bronce'): ?>
                                    <li>10% descuento seleccionados</li>
                                    <li>Envío prioritario +$300</li>
                                    <li>Acceso anticipado</li>
                                <?php elseif ($nivel_usuario['nombre'] == 'Plata'): ?>
                                    <li>15% descuento total</li>
                                    <li>Envío gratis +$200</li>
                                    <li>Soporte prioritario</li>
                                <?php elseif ($nivel_usuario['nombre'] == 'Oro'): ?>
                                    <li>20% descuento permanente</li>
                                    <li>Envío gratis siempre</li>
                                    <li>Asesor personalizado</li>
                                <?php else: ?>
                                    <li>25% descuento permanente</li>
                                    <li>Regalo sorpresa</li>
                                    <li>Experiencias exclusivas</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Acciones Rápidas -->
                    <div class="quick-actions mt-4">
                        <a href="tienda.php" class="action-btn">
                            <i class="fas fa-store fa-2x mb-2"></i>
                            <div>Ir a la Tienda</div>
                        </a>
                        <a href="cart.php" class="action-btn position-relative">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <div>Mi Carrito</div>
                        </a>
                        <a href="orders.php" class="action-btn position-relative">
                            <i class="fas fa-gift fa-2x mb-2"></i>
                            <div>Mis Recompensas</div>
                            <?php if ($recompensas_disponibles > 0): ?>
                                <span class="notification-badge"><?php echo $recompensas_disponibles; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="historial.php" class="action-btn">
                            <i class="fas fa-history fa-2x mb-2"></i>
                            <div>Historial</div>
                        </a>
                    </div>
                </div>
                
                <!-- Columna Derecha - Estadísticas -->
                <div class="col-lg-8">
                    <div class="row g-4">
                        <!-- Puntos de Recompensa -->
                        <div class="col-md-6">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="stat-number"><?php echo $puntos_acumulados; ?></div>
                                <div class="stat-label">Puntos Acumulados</div>
                                <small class="text-white-50">Gana más puntos con cada compra</small>
                            </div>
                        </div>
                        
                        <!-- Total de Pedidos -->
                        <div class="col-md-6">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stat-number"><?php echo $total_pedidos; ?></div>
                                <div class="stat-label">Pedidos Realizados</div>
                                <small class="text-white-50">Compras completadas</small>
                            </div>
                        </div>
                        
                        <!-- Total Gastado -->
                        <div class="col-md-6">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stat-number">$<?php echo number_format($total_gastado, 2); ?></div>
                                <div class="stat-label">Total Gastado</div>
                                <small class="text-white-50">En todas tus compras</small>
                            </div>
                        </div>
                        
                        <!-- Recompensas Disponibles -->
                        <div class="col-md-6">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div class="stat-number"><?php echo $recompensas_disponibles; ?></div>
                                <div class="stat-label">Recompensas Disponibles</div>
                                <small class="text-white-50">Cupones por usar</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Fila inferior: Historial de Puntos y Seguridad -->
                    <div class="row g-4 mt-2">
                        <!-- Historial de Puntos Recientes -->
                        <div class="col-lg-6">
                            <div class="content-box content-box-medium">
                                <div class="section-header">
                                    <i class="fas fa-history"></i>
                                    <h5 class="mb-0">Historial de Puntos</h5>
                                </div>
                                <div class="compact-content">
                                    <?php if (!empty($historial_puntos)): ?>
                                        <div class="points-history">
                                            <?php foreach ($historial_puntos as $historial): ?>
                                                <div class="points-item">
                                                    <div>
                                                        <strong>Pedido #<?php echo $historial['pedido_id']; ?></strong>
                                                        <div class="text-white-50 small">
                                                            <?php echo date('d/m/Y', strtotime($historial['fecha_pedido'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-success fw-bold">
                                                        +<?php echo $historial['puntos_obtenidos']; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex justify-content-center align-items-center flex-grow-1">
                                            <p class="text-white-50 text-center">Aún no tienes puntos.<br>¡Realiza tu primera compra!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Estado de Seguridad y Próximo Nivel -->
                        <div class="col-lg-6">
                            <div class="content-box content-box-medium">
                                <div class="section-header">
                                    <i class="fas fa-shield-alt"></i>
                                    <h5 class="mb-0">Seguridad</h5>
                                </div>
                                <div class="compact-content">
                                    <div class="security-status">
                                        <div>
                                            <h6 class="mb-1">Autenticación 2FA</h6>
                                            <?php if (!empty($user_info['auth_secret'])): ?>
                                                <p class="text-success mb-0"><i class="fas fa-check-circle me-2"></i>Activada</p>
                                            <?php else: ?>
                                                <p class="text-warning mb-0"><i class="fas fa-exclamation-triangle me-2"></i>No activada</p>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?php if (!empty($user_info['auth_secret'])): ?>
                                                <span class="badge bg-success">Protegida</span>
                                            <?php else: ?>
                                                <a href="setup-2fa.php" class="btn btn-custom btn-sm">Activar</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="next-level-info mt-auto">
                                        <h6>Próximo: <?php echo $nivel_usuario['siguiente']; ?></h6>
                                        <?php if ($nivel_usuario['puntos_restantes'] > 0): ?>
                                            <div class="progress mb-2">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $nivel_usuario['progreso']; ?>%" 
                                                     aria-valuenow="<?php echo $nivel_usuario['progreso']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="text-white-50">
                                                <i class="fas fa-bullseye me-1"></i>
                                                <?php echo $nivel_usuario['puntos_restantes']; ?> puntos restantes
                                            </small>
                                        <?php else: ?>
                                            <p class="text-success mb-0 small">
                                                <i class="fas fa-trophy me-2"></i>¡Nivel máximo alcanzado!
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animación de las barras de progreso
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
            
            // Efecto de aparición para las tarjetas
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>