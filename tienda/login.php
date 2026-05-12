<?php
// tienda/login.php
session_start();

// Detectamos el "origen" y la "url" por la variable GET
$origen = $_GET['origen'] ?? 'general';
$urlDestino = $_GET['url'] ?? '';

// Variables dinámicas para adaptar la interfaz
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

    <main class="container my-5">
        <?php
            // Alertas (Timeout de la sesión, errores de login, etc.)
            if (isset($_GET['timeout'])) {
                echo "<div class='alert alert-warning text-center mx-auto' style='max-width: 500px;'>⏱️ Tu sesión ha expirado por inactividad.</div>";
            }
            if (isset($_SESSION['mensaje'])) {
                echo "<div class='alert alert-danger text-center mx-auto' style='max-width: 500px;'>" . htmlspecialchars($_SESSION['mensaje']) . "</div>";
                unset($_SESSION['mensaje']);
            }
        ?>

        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-header <?= $colorCabecera ?> text-white text-center py-3">
                        <h4 class="mb-0"><?= $tituloPanel ?></h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="../controladores/login.php">
                            <!-- Input oculto para que el login.php sepa a dónde enviarnos después -->
                            <input type="hidden" name="url" value="<?= htmlspecialchars($urlDestino) ?>">
                            <input type="hidden" name="tipoAcceso" value="Acceso">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-secondary">Email / Usuario</label>
                                <input name="usuario" type="email" class="form-control form-control-lg" required placeholder="tu@email.com"/>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary">Contraseña</label>
                                <input name="clave" type="password" class="form-control form-control-lg" required placeholder="••••••••"/>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-<?= $origen === 'carrito' ? 'primary' : 'dark' ?> btn-lg shadow-sm">
                                    <?= $btnTexto ?>
                                </button>
                                
                                <hr class="my-4">
                                
                                <p class="text-center text-muted small"><?= $textoRegistro ?></p>
                                <a href="registroUsuario.php?url=<?= urlencode($urlDestino) ?>" class="btn btn-outline-<?= $origen === 'carrito' ? 'primary' : 'dark' ?>">
                                    <?= $btnRegistro ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
</body>
</html>