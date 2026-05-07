<?php
session_start();
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

$con = AccesoBD::getInstance();
if (!isset($_SESSION['codigo']) || $_SESSION['rol'] != 1) {
    header("Location: ../tienda/loginUsuario.php");
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
</head>
<body class="bg-light">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        <h3 class="mb-4 text-primary">📩 Consultas de Clientes</h3>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Remitente</th>
                            <th>Email</th>
                            <th>Asunto</th>
                            <th>Mensaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mensajes as $m) { ?>
                            <tr>
                                <td><?= $m['fecha'] ?></td>
                                <td><strong><?= htmlspecialchars($m['nombre']) ?></strong></td>
                                <td><a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($m['asunto']) ?></span></td>
                                <td><small><?= nl2br(htmlspecialchars($m['mensaje'])) ?></small></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>