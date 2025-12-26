<?php
session_start();

$error_message = '';
$recaptcha_secret_key = "6Leckt8rAAAAALcjcW8_vPaM6WheyV9S1LQH9d_t";

$db_config = [
    'servername' => "localhost",
    'username'   => "u686732311_Sabores",
    'password'   => "Cuchit@Pichichis9901221512",
    'dbname'     => "u686732311_saboresdc"
];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = ['secret' => $recaptcha_secret_key, 'response' => $recaptcha_response];
    $options = ['http' => ['header'  => "Content-type: application/x-www-form-urlencoded\r\n", 'method'  => 'POST', 'content' => http_build_query($recaptcha_data)]];
    $context  = stream_context_create($options);
    $recaptcha_result = json_decode(file_get_contents($recaptcha_url, false, $context));

    if (!$recaptcha_result || !$recaptcha_result->success) {
        $error_message = 'Verificación de reCAPTCHA fallida. Inténtalo de nuevo.';
    } else {
        $conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
        
        if ($conn->connect_error) { 
            $error_message = 'Error en el servidor. Inténtalo más tarde.';
        } else {
            $sql = "SELECT id, password, auth_secret, is_verified FROM usuarios WHERE email = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    
                    if ($user['is_verified'] == 0) {
                        $error_message = 'Tu cuenta no ha sido verificada. Revisa tu correo electrónico para activarla.';
                    } else {
                        if (!empty($user['auth_secret'])) {
                            $_SESSION['2fa_user_id'] = $user['id'];
                            header('Location: verify-2fa.php');
                            exit;
                        } else {
                            $_SESSION['loggedin'] = true;
                            $_SESSION['user_id'] = $user['id'];
                            header('Location: profile.php'); 
                            exit;
                        }
                    }

                } else { 
                    $error_message = 'Correo o contraseña incorrectos.';
                }
            } else { 
                $error_message = 'Correo o contraseña incorrectos.';
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Iniciar Sesión - Sabores de Cristal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="icon" type="image/png" sizes="16x16" href="Images/LOGO-min.png">
<link rel="icon" type="image/png" sizes="32x32" href="Images/LOGO-min.png">
<link rel="apple-touch-icon" sizes="180x180" href="Images/LOGO-min.png">
<link rel="shortcut icon" href="Images/LOGO-min.png">
    <link rel="stylesheet" href="responsive.css">
    <style>
        :root { --negro: #1C1C1C; --dorado: #CBA135; --crema: #FAF3E0; --burdeos: #7B2D26; }
        body {
            background-color: var(--negro);
            background-image: url('Images/banner-min.gif');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--crema);
        }
        .auth-container { max-width: 450px; width: 100%; padding: 40px; background-color: rgba(28, 28, 28, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(203, 161, 53, 0.2); border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
        .auth-container h3 { font-family: 'Playfair Display', serif; color: var(--dorado); }
        .auth-container p.muted-small { color: var(--crema); opacity: 0.8; }
        .form-label { color: var(--dorado); }
        .form-control { background-color: #2a2a2a; border: 1px solid var(--dorado); color: var(--crema); }
        .form-control:focus { background-color: #333; border-color: var(--burdeos); color: var(--crema); box-shadow: none; }
        .btn-primary { background-color: var(--burdeos); border-color: var(--burdeos); padding: 12px; font-weight: bold; }
        .btn-primary:hover { background-color: #A53D34; border-color: #A53D34; }
        .auth-container .link { color: var(--dorado); text-decoration: none; }
        .auth-container .link:hover { color: var(--crema); }
    </style>
</head>
<body>
    <main class="container">
        <div class="auth-container">
            <h3 class="text-center">Iniciar Sesión</h3>
            <p class="text-center muted-small mb-4">Ingresa tus credenciales para continuar.</p>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <form id="loginForm" method="POST" action="login.php" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo</label>
                    <input id="email" name="email" type="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input id="password" name="password" type="password" class="form-control" required>
                </div>
                <div class="d-flex justify-content-center my-4">
                    <div class="g-recaptcha" data-sitekey="6Leckt8rAAAAADpLJnZRkr2_af4KxsXVuo17Og-q"></div>
                </div>
                <div class="d-grid">
                    <button name="login_submit" type="submit" class="btn btn-primary btn-lg">Continuar</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <a href="register.html" class="link">¿No tienes cuenta? Regístrate</a>
            </div>
        </div>
    </main>
</body>
</html>