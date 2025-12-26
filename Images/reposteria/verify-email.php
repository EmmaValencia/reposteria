<?php
$db_config = [
    'servername' => "localhost",
    'username'   => "u686732311_Sabores",
    'password'   => "Cuchit@Pichichis9901221512",
    'dbname'     => "u686732311_saboresdc"
];

$token = $_GET['token'] ?? '';
$message = '';
$message_type = 'danger';

if (!empty($token)) {
    $conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
    if ($conn->connect_error) {
        $message = "Error del servidor. Inténtalo más tarde.";
    } else {
        $sql = "UPDATE usuarios SET is_verified = 1, verification_token = NULL WHERE verification_token = ? AND is_verified = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "¡Tu cuenta ha sido verificada con éxito! Ya puedes iniciar sesión.";
            $message_type = 'success';
        } else {
            $message = "Este enlace de verificación no es válido o ya ha sido utilizado.";
        }
        $stmt->close();
        $conn->close();
    }
} else {
    $message = "No se proporcionó un token de verificación.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" type="image/png" sizes="16x16" href="Images/LOGO-min.png">
<link rel="icon" type="image/png" sizes="32x32" href="Images/LOGO-min.png">
<link rel="apple-touch-icon" sizes="180x180" href="Images/LOGO-min.png">
<link rel="shortcut icon" href="Images/LOGO-min.png">||
    <meta charset="UTF-8">
    <title>Verificación de Cuenta - Sabores de Cristal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root { --negro: #1C1C1C; --dorado: #CBA135; --crema: #FAF3E0; --burdeos: #7B2D26; }
        body { background-color: var(--negro); font-family: 'Montserrat', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; color: var(--crema); }
        .message-container { max-width: 500px; text-align: center; padding: 40px; background-color: rgba(28, 28, 28, 0.9); border: 1px solid var(--dorado); border-radius: 15px; }
        h3 { font-family: 'Playfair Display', serif; color: var(--dorado); }
        .btn-primary { background-color: var(--burdeos); border: none; }
    </style>
</head>
<body>
    <div class="message-container">
        <h3>Verificación de Cuenta</h3>
        <div class="alert alert-<?php echo $message_type; ?> mt-3">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <a href="login.php" class="btn btn-primary mt-3">Ir a Iniciar Sesión</a>
    </div>
</body>
</html>