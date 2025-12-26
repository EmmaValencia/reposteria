<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

require_once 'config.php';

$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($nombre) || empty($email) || empty($password) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

$conn = conectar_db();
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión con el servidor.']);
    exit;
}

$sql_check = "SELECT id FROM usuarios WHERE email = ? LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();

if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Este correo electrónico ya está registrado.']);
    $stmt_check->close();
    $conn->close();
    exit;
}
$stmt_check->close();

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$verification_token = bin2hex(random_bytes(32));

$sql_insert = "INSERT INTO usuarios (nombre, email, password, phone, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, 0)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("sssss", $nombre, $email, $password_hash, $phone, $verification_token);

if ($stmt_insert->execute()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emmaramirezvalencia@gmail.com';
        $mail->Password   = 'owpqxryjmqasvtyr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('emmaramirezvalencia@gmail.com', 'Sabores de Cristal');
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta en Sabores de Cristal';
        
        $verification_link = "https://saboresdecristal.site/verify-email.php?token=" . $verification_token;

        $mail->Body = "
            <h1>¡Bienvenido a Sabores de Cristal, {$nombre}!</h1>
            <p>Gracias por registrarte. Por favor, haz clic en el siguiente enlace para activar tu cuenta:</p>
            <p><a href='{$verification_link}' style='background-color:#7B2D26; color:white; padding:15px 25px; text-decoration:none; border-radius:5px; display:inline-block;'>Activar mi Cuenta</a></p>
            <p style='margin-top:20px;'>Si no te registraste en nuestro sitio, puedes ignorar este correo.</p>
        ";
        
        $mail->send();
        echo json_encode(['success' => true, 'message' => '¡Registro casi listo! Revisa tu correo para activar tu cuenta.']);
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        echo json_encode(['success' => false, 'message' => "La cuenta fue creada, pero no se pudo enviar el correo de verificación."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Hubo un error al registrar la cuenta.']);
}

$stmt_insert->close();
$conn->close();
?>