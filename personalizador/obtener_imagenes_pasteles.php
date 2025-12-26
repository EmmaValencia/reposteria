<?php
header('Content-Type: application/json');

$imageDir = 'Images/pasteles/';
$images = [];

if (is_dir($imageDir)) {
    $files = scandir($imageDir);
    
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && !is_dir($imageDir . $file)) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = $file;
            }
        }
    }
}

echo json_encode($images);
?>