<?php
session_start();
session_unset();
session_destroy();
header("Location: ../tienda/index.php"); // Volvemos al inicio directamente
exit;
?>