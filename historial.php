<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'config.php'; 

$user_id = $_SESSION['user_id'];
$conn = conectar_db();

// Consulta para obtener el historial de pedidos
$pedidos = [];
$stmt_pedidos = $conn->prepare("SELECT id, total, fecha_pedido, estado FROM pedidos WHERE usuario_id = ? ORDER BY fecha_pedido DESC");
$stmt_pedidos->bind_param("i", $user_id);
$stmt_pedidos->execute();
$result_pedidos = $stmt_pedidos->get_result();
while($row = $result_pedidos->fetch_assoc()) {
    $pedidos[] = $row;
}
$stmt_pedidos->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos - Sabores de Cristal</title>
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
                        <li><a href="setup-2fa.php">Seguridad (2FA)</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </label>
        </div>

        <div class="main-dashboard-content">
            <header class="mb-4"><h1>Historial de Pedidos</h1></header>
            
            <div class="content-box">
                <h4><i class="fas fa-shopping-bag"></i> Mis Compras Anteriores</h4>
                <hr style="border-color: rgba(255,255,255,0.2);">
                <div class="table-responsive">
                    <table class="table table-dark table-striped mt-3">
                        <thead>
                            <tr>
                                <th scope="col"># Pedido</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Total</th>
                                <th scope="col">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pedidos)): ?>
                                <tr><td colspan="4" class="text-center text-white-50">No tienes pedidos todavía.</td></tr>
                            <?php else: ?>
                                <?php foreach($pedidos as $pedido): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($pedido['id']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($pedido['estado']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>