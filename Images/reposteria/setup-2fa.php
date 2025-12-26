<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'vendor/autoload.php';
$ga = new PHPGangsta_GoogleAuthenticator();

$db_config = [
    'servername' => "localhost",
    'username'   => "u686732311_Sabores",
    'password'   => "Cuchit@Pichichis9901221512",
    'dbname'     => "u686732311_saboresdc"
];

$current_user_id = $_SESSION['user_id']; 
$secret = $ga->createSecret();

$conn = new mysqli($db_config['servername'], $db_config['username'], $db_config['password'], $db_config['dbname']);
if ($conn->connect_error) { die("Error de conexión: " . $conn->connect_error); }

$stmt = $conn->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_email_for_qr = $user['email'];

$stmt = $conn->prepare("UPDATE usuarios SET auth_secret = ? WHERE id = ?");
$stmt->bind_param("si", $secret, $current_user_id);
$stmt->execute();
$stmt->close();
$conn->close();

$qrCodeUrl = $ga->getQRCodeGoogleUrl('SaboresDeCristal (' . $user_email_for_qr . ')', $secret);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar 2FA - Sabores de Cristal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="icon" type="image/png" sizes="16x16" href="Images/LOGO-min.png">
    <link rel="icon" type="image/png" sizes="32x32" href="Images/LOGO-min.png">
    <link rel="apple-touch-icon" sizes="180x180" href="Images/LOGO-min.png">
    <link rel="shortcut icon" href="Images/LOGO-min.png">
    
    <style>
        :root { 
            --negro: #1C1C1C; 
            --dorado: #CBA135; 
            --crema: #FAF3E0; 
            --burdeos: #7B2D26; 
            --gris-oscuro: #2a2a2a;
            --burdeos-claro: #A53D34;
        }
        body { 
            background-image: url('Images/Cupcake1.gif'); /* Usa la imagen de fondo que subiste */
            background-size: cover;
            background-position: center;
        }

        .setup-container { 
            max-width: 650px; /* Un poco más ancho */
            margin: 50px auto; 
            padding: 3rem; 
            background-color: var(--negro); 
            border-radius: 15px; 
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4); /* Sombra para profundidad */
            border-top: 5px solid var(--dorado); /* Línea elegante */
        }
        h1 { 
            font-family: 'Playfair Display', serif; 
            color: var(--dorado);
            margin-bottom: 0.5rem;
        }
        h4 {
            color: var(--dorado); /* Pasos destacados en dorado */
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 0.8rem;
        }
        h4 i {
            margin-right: 10px;
        }
        .qr-code { 
            padding: 20px; 
            background-color: white; 
            display: inline-block; 
            border-radius: 10px; 
            box-shadow: 0 5px 15px rgba(203, 161, 53, 0.5); /* Sombra dorada suave */
        }
        .key-box {
            background-color: var(--gris-oscuro);
            color: var(--crema);
            padding: 1rem 1.5rem;
            display: inline-block;
            border-radius: 8px;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
            border: 2px solid var(--dorado); /* Borde dorado para clave */
            user-select: all; /* Permite una fácil selección del texto */
            cursor: pointer;
        }
        .btn-custom { 
            background-color: var(--burdeos); 
            border-color: var(--burdeos); 
            color: var(--crema); 
            padding: 12px 30px;
            font-weight: 500;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .btn-custom:hover {
            background-color: var(--burdeos-claro); /* Usar el burdeos claro en hover */
            border-color: var(--burdeos-claro);
            color: var(--crema);
        }
        .alert-success-custom {
            background-color: var(--dorado);
            color: var(--negro);
            font-weight: 700;
            border: none;
            padding: 1.5rem;
            margin-top: 2.5rem;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container text-center setup-container">
        <div class="mb-4">
            <img src="Images/LOGO-min.png" alt="Sabores de Cristal Logo" style="height: 60px;">
        </div>

        <h1>Autenticación de Dos Pasos (2FA)</h1>
        <p class="lead text-white-50">Añade una capa extra de seguridad a tu cuenta con una app de autenticación.</p>
        <hr class="border-secondary mt-4">

        <h4><i class="fas fa-qrcode"></i> Paso 1: Escanea el Código QR</h4>
        <p>Abre tu aplicación de autenticación (Google Authenticator, Microsoft Authenticator o Authy) y escanea la siguiente imagen:</p>
        
        <div class="qr-code my-4">
            <img src="<?php echo $qrCodeUrl; ?>" alt="Código QR de Autenticación 2FA" style="width: 200px; height: 200px;">
        </div>
        
        <h4><i class="fas fa-key"></i> Paso 2: Clave Manual (Alternativa)</h4>
        <p class="text-white-50">Si no puedes escanear el código, introduce esta clave manualmente en la aplicación:</p>
        
        <div class="key-box mt-3 mb-4">
            <?php echo $secret; ?>
        </div>
        
        <hr class="mt-5 border-secondary">
        <div class="alert-success-custom">
            <i class="fas fa-lock me-2"></i> 
            ¡Tu clave de autenticación ha sido generada! Guarda tu código secreto.
        </div>
        
        <a href="profile.php" class="btn btn-custom mt-4">
            <i class="fas fa-user"></i> Volver a mi Perfil
        </a>
    </div>
    <script>
        document.querySelector('.key-box').addEventListener('click', function() {
            const range = document.createRange();
            range.selectNodeContents(this);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            // Opcional: mostrar un mensaje de "Copiado"
            // alert('Clave secreta copiada al portapapeles.');
        });
    </script>
</body>
</html>