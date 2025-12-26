<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Habilitar errores para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "=== DEBUG SESSION ===\n";
var_dump($_SESSION);
echo "=== DEBUG POST ===\n";
var_dump($_POST);
echo "=== DEBUG GET ===\n";
var_dump($_GET);

// Probar conexión a la base de datos
try {
    $conn = conectar_db();
    echo "=== DEBUG DB CONNECTION ===\n";
    echo "Conexión exitosa\n";
    
    // Probar consulta de productos
    $test_id = 11; // El ID que estás intentando agregar
    $stmt = $conn->prepare("SELECT id, nombre, precio, imagen FROM productos WHERE id = ?");
    $stmt->bind_param("i", $test_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();
    
    echo "=== DEBUG PRODUCT QUERY ===\n";
    var_dump($producto);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "=== DEBUG DB ERROR ===\n";
    echo "Error: " . $e->getMessage() . "\n";
}
?>