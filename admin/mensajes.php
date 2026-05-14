<?php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

$con = AccesoBD::getInstance();
if (!isset($_SESSION['codigo']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/login.php");
    exit;
}

$mensajes = $con->obtenerMensajes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bandeja de Contacto - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        <div class="mb-4">
            <h3 class="fw-bold text-dark">📩 Consultas de Clientes</h3>
            <p class="text-muted">Bandeja de entrada del formulario de atención al cliente.</p>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 border-0">Fecha</th>
                            <th class="py-3 border-0">Remitente / Email</th>
                            <th class="py-3 border-0">Asunto</th>
                            <th class="pe-4 py-3 border-0">Mensaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($mensajes)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="fs-1 mb-2 opacity-50">📭</div>
                                    <p class="mb-0">No hay mensajes nuevos.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mensajes as $m) { ?>
                                <tr class="border-bottom border-secondary border-opacity-10">
                                    <td class="ps-4 text-secondary small fw-medium">
                                        <?= date('d/m/Y', strtotime($m['fecha'])) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($m['nombre']) ?></div>
                                        <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="text-decoration-none text-primary small">
                                            <?= htmlspecialchars($m['email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3 py-2 fw-medium">
                                            <?= htmlspecialchars($m['asunto']) ?>
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <p class="text-muted small mb-0 lh-sm" style="max-width: 400px;">
                                            <?= nl2br(htmlspecialchars($m['mensaje'])) ?>
                                        </p>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>