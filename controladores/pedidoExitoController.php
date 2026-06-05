<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>¡Pago Completado! - LogisTFG</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh; font-family: 'Inter', sans-serif;">

    <div class="card border-0 shadow-lg rounded-4 p-5 text-center" style="max-width: 500px;">
        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2rem;">
            ✔️
        </div>
        
        <h2 class="fw-bold text-dark mb-3">¡Pedido Confirmado!</h2>
        <p class="text-muted mb-4">El pago se ha procesado correctamente. La ruta ha sido registrada y despachada a nuestros almacenes.</p>
        
        <div class="d-grid gap-3">
            <a href="../controladores/descargarAlbaran.php?id=<?= htmlspecialchars($_GET['id'] ?? '') ?>" target="_blank" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                🖨️ Descargar Albarán (PDF)
            </a>
            
            <a href="usuario.php" class="btn btn-outline-dark btn-lg rounded-pill fw-bold">
                Volver a mi Panel
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            localStorage.removeItem('carrito');
            // Si tienes una función para actualizar la burbuja del carrito en tu menú, llámala aquí:
            // actualizarBadgeCarrito(); 
        });
    </script>
</body>
</html>