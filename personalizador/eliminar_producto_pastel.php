<?php
header('Content-Type: application/json');
require_once '../config.php'; // Usa tu config.php principal

$conn = conectar_db(); // Usa tu función de conexión

$data = json_decode(file_get_contents('php://input'), true);

$id_ingrediente = $data['id_ingrediente'];

$sql = "DELETE FROM productos_pasteles WHERE id_ingrediente=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_ingrediente);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto']);
}

$stmt->close();
$conn->close();
?>