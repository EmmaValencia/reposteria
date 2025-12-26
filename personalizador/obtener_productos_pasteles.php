<?php
header('Content-Type: application/json');
require_once '../config.php'; // Usa tu config.php principal

$conn = conectar_db(); // Usa tu función de conexión

$sql = "SELECT * FROM productos_pasteles ORDER BY categoria_ingrediente, nombre_ingrediente";
$result = $conn->query($sql);

$products = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();

echo json_encode($products);
?>