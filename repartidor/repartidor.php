<?php
session_start();
require_once '../modelos/AccesoBD.php';

// Asumimos que Rol 0 = Cliente, Rol 1 = Admin, Rol 2 = Repartidor
$rolUsuario = $_SESSION['rol'] ?? 0; 
$idRepartidor = $_SESSION['codigo'] ?? 0;

// Seguridad: Si no está logueado o su rol no es 2 (Repartidor), lo echamos
if ($idRepartidor <= 0 || $rolUsuario != 2) {
    header("Location: ../tienda/loginUsuario.php");
    exit;
}

$con = AccesoBD::getInstance();
$nombreRepartidor = $_SESSION['nombreUsuario'] ?? 'Repartidor';

// Obtenemos los pedidos asignados a este repartidor
$paradas = $con->obtenerRutasRepartidor($idRepartidor);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Viewport crucial para que parezca una App en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>App Reparto - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
    <style>
        /* Estilos específicos para que parezca una app móvil */
        body { background-color: #f0f2f5; padding-bottom: 70px; }
        .app-header { background-color: #0d6efd; color: white; padding: 15px; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .parada-card { border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 15px; }
        .btn-app { border-radius: 8px; font-weight: bold; padding: 12px; }
    </style>
</head>
<body>
    <!-- Cabecera estilo App -->
    <div class="app-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">🚚 Mis Rutas</h5>
        <a href="../controladores/logout.php" class="btn btn-sm btn-light text-primary fw-bold">Salir</a>
    </div>

    <main class="container mt-3">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <span class="text-muted">Hola, <strong><?= htmlspecialchars($nombreRepartidor) ?></strong></span>
            <span class="badge bg-primary fs-6" id="contador-entregas">0 / <span id="total-entregas"><?= count($paradas) ?></span> Entregas</span>
        </div>

        <div id="lista-paradas">
            <?php if (empty($paradas)): ?>
                <div class="alert alert-success text-center mt-5" style="border-radius: 15px;">
                    <h1 style="font-size: 4rem;">🎉</h1>
                    <h4>¡Buen trabajo!</h4>
                    <p>No tienes entregas pendientes en tu ruta actual.</p>
                </div>
            <?php else: ?>
                <?php foreach ($paradas as $index => $parada): ?>
                    <div class="card parada-card" id="pedido-<?= $parada['id'] ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0 fw-bold">Pedido #<?= $parada['id'] ?></h5>
                                <span class="badge bg-warning text-dark">En Ruta</span>
                            </div>
                            
                            <p class="mb-1"><strong>👤 Cliente:</strong> <?= htmlspecialchars($parada['cliente']) ?></p>
                            <p class="mb-1"><strong>📞 Tel:</strong> <a href="tel:<?= htmlspecialchars($parada['telefono']) ?>"><?= htmlspecialchars($parada['telefono']) ?></a></p>
                            <p class="mb-3 text-secondary"><strong>📍 Destino:</strong> <?= htmlspecialchars($parada['destino']) ?></p>
                            
                            <div class="d-flex gap-2">
                                <!-- Enlace directo a Google Maps con el destino -->
                                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($parada['destino']) ?>" target="_blank" class="btn btn-primary btn-app flex-grow-1">🗺️ Navegar</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/logica.js"></script>
</body>
</html>