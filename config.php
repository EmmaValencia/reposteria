<?php
// config.php

// --- 1. CONFIGURACIÓN DE LA BASE DE DATOS ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u686732311_Sabores');
define('DB_PASSWORD', 'Cuchit@Pichichis9901221512');
define('DB_NAME', 'u686732311_saboresdc');

// --- 2. CLAVES DE APIS Y SERVICIOS ---
define('RECAPTCHA_SECRET_KEY', '6Leckt8rAAAAALcjcW8_vPaM6WheyV9S1LQH9d_t');

// ===================================================================
// ===== INICIO DE LA IMPLEMENTACIÓN: CREDENCIALES DE PAYPAL SANDBOX =====
// ===================================================================
// Tu Client ID para "Sabores de Cristal Tienda" ya está configurado.
define('PAYPAL_CLIENT_ID', 'Aby7J6jJOMhzHghyko6RAv_oVliAY3qKJy_DQ_XSikLjq46MQ6djxITPZ3TIGI5Q-TaJHXxIKqWFJg5A');

// ***** ACCIÓN REQUERIDA *****: Reemplaza el siguiente valor con tu Client Secret.
define('PAYPAL_CLIENT_SECRET', 'EHvMY-AKQJiig_Yy34eR5w91yjNdS3usFgGchcHZgAWvD0p_hS7j7BRiAoWO0zcwA7lgQDmS76T6Z7p8');
// ===================================================================
// ===== FIN DE LA IMPLEMENTACIÓN =====
// ===================================================================

// --- 3. FUNCIÓN DE CONEXIÓN GLOBAL ---
function conectar_db() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        // En un entorno real, es mejor registrar el error que mostrarlo al usuario.
        error_log("Error de conexión a la base de datos: " . $conn->connect_error);
        die("Error en el servidor. Inténtalo más tarde.");
    }
    return $conn;
}
?>