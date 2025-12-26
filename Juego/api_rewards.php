<?php
// api_rewards.php
require 'db_config.php';
header('Content-Type: application/json');

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

$conn->set_charset('utf8');

// 1. Obtener y validar los datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$user_id = $data['user_id'] ?? null;
$score = $data['score'] ?? null;

if (!is_int($user_id) || !is_int($score) || $user_id <= 0 || $score < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos (ID de usuario o puntuación).']);
    exit;
}

// 2. Lógica de Recompensas
function calculateRewardPoints($score) {
    $points = 0;
    
    // Aquí defines tu sistema de premios
    if ($score >= 500) {
        $points = 50; // ¡Gran trabajo!
    } elseif ($score >= 200) {
        $points = 20; // Premio modesto
    } else {
        $points = 5; // Premio de consolación
    }
    
    return $points;
}

$reward_points = calculateRewardPoints($score);

// 3. Guardar la recompensa en la base de datos
if ($reward_points > 0) {
    
    // Asegúrate de que tu tabla 'recompensas' tenga los campos 'usuario_id' y 'puntos_obtenidos'
    $sql = "INSERT INTO recompensas (usuario_id, descripcion, puntos_obtenidos, fecha_obtencion) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    
    $description = "Puntos ganados en el minijuego de repostería (Puntuación: $score)";
    $stmt->bind_param("isi", $user_id, $description, $reward_points);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'reward_points' => $reward_points,
            'message' => 'Recompensa asignada con éxito.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al guardar la recompensa en la base de datos.']);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => true,
        'reward_points' => 0,
        'message' => 'No se ganó ningún premio con esta puntuación.'
    ]);
}

$conn->close();
?>