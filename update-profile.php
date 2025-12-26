<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$db_config = [
    'servername' => "localhost",
    'username'   => "u686732311_Sabores",
    'password'   => "Cuchit@Pichichis9901221512",
    'dbname'     => "u686732311_saboresdc"
];

$user_id = $_SESSION['user_id'];
$upload_dir = 'uploads/avatars/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
    if ($conn->connect_error) { die("Error de conexión: " . $conn->connect_error); }

    if (!empty($_POST['nombre'])) {
        $new_name = $_POST['nombre'];
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file_tmp_path = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $new_file_name = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
        $dest_path = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $stmt = $conn->prepare("UPDATE usuarios SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $new_file_name, $user_id);
            $stmt->execute();
        }
    }

    $stmt->close();
    $conn->close();

    header('Location: profile.php?status=success');
    exit;
}
?>