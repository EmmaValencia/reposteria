<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { header('Location: login.php'); exit; }
$db_config = [ 'servername' => "localhost", 'username' => "u686732311_Sabores", 'password' => "Cuchit@Pichichis9901221512", 'dbname' => "u686732311_saboresdc" ];
$user_id = $_SESSION['user_id'];
$user_info = null;
$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_info = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Sabores de Cristal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="responsive.css">
    
    <link rel="icon" type="image/png" sizes="16x16" href="Images/LOGO-min.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Images/LOGO-min.png">
    <link rel="apple-touch-icon" sizes="180x180" href="Images/LOGO-min.png">
    <link rel="shortcut" href="Images/LOGO-min.png">
    
    </head>
<body class="dashboard"> <main class="form-main-content"> <header class="main-header mb-4">
            <h1>Editar Perfil</h1>
        </header>
        <div class="form-content-box"> <form action="update-profile.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="profile_picture" class="form-label">Cambiar Foto de Perfil</label>
                    <input class="form-control" type="file" id="profile_picture" name="profile_picture" accept="image/png, image/jpeg">
                </div>
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user_info['nombre'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>" disabled>
                    <div class="form-text">El correo no se puede modificar.</div>
                </div>
                <hr class="my-4" style="border-color: rgba(203, 161, 53, 0.3);">
                
                <div class="d-flex justify-content-start gap-3">
                    <button class="aurora-button" type="submit"><div><span>Guardar Cambios</span></div></button>
                    <a href="profile.php" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </main>
</body>
</html>