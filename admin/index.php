<?php
session_start();

require_once '../includes/controlSesion.php';
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

$codigoLogueado = $_SESSION['codigo'] ?? 0;
$con = AccesoBD::getInstance();
$usuarioActual = $con->obtenerUsuarioBD($codigoLogueado);

if ($usuarioActual == null || $usuarioActual->getRol() != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

// OBTENER DATOS AVANZADOS
$pedidosPendientes = $con->obtenerPedidosPendientesMapa();
$repartidores = $con->obtenerRepartidores();
$stats = $con->obtenerEstadisticas();

// Serializamos a JSON para que JavaScript pueda usar las coordenadas en el mapa Leaflet
$pedidosJson = json_encode($pedidosPendientes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Librerías de Leaflet para el Mapa (Open Source) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style> #mapa-logistico { height: 400px; width: 100%; border-radius: 8px; } </style>
</head>
<body class="bg-light">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        <!-- DASHBOARD DE ESTADÍSTICAS -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-success text-success bg-success bg-opacity-10">
                    <div class="card-body">
                        <h5 class="card-title">Ingresos Confirmados</h5>
                        <h2><?= number_format($stats['ingresos'], 2) ?> €</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-primary text-primary bg-primary bg-opacity-10">
                    <div class="card-body">
                        <h5 class="card-title">Envíos Completados</h5>
                        <h2><?= $stats['total_entregados'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-warning text-warning bg-warning bg-opacity-10">
                    <div class="card-body">
                        <h5 class="card-title">Bultos en Almacén</h5>
                        <h2><?= $stats['total_pendientes'] ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['mensajeAdmin'])) { ?>
            <div class="alert alert-success text-center">
                <?= $_SESSION['mensajeAdmin']; unset($_SESSION['mensajeAdmin']); ?>
            </div>
        <?php } ?>

        <!-- SECCIÓN DE LOGÍSTICA: MAPA Y ASIGNACIÓN -->
        <div class="row">
            <!-- COLUMNA IZQUIERDA: MAPA LEAFLET -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-header bg-dark text-white">🗺️ Visión Geo-Logística (Entregas Pendientes)</div>
                    <div class="card-body p-2">
                        <div id="mapa-logistico"></div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: ASIGNACIÓN MANUAL -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-header bg-primary text-white">📦 Asignación de Rutas</div>
                    <div class="card-body overflow-auto" style="max-height: 400px;">
                        <?php if (empty($pedidosPendientes)) { ?>
                            <div class="alert alert-info">Almacén vacío. Todo está en ruta.</div>
                        <?php } else { ?>
                            <ul class="list-group">
                                <?php foreach ($pedidosPendientes as $ped) { ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <strong>Pedido #<?= $ped['id'] ?></strong>
                                            <span class="text-muted"><?= htmlspecialchars($ped['cliente']) ?></span>
                                        </div>
                                        <div class="small mb-2 text-secondary">📍 <?= htmlspecialchars($ped['destino']) ?></div>
                                        <form action="../controladores/asignarRepartidor.php" method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="idPedido" value="<?= $ped['id'] ?>">
                                            <select name="idRepartidor" class="form-select form-select-sm" required>
                                                <option value="" disabled selected>Asignar repartidor...</option>
                                                <?php foreach ($repartidores as $rep) { ?>
                                                    <option value="<?= $rep['id'] ?>"><?= htmlspecialchars($rep['nombre']) ?></option>
                                                <?php } ?>
                                            </select>
                                            <button type="submit" class="btn btn-success btn-sm">OK</button>
                                        </form>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- SCRIPT PARA DIBUJAR EL MAPA LEAFLET DINÁMICO -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar mapa centrado por defecto en España/Valencia
            var map = L.map('mapa-logistico').setView([39.4699, -0.3762], 12);

            // Cargar capa de OpenStreetMap (Software Libre)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Importar datos de PHP a Javascript
            var pedidos = <?= $pedidosJson ?>;
            var limites = [];

            // Recorrer los pedidos y pintar un marcador por cada entrega pendiente
            pedidos.forEach(function(ped) {
                if (ped.latitud && ped.longitud && ped.latitud != "0.0") {
                    var marker = L.marker([ped.latitud, ped.longitud]).addTo(map);
                    marker.bindPopup("<b>Pedido #" + ped.id + "</b><br>" + ped.cliente + "<br><small>" + ped.destino + "</small>");
                    limites.push([ped.latitud, ped.longitud]);
                }
            });

            // Ajustar el zoom automáticamente si hay pedidos en el mapa
            if (limites.length > 0) {
                map.fitBounds(limites, {padding: [30, 30]});
            }
        });
    </script>
</body>
</html>