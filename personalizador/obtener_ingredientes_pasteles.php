<?php
header('Content-Type: application/json');
require_once '../config.php'; // Usa tu config.php principal

$conn = conectar_db(); // Usa tu función de conexión

$sql = "SELECT * FROM productos_pasteles WHERE activo_ingrediente = 1 ORDER BY categoria_ingrediente, nombre_ingrediente";
$result = $conn->query($sql);

$ingredients = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ingredients[] = $row;
    }
}

$conn->close();

echo json_encode($ingredients);
?>