<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'config.php'; 

$user_id = $_SESSION['user_id'];
$conn = conectar_db();

// Consulta para obtener las recompensas
$recompensas = [];
$stmt_recompensas = $conn->prepare("SELECT codigo_cupon, descripcion, valor_descuento, fecha_creacion, utilizado FROM recompensas WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
$stmt_recompensas->bind_param("i", $user_id);
$stmt_recompensas->execute();
$result_recompensas = $stmt_recompensas->get_result();
while ($row = $result_recompensas->fetch_assoc()) {
    $recompensas[] = $row;
}
$stmt_recompensas->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Recompensas - Sabores de Cristal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" sizes="16x16" href="Images/LOGO-min.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Images/LOGO-min.png">
    <link rel="apple-touch-icon" sizes="180x180" href="Images/LOGO-min.png">
    <link rel="shortcut icon" href="Images/LOGO-min.png">
     <style>
        .content-box { background-color: rgba(0,0,0,0.3); padding: 2rem; border-radius: 15px; }
        .coupon { background-color: rgba(0,0,0,0.4); border-left: 5px solid var(--dorado); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: white; }
        .coupon-code { font-family: 'Courier New', Courier, monospace; font-size: 1.2rem; font-weight: bold; color: var(--dorado); }
    </style>
</head>
<body class="dashboard">

    <div class="swanky">
        <div class="swanky_wrapper">
            <input type="radio" name="radio" id="mi-perfil">
            <label for="mi-perfil">
                <i class="fas fa-user-circle"></i>
                <span>Mi Perfil</span>
                <div class="lil_arrow"></div>
                <div class="bar"></div>
                <div class="swanky_wrapper__content">
                    <ul>
                        <li><a href="profile.php">Ver mi Perfil</a></li>
                        <li><a href="edit-profile.php">Editar Información</a></li>
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

            <input type="radio" name="radio" id="mis-recompensas" checked>
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
            <header class="mb-4"><h1>Mis Recompensas</h1></header>
            
            <div class="content-box mb-4">
                <h4><i class="fas fa-gift text-warning"></i> Mis Cupones de Recompensa</h4>
                <hr style="border-color: rgba(255,255,255,0.2);">
                <?php if (isset($_GET['compra']) && $_GET['compra'] === 'exitosa'): ?>
                    <div class="alert alert-success">¡Gracias por tu compra! Tus puntos han sido actualizados.</div>
                <?php endif; ?>
                <?php if (empty($recompensas)): ?>
                    <p class="text-white-50">Aún no has ganado ninguna recompensa. ¡Sigue comprando para acumular puntos!</p>
                <?php else: ?>
                    <?php foreach ($recompensas as $recompensa): ?>
                        <div class="coupon">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5><?php echo htmlspecialchars($recompensa['descripcion']); ?></h5>
                                    <p class="coupon-code mb-0"><?php echo htmlspecialchars($recompensa['codigo_cupon']); ?></p>
                                    <small class="text-white-50">Obtenido el: <?php echo date('d/m/Y', strtotime($recompensa['fecha_creacion'])); ?></small>
                                </div>
                                <div>
                                    <?php if ($recompensa['utilizado']): ?>
                                        <span class="badge bg-secondary">Utilizado</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Disponible</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>