<?php
// includes/config.php
define('DB_HOST', 'localhost');
define('DB_PORT', '3305'); // O 3306 según tu entorno
define('DB_NAME', 'gestionlogistica');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

// --- CREEDENCIALES DE CORREO (PHPMailer) ---
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'tu_correo_tfg@gmail.com');
define('MAIL_PASS', 'tu_contraseña_de_aplicacion');
define('MAIL_FROM_EMAIL', 'no-reply@logistfg.es');
define('MAIL_FROM_NAME', 'LogisTFG');
?>
?>