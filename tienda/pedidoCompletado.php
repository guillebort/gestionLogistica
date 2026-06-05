<?php
session_start();
// Validación básica de seguridad: debe venir con un ID y estar logueado
if (!isset($_GET['id']) || !isset($_SESSION['codigo'])) {
    header("Location: index.php");
    exit;
}
$idPedido = htmlspecialchars($_GET['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>¡Pago Completado! - LogisTFG</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh; font-family: 'Inter', sans-serif;">

    <div class="card border-0 shadow-lg rounded-4 p-5 text-center" style="max-width: 500px;">
        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-4 shadow-sm" style="width: 80px; height: 80px; font-size: 2.5rem;">
            ✔️
        </div>
        
        <h2 class="fw-bold text-dark mb-3">¡Pedido Confirmado!</h2>
        <p class="text-muted mb-2">El pago se ha procesado correctamente. La ruta ha sido registrada con el número de seguimiento:</p>
        <p class="fs-4 fw-bold text-primary font-monospace mb-4">#<?= str_pad($idPedido, 6, '0', STR_PAD_LEFT) ?></p>
        
        <div class="d-grid gap-3 mt-2">
            <a href="../controladores/descargarAlbaran.php?id=<?= $idPedido ?>" target="_blank" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                🖨️ Descargar Albarán (PDF)
            </a>
            
            <a href="../controladores/usuarioController.php" class="btn btn-outline-dark btn-lg rounded-pill fw-bold">
                Volver a mi Panel
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Elimina la variable 'carrito' que usa js/logica.js
            localStorage.removeItem('carrito');
            
            // Si en tu JS tienes una función para repintar el número de artículos del menú (el icono del carrito), llámala aquí.
            // Por ejemplo: si se llama actualizarBadgeCarrito(), descomenta la siguiente línea:
            // if (typeof actualizarBadgeCarrito === 'function') actualizarBadgeCarrito();
        });
    </script>
</body>
</html>