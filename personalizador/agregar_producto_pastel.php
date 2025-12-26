<?php
header('Content-Type: application/json');
require_once '../config.php'; // Usa tu config.php principal

$conn = conectar_db(); // Usa tu función de conexión

$data = json_decode(file_get_contents('php://input'), true);

$nombre_ingrediente = $data['nombre_ingrediente'];
$categoria_ingrediente = $data['categoria_ingrediente'];
$precio_ingrediente = $data['precio_ingrediente'];
$imagen_ingrediente = $data['imagen_ingrediente'];
$textura_ingrediente = $data['textura_ingrediente'];
$descripcion_ingrediente = $data['descripcion_ingrediente'];

$sql = "INSERT INTO productos_pasteles (nombre_ingrediente, categoria_ingrediente, precio_ingrediente, imagen_ingrediente, textura_ingrediente, descripcion_ingrediente) VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdsss", $nombre_ingrediente, $categoria_ingrediente, $precio_ingrediente, $imagen_ingrediente, $textura_ingrediente, $descripcion_ingrediente);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto añadido correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al añadir el producto']);
}

$stmt->close();
$conn->close();
?>