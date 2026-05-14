<?php
    session_start();
    // 1. CAPA DE SEGURIDAD PROFESIONAL:
    // Si no hay mensaje de éxito en la sesión, significa que no viene de FinalizarPedidoServlet.
    // Redirigimos a productos para evitar que vean una página de "victoria" vacía.
    $mensajeExito = $_SESSION["mensaje"] ?? null;
    if ($mensajeExito == null) {
        header("Location: productos.php");
        exit; 
    }
    // Una vez leído, lo eliminamos para que el mensaje sea "de un solo uso"
    unset($_SESSION["mensaje"]);
    $nombreUsuario = $_SESSION['nombreUsuario'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Reserva - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <?php include '../includes/menu.php'; ?>

    <main class="container my-5 text-center">
        <div class="card shadow-lg border-0 mx-auto">
            <div class="card-body py-5 px-4">
                <div class="success-icon mb-4">✅</div>
                
                <h2 class="fw-bold mb-3">¡Reserva Completada con Éxito!</h2>
                <p class="text-muted mb-4">Hemos procesado tu solicitud. Los detalles de tu envío ya están en manos de nuestra central logística.</p>
                
                <div class="p-4 mb-4">
                    <p class="text-uppercase small fw-bold text-secondary mb-2">Referencia de Seguimiento</p>
                    <div class="h4 text-primary font-monospace mb-0">
                        <?php echo htmlspecialchars($mensajeExito); ?>
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center text-start mb-4" role="alert">
                    <div>
                        <small>Recibirás un correo electrónico con el resumen detallado y la asignación del repartidor en los próximos minutos.</small>
                    </div>
                </div>

                <div class="d-grid gap-3 d-sm-flex justify-content-sm-center mt-2">
                    <a href="usuario.php" class="btn btn-dark btn-lg px-5">Ver mis pedidos</a>
                    <a href="productos.php" class="btn btn-outline-secondary btn-lg px-4">Nueva reserva</a>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
    
    <script src="../js/logica.js"></script>
    
</body>
</html>