<?php
session_start();
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Pago Pendiente - Sabores de Cristal</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="style.css"></head>
<body class="dashboard">
    <div class="main-dashboard-content text-center" style="margin: auto; padding: 2rem;">
        <h1 style="color: var(--dorado);">⏳ Tu pago está pendiente</h1>
        <p class="text-white-50 fs-5 mt-3">Estamos esperando la confirmación de tu pago.</p>
        <p class="text-white-50">Si pagaste en efectivo (ej. OXXO), puede tardar hasta 24 horas en acreditarse. Te notificaremos cuando se confirme.</p>
        <div class="mt-4">
            <a href="historial.php" class="aurora-button" style="text-decoration: none;"><div><span>Ver mis Pedidos</span></div></a>
        </div>
    </div>
</body></html>