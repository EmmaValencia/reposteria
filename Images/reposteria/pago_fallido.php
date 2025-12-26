<?php
session_start();
require_once 'config.php';

if (isset($_GET['external_reference'])) {
    $pedido_id = (int)$_GET['external_reference'];
    $conn = conectar_db();
    $stmt = $conn->prepare("UPDATE pedidos SET estado = 'Fallido' WHERE id = ?");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Pago Fallido - Sabores de Cristal</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="style.css"></head>
<body class="dashboard">
    <div class="main-dashboard-content text-center" style="margin: auto; padding: 2rem;">
        <h1 style="color: var(--burdeos);">😥 Hubo un problema con tu pago</h1>
        <p class="text-white-50 fs-5 mt-3">El pago no pudo ser procesado.</p>
        <p class="text-white-50">Por favor, revisa los datos de tu tarjeta o intenta con otro método de pago.</p>
        <div class="mt-4">
            <a href="cart.php" class="aurora-button" style="text-decoration: none;"><div><span>Volver al Carrito</span></div></a>
        </div>
    </div>
</body></html>