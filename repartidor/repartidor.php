<?php
session_start();
require_once '../modelos/AccesoBD.php';
// Aquí podrías añadir una comprobación de ROL para que solo entren repartidores
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Reparto - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">
    <header class="bg-dark text-white text-center py-3">
        <h5>🚚 LogisTFG - Ruta de Entrega</h5>
    </header>

    <main class="container my-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body text-center">
                <h6>Ruta actual: <strong>Valencia Centro</strong></h6>
                <div class="ratio ratio-16x9 my-3">
                    <!-- Mapa de ejemplo. En implementación real, usarías Leaflet o Google Maps API -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d12318.12345!2d-0.376!3d39.469" style="border:0;" allowfullscreen=""></iframe>
                </div>
            </div>
        </div>

        <h6 class="text-muted text-uppercase small fw-bold">Paradas pendientes</h6>
        <div class="list-group shadow-sm">
            <!-- Este bloque se generaría con un foreach desde la BD en la versión final -->
            <div class="list-group-item p-3" id="pedido-1">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Pedido #24</h5>
                    <span class="badge bg-warning text-dark">Pendiente</span>
                </div>
                <p class="mb-2 text-secondary">📍 Destino: Calle Colón 45, Valencia</p>
                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm flex-grow-1" onclick="marcarParada('pedido-1', 'entregado')">Entregado</button>
                    <button class="btn btn-outline-danger btn-sm flex-grow-1" onclick="marcarParada('pedido-1', 'incidencia')">Incidencia</button>
                </div>
            </div>
        </div>
    </main>

    <script src="../js/logica.js"></script>
</body>
</html>