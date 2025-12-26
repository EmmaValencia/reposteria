<?php
header('Content-Type: application/json');
require_once '../config.php'; // Usa tu config.php principal

$conn = conectar_db(); // Usa tu función de conexión

$data = json_decode(file_get_contents('php://input'), true);

$id_ingrediente = $data['id_ingrediente'];

$sql = "SELECT activo_ingrediente FROM productos_pasteles WHERE id_ingrediente=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_ingrediente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$currentStatus = $row['activo_ingrediente'];
$stmt->close();

$newStatus = $currentStatus ? 0 : 1;

$sql = "UPDATE productos_pasteles SET activo_ingrediente=? WHERE id_ingrediente=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $newStatus, $id_ingrediente);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Estado del producto actualizado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado del producto']);
}

$stmt->close();
$conn->close();
?>