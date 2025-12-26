<?php
header('Content-Type: application/json');
require_once '../config.php'; // Usa tu config.php principal

$conn = conectar_db(); // Usa tu función de conexión

$data = json_decode(file_get_contents('php://input'), true);

$id_ingrediente = $data['id_ingrediente'];
$nombre_ingrediente = $data['nombre_ingrediente'];
$categoria_ingrediente = $data['categoria_ingrediente'];
$precio_ingrediente = $data['precio_ingrediente'];
$imagen_ingrediente = $data['imagen_ingrediente'];
$textura_ingrediente = $data['textura_ingrediente'];
$descripcion_ingrediente = $data['descripcion_ingrediente'];

$sql = "UPDATE productos_pasteles SET nombre_ingrediente=?, categoria_ingrediente=?, precio_ingrediente=?, imagen_ingrediente=?, textura_ingrediente=?, descripcion_ingrediente=? WHERE id_ingrediente=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdsssi", $nombre_ingrediente, $categoria_ingrediente, $precio_ingrediente, $imagen_ingrediente, $textura_ingrediente, $descripcion_ingrediente, $id_ingrediente);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto']);
}

$stmt->close();
$conn->close();
?>