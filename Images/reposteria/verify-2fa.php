<?php
session_start();
require_once 'vendor/autoload.php';

// Redirigir si no está en el proceso 2FA
if (!isset($_SESSION['2fa_user_id'])) {
    header('Location: login.php');
    exit;
}

$ga = new PHPGangsta_GoogleAuthenticator();
$error_message = '';

// Configuración de la Base de Datos (manteniendo tu original)
$db_config = [
    'servername' => "localhost",
    'username'   => "u686732311_Sabores",
    'password'   => "Cuchit@Pichichis9901221512",
    'dbname'     => "u686732311_saboresdc"
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $_POST['code'] ?? '';
    $user_id = $_SESSION['2fa_user_id'];

    $conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
    
    $stmt = $conn->prepare("SELECT auth_secret, email, is_verified FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($user && $user['auth_secret']) {
        
        // Tolerancia de 2 ventanas de tiempo (30 segundos * 2 = 60 segundos)
        $tolerance_windows = 2; 
        $checkResult = $ga->verifyCode($user['auth_secret'], $code, $tolerance_windows);
        
        if ($checkResult) {
            unset($_SESSION['2fa_user_id']);
            
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user_id; 
            $_SESSION['email'] = $user['email'];
            
            header('Location: index.php'); // Redirige a la página principal
            exit;
        } else {
            $error_message = "El código de verificación es incorrecto. Inténtalo de nuevo.";
        }
    } else {
        $error_message = "Error. No se pudo encontrar tu cuenta o la configuración 2FA es incorrecta.";
        unset($_SESSION['2fa_user_id']); // Eliminar la clave para evitar reintentos si hay error grave
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de 2 Pasos - Sabores de Cristal</title>
    
    <link rel="icon" type="image/png" sizes="32x32" href="Images/LOGO-min.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"> 
    
    <style>
        body {
            background-image: url('Images/Cupcake1.gif'); /* Usa la imagen de fondo que subiste */
            background-size: cover;
            background-position: center;
        }
        /* Overlay oscuro para que el contenido resalte */
        .min-vh-100::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7); /* Oscuro más sólido para enfocar */
            z-index: 0;
        }
        .auth-container {
            position: relative;
            z-index: 1; /* Asegura que el contenedor esté sobre el overlay */
            background-color: var(--negro); /* Fondo sólido sin transparencia para minimalismo */
            /* Quitar backdrop-filter para eliminar el efecto glassmorphism */
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8); /* Sombra más pronunciada */
            border: 2px solid var(--dorado); /* Borde dorado más grueso y notorio */
        }
    </style>

</head>
<body>
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <main class="container">
            <div class="auth-container"> 
                <div class="text-center mb-4">
                     <img src="Images/LOGO-min.png" alt="Logo" style="width: 50px; margin-bottom: 1rem;"> 
                    <h3 class="mb-1">Acceso Seguro</h3>
                    <p class="muted-small">
                        Hemos activado la Verificación de 2 Pasos.
                    </p>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger text-center small py-2" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="verify-2fa.php">
                    <div class="mb-4">
                        <label for="code" class="form-label text-center w-100">CÓDIGO TEMPORAL (6 DÍGITOS)</label>
                        <input type="text" id="code" name="code" class="form-control" maxlength="6" inputmode="numeric" required autofocus>
                    </div>
                    
                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-lg">AUTENTICAR Y ENTRAR</button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <a href="logout.php" class="text-white-50 small">
                        ¿Tienes problemas para acceder?
                    </a>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>