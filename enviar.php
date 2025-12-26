<?php
require_once 'config.php';
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Hubo un problema con la solicitud.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = strip_tags(trim($_POST["nombre"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $asunto_usuario = strip_tags(trim($_POST["asunto"]));
    $mensaje = trim($_POST["mensaje"]);

    if (empty($nombre) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($asunto_usuario) || empty($mensaje)) {
        $response['message'] = 'Por favor, completa todos los campos del formulario.';
    } else {
        $conn = conectar_db();
        
        // Asumiendo que tienes una tabla 'contactos' para guardar los mensajes.
        // Si no existe, deberás crearla en tu base de datos.
        $sql = "INSERT INTO contactos (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $email, $asunto_usuario, $mensaje);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = '¡Gracias! Tu mensaje ha sido recibido.';
        } else {
            $response['message'] = 'Oops! Hubo un problema al guardar tu mensaje.';
        }
        $stmt->close();
        $conn->close();
    }
}

echo json_encode($response);
?>