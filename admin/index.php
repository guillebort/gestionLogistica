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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
    
    <style> 
        #mapa-logistico { 
            height: 450px; 
            width: 100%; 
            border-radius: 1rem; 
            z-index: 1; 
        } 
        /* Ocultar barra de scroll en la lista de asignación para diseño más limpio */
        .scroll-invisible::-webkit-scrollbar { width: 6px; }
        .scroll-invisible::-webkit-scrollbar-thumb { background-color: #dee2e6; border-radius: 10px; }
    </style>
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        
        <div class="mb-4">
            <h3 class="fw-bold text-dark">Panel de Control General</h3>
            <p class="text-muted">Resumen de la actividad operativa y financiera.</p>
        </div>

        <!-- DASHBOARD DE ESTADÍSTICAS MODERNIZADO -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">💶</div>
                        <div>
                            <p class="text-muted mb-0 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Ingresos Confirmados</p>
                            <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['ingresos'], 2) ?> €</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">📦</div>
                        <div>
                            <p class="text-muted mb-0 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Envíos Completados</p>
                            <h3 class="fw-bold mb-0 text-dark"><?= $stats['total_entregados'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning-emphasis rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">🕒</div>
                        <div>
                            <p class="text-muted mb-0 fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Bultos en Almacén</p>
                            <h3 class="fw-bold mb-0 text-dark"><?= $stats['total_pendientes'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['mensajeAdmin'])) { ?>
            <div class="alert alert-success text-center rounded-4 shadow-sm border-0 mb-4 fw-medium">
                <?= $_SESSION['mensajeAdmin']; unset($_SESSION['mensajeAdmin']); ?>
            </div>
        <?php } ?>

        <!-- SECCIÓN DE LOGÍSTICA: MAPA Y ASIGNACIÓN -->
        <div class="row g-4">
            
            <!-- COLUMNA IZQUIERDA: MAPA LEAFLET -->
            <div class="col-lg-7">
                <div class="card shadow-sm h-100 border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold text-dark mb-0">🗺️ Visión Geo-Logística</h5>
                        <p class="text-muted small">Ubicación de entregas pendientes</p>
                    </div>
                    <div class="card-body p-4">
                        <div id="mapa-logistico" class="shadow-sm border border-secondary border-opacity-25"></div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: ASIGNACIÓN MANUAL -->
            <div class="col-lg-5">
                <div class="card shadow-sm h-100 border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Asignación de Rutas</h5>
                            <p class="text-muted small mb-0">Despacha los pedidos al personal</p>
                        </div>
                        <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm"><?= count($pedidosPendientes) ?> Pdte.</span>
                    </div>
                    
                    <div class="card-body p-4 overflow-auto scroll-invisible" style="max-height: 500px;">
                        <?php if (empty($pedidosPendientes)) { ?>
                            <div class="text-center py-5">
                                <div style="font-size: 3rem; opacity: 0.5;" class="mb-2">🧹</div>
                                <h6 class="fw-bold text-dark">Almacén despejado</h6>
                                <p class="text-muted small">Todos los pedidos han sido asignados y están en ruta.</p>
                            </div>
                        <?php } else { ?>
                            
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($pedidosPendientes as $ped) { ?>
                                    <div class="bg-light p-3 rounded-4 border-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-dark">Pedido #<?= $ped['id'] ?></span>
                                            <span class="badge bg-white text-dark border fw-medium px-2 shadow-sm">👤 <?= htmlspecialchars($ped['cliente']) ?></span>
                                        </div>
                                        <div class="small mb-3 text-secondary d-flex align-items-start gap-1">
                                            <span>📍</span> 
                                            <span><?= htmlspecialchars($ped['destino']) ?></span>
                                        </div>
                                        
                                        <form action="../controladores/asignarRepartidor.php" method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="idPedido" value="<?= $ped['id'] ?>">
                                            <select name="idRepartidor" class="form-select form-select-sm rounded-pill px-3 shadow-none border-secondary-subtle" required>
                                                <option value="" disabled selected>Elegir repartidor...</option>
                                                <?php foreach ($repartidores as $rep) { ?>
                                                    <option value="<?= $rep['id'] ?>"><?= htmlspecialchars($rep['nombre']) ?></option>
                                                <?php } ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">Asignar</button>
                                        </form>
                                    </div>
                                <?php } ?>
                            </div>
                            
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- SCRIPT PARA DIBUJAR EL MAPA LEAFLET DINÁMICO -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

            // Icono personalizado para los marcadores
            var iconoPedido = L.divIcon({
                html: '<div style="background-color: #0d6efd; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); font-weight: bold; font-size: 10px;">📦</div>',
                className: '',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            // Recorrer los pedidos y pintar un marcador por cada entrega pendiente
            pedidos.forEach(function(ped) {
                if (ped.latitud && ped.longitud && ped.latitud != "0.0") {
                    var marker = L.marker([ped.latitud, ped.longitud], {icon: iconoPedido}).addTo(map);
                    marker.bindPopup("<div class='text-center'><b>Pedido #" + ped.id + "</b><br><span class='text-muted'>" + ped.cliente + "</span><br><small>" + ped.destino + "</small></div>");
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