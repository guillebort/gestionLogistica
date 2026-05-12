<?php
// tienda/login.php
session_start();

// Detectamos el "origen" y la "url" por la variable GET
$origen = $_GET['origen'] ?? 'general';
$urlDestino = $_GET['url'] ?? '';

// Variables dinámicas para adaptar la interfaz (Mantenemos tu lógica intacta)
if ($origen === 'carrito') {
    $tituloPanel = "Identificación para el Pedido";
    $btnTexto = "Continuar con la reserva";
    $textoRegistro = "¿No tienes cuenta de empresa?";
    $btnRegistro = "Registrarse y pagar";
    $colorCabecera = "bg-primary";
    if (empty($urlDestino)) $urlDestino = "datosEnvio.php";
} else {
    $tituloPanel = "Acceso de Clientes";
    $btnTexto = "Entrar al Panel";
    $textoRegistro = "¿Eres nuevo en LogisTFG?";
    $btnRegistro = "Crear mi cuenta";
    $colorCabecera = "bg-dark";
    if (empty($urlDestino)) $urlDestino = "usuario.php";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPanel ?> - LogisTFG</title>
    <link rel="icon" type="image/ico" href="../img/icono.ico" sizes="64x64">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <?php include '../includes/menu.php'; ?>

    <main class="container d-flex align-items-center justify-content-center my-5" style="min-height: 70vh;">
        <div class="w-100" style="max-width: 500px;">
            
            <?php
                // Alertas intactas
                if (isset($_GET['timeout'])) {
                    echo "<div class='alert alert-warning text-center rounded-4 shadow-sm mb-4'>⏱️ Tu sesión ha expirado por inactividad.</div>";
                }
                if (isset($_SESSION['mensaje'])) {
                    echo "<div class='alert alert-danger text-center rounded-4 shadow-sm mb-4'>" . htmlspecialchars($_SESSION['mensaje']) . "</div>";
                    unset($_SESSION['mensaje']);
                }
            ?>

            <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                <div class="card-header <?= $colorCabecera ?> text-white text-center py-4 border-0">
                    <h4 class="mb-0 fw-bold"><?= $tituloPanel ?></h4>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form method="post" action="../controladores/login.php">
                        <!-- Inputs ocultos originales -->
                        <input type="hidden" name="url" value="<?= htmlspecialchars($urlDestino) ?>">
                        <input type="hidden" name="tipoAcceso" value="Acceso">
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary ms-1">Email / Usuario</label>
                            <input name="usuario" type="email" class="form-control form-control-lg rounded-pill px-4 shadow-sm" required placeholder="tu@email.com"/>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary ms-1">Contraseña</label>
                            <input name="clave" type="password" class="form-control form-control-lg rounded-pill px-4 shadow-sm" required placeholder="••••••••"/>
                        </div>
                        
                        <div class="d-grid gap-3 mt-5">
                            <button type="submit" class="btn btn-<?= $origen === 'carrito' ? 'primary' : 'dark' ?> btn-lg rounded-pill shadow">
                                <?= $btnTexto ?>
                            </button>
                            
                            <hr class="my-3 opacity-10">
                            
                            <p class="text-center text-muted small mb-1"><?= $textoRegistro ?></p>
                            <a href="registroUsuario.php?url=<?= urlencode($urlDestino) ?>" class="btn btn-outline-<?= $origen === 'carrito' ? 'primary' : 'dark' ?> rounded-pill">
                                <?= $btnRegistro ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
</body>
</html>