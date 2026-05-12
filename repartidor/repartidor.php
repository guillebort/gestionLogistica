
<?php
session_start();
require_once '../includes/controlSesion.php';
require_once '../modelos/AccesoBD.php';

// 1. Validación de seguridad y rol
$rolUsuario = $_SESSION['rol'] ?? 0; 
$idRepartidor = $_SESSION['codigo'] ?? 0;

// Si no está logueado o no es repartidor (rol 2), lo echamos a la pantalla de login
if ($idRepartidor <= 0 || $rolUsuario != 2) {
    header("Location: ../tienda/loginUsuario.php");
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
    <!-- Mantenemos el manifest para la PWA -->
    <link rel="manifest" href="../repartidor/manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="../css/estilo.css">
    <style>
        body { background-color: #f0f2f5; padding-bottom: 70px; }
        .app-header { background-color: #0d6efd; color: white; padding: 15px; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .parada-card { border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 15px; }
        .btn-app { border-radius: 8px; font-weight: bold; padding: 12px; }
        #mapa-repartidor { height: 350px; width: 100%; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; z-index: 1;}
        .leaflet-routing-container { display: none !important; }
    </style>
</head>
<body>
    <div class="app-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">🚚 Mis Rutas</h5>
        <a href="../controladores/logout.php" class="btn btn-sm btn-light text-primary fw-bold">Salir</a>
    </div>

    <main class="container mt-3">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <span class="text-muted">Hola, <strong><?= htmlspecialchars($nombreRepartidor) ?></strong></span>
            <span class="badge bg-primary fs-6" id="contador-entregas">0 / <span id="total-entregas"><?= count($paradas) ?></span> Entregas</span>
        </div>

        <div class="d-grid mb-3">
            <button id="btn-optimizar-ruta" class="btn btn-dark btn-lg shadow-sm">
                🗺️ Calcular Ruta Óptima (Todos los paquetes)
            </button>
        </div>
        
        <!-- CONTENEDOR DEL MAPA ESTÁTICO -->
        <div id="mapa-repartidor"></div>

        <div id="lista-paradas">
            <?php if (empty($paradas)): ?>
                <div class="alert alert-success text-center mt-5" style="border-radius: 15px;">
                    <h1 style="font-size: 4rem;">🎉</h1>
                    <h4>¡Buen trabajo!</h4>
                    <p>No tienes entregas pendientes en tu ruta actual.</p>
                </div>
            <?php else: ?>
                <!-- Iteramos sobre la variable inyectada desde el controlador -->
                <?php foreach ($paradas as $index => $parada): ?>
                    <div class="card parada-card" id="pedido-<?= $parada['id'] ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0 fw-bold">Pedido #<?= $parada['id'] ?></h5>
                                <span class="badge bg-warning text-dark">En Ruta</span>
                            </div>
                            
                            <p class="mb-1"><strong>👤 Cliente:</strong> <?= htmlspecialchars($parada['cliente']) ?></p>
                            <p class="mb-1"><strong>📞 Tel:</strong> <a href="tel:<?= htmlspecialchars($parada['telefono']) ?>"><?= htmlspecialchars($parada['telefono']) ?></a></p>
                            <p class="mb-1 text-secondary"><strong>🟢 Origen:</strong> <?= htmlspecialchars($parada['origen']) ?></p>
                            <p class="mb-3 text-secondary"><strong>📍 Destino:</strong> <?= htmlspecialchars($parada['destino']) ?></p>
                            
                            <div class="d-flex gap-2">
                                <!-- Datos inyectados en HTML5 data-attributes para ser leídos por JS -->
                                <button class="btn btn-primary btn-app flex-grow-1 btn-simular-ruta" 
                                        data-lato="<?= $parada['lat_origen'] ?>" 
                                        data-lono="<?= $parada['lon_origen'] ?>" 
                                        data-latd="<?= $parada['lat_destino'] ?>" 
                                        data-lond="<?= $parada['lon_destino'] ?>">
                                    🚗 Simular Ruta
                                </button>
                            </div>
                            
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-success btn-app flex-grow-1 btn-entregado" data-pedido="<?= $parada['id'] ?>">✔️ Entregado</button>
                                <button class="btn btn-outline-danger btn-app flex-grow-1 btn-incidencia" data-pedido="<?= $parada['id'] ?>">❌ Incidencia</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="../js/logica.js"></script>
</body>
</html>