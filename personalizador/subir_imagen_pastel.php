<?php
header('Content-Type: application/json');

// La ruta ahora es relativa a la carpeta raíz 'public_html'
$uploadDir = '../Images/pasteles/'; 
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 2 * 1024 * 1024; // 2MB

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibió ninguna imagen']);
    exit;
}

$file = $_FILES['image'];

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
    exit;
}

if ($file['size'] > $maxFileSize) {
    echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande (máximo 2MB)']);
    exit;
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;

if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
}
?>