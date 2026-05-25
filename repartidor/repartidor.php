<?php

require_once '../includes/controlSesion.php';
require_once '../modelos/AccesoBD.php';
session_start();
// 1. Validación de seguridad y rol
$rolUsuario = $_SESSION['rol'] ?? 0; 
$idRepartidor = $_SESSION['codigo'] ?? 0;

// Si no está logueado o no es repartidor (rol 2), lo echamos a la pantalla de login
if ($idRepartidor <= 0 || $rolUsuario != 2) {
    header("Location: ../tienda/login.php");
    exit;
}

// 2. Conexión al Modelo para obtener los datos de MySQL
$con = AccesoBD::getInstance();
$nombreRepartidor = $_SESSION['nombreUsuario'] ?? 'Repartidor';

// 3. Obtenemos las rutas/pedidos asignados a este repartidor en concreto
$paradas = $con->obtenerRutasRepartidor($idRepartidor);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>App Reparto - LogisTFG</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="../repartidor/manifest.json">
    <meta name="theme-color" content="#ffffff">
    
    <!-- Fuentes y Estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="../css/estilo.css">
    
</head>
<body class="body-repartidor">

    <!-- CABECERA ESTILO APP -->
    <nav class="navbar sticky-top bg-white shadow-sm">
        <div class="container-fluid px-3 py-1">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2 m-0" href="#">
                <span class="fs-4">🚚</span> <span class="fs-5">Ruta Activa</span>
            </a>
            <a href="../controladores/logout.php" class="btn btn-light rounded-pill text-danger fw-bold btn-sm px-4 shadow-sm border border-danger-subtle">Salir</a>
        </div>
    </nav>

    <main class="container mt-4">
        
        <!-- SALUDO Y CONTADOR -->
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Hola, <?= htmlspecialchars($nombreRepartidor) ?></h5>
                <small class="text-muted fw-medium">Tu jornada logística</small>
            </div>
            <div class="text-end">
                <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 rounded-pill shadow-sm border border-primary-subtle" id="contador-entregas">
                    0 / <span id="total-entregas"><?= count($paradas) ?></span> 📦
                </span>
            </div>
        </div>

        <!-- BOTONES DE NAVEGACIÓN UNIFICADA -->
        <div class="d-grid gap-2 mb-4">
            <button id="btn-optimizar-ruta" class="btn btn-dark btn-lg rounded-pill shadow-sm fw-bold py-3 d-flex justify-content-center align-items-center gap-2">
                🗺️ Calcular Ruta Óptima
            </button>
            <button id="btn-siguiente-parada" class="btn btn-primary btn-lg rounded-pill shadow-sm fw-bold py-3 d-flex justify-content-center align-items-center gap-2 d-none">
                🚗 Iniciar Navegación
            </button>
        </div>
        
        <!-- MAPA (Con bordes redondeados y sombra) -->
        <div id="mapa-repartidor" class="rounded-4 shadow-sm border-0 mb-4" style="height: 400px; width: 100%;"></div>

        <!-- LISTADO DE PARADAS -->
        <div id="lista-paradas">
            <?php if (empty($paradas)): ?>
                <div class="card border-0 shadow-sm rounded-4 text-center py-5 mt-4">
                    <div class="card-body">
                        <div class="mb-3">🎉</div>
                        <h4 class="fw-bold text-dark">¡Jornada finalizada!</h4>
                        <p class="text-muted mb-0">No tienes más entregas pendientes en tu ruta actual. Buen trabajo.</p>
                    </div>
                </div>
            <?php else: ?>
                
                <?php foreach ($paradas as $index => $parada): ?>
                    <div class="card parada-card border-0 shadow-sm rounded-4 mb-4" id="pedido-<?= $parada['id'] ?>">
                        <div class="card-body p-4">
                            
                            <!-- Cabecera de la Tarjeta -->
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark">Pedido #<?= $parada['id'] ?></h5>
                                    <span class="badge bg-warning bg-opacity-25 text-warning-emphasis fw-bold rounded-pill px-3">En Ruta</span>
                                </div>
                                <button class="btn btn-primary rounded-pill fw-bold shadow-sm btn-simular-ruta px-3 py-2" 
                                        data-lato="<?= $parada['lat_origen'] ?>" 
                                        data-lono="<?= $parada['lon_origen'] ?>" 
                                        data-latd="<?= $parada['lat_destino'] ?>" 
                                        data-lond="<?= $parada['lon_destino'] ?>">
                                    🚗 Simular
                                </button>
                            </div>
                            
                            <!-- Datos del Cliente -->
                            <div class="d-flex align-items-center gap-3 mb-4 bg-light p-3 rounded-4 border-0">
                                <div class="bg-white rounded-circle d-flex justify-content-center align-items-center shadow-sm">👤</div>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($parada['cliente']) ?></div>
                                    <a href="tel:<?= htmlspecialchars($parada['telefono']) ?>" class="text-decoration-none text-primary fw-bold d-flex align-items-center gap-1 mt-1">
                                        📞 <?= htmlspecialchars($parada['telefono']) ?>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Timeline de la Ruta -->
                            <div class="timeline-reparto mb-4">
                                <div class="mb-3 position-relative">
                                    <div class="timeline-punto bg-white border border-primary border-3 timeline-dot"></div>
                                    <small class="text-muted fw-bold d-block text-uppercase timeline-label">Recogida (Origen)</small>
                                    <span class="fw-medium text-dark"><?= htmlspecialchars($parada['origen']) ?></span>
                                </div>
                                <div class="position-relative">
                                    <div class="timeline-punto bg-primary shadow-sm" ></div>
                                    <small class="text-muted fw-bold d-block text-uppercase">Entrega (Destino)</small>
                                    <span class="fw-medium text-dark"><?= htmlspecialchars($parada['destino']) ?></span>
                                </div>
                            </div>
                            
                            <!-- Botones de Acción de Estado -->
                            <div class="row g-2 mt-2">
                                <div class="col-6">
                                    <button class="btn btn-success w-100 rounded-pill fw-bold shadow-sm btn-entregado py-3 d-flex align-items-center justify-content-center gap-2" data-pedido="<?= $parada['id'] ?>">
                                        ✔️ Entregado
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-danger w-100 rounded-pill fw-bold py-3 btn-incidencia d-flex align-items-center justify-content-center gap-2" data-pedido="<?= $parada['id'] ?>">
                                        ❌ Incidencia
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
                
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script src="../js/logica.js?v=<?= time(); ?>"></script>
</body>
</html>