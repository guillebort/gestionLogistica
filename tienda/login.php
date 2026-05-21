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
    $bgLateral = "bg-primary";
    if (empty($urlDestino)) $urlDestino = "datosEnvio.php";
} else {
    $tituloPanel = "Acceso de Clientes";
    $btnTexto = "Entrar al Panel";
    $textoRegistro = "¿Eres nuevo en LogisTFG?";
    $btnRegistro = "Crear mi cuenta";
    $bgLateral = "bg-dark";
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
    <link rel="stylesheet" href="[https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css](https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css)"/>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light d-flex flex-column vh-100">

    <?php include '../includes/menu.php'; ?>

    <main class="container-fluid flex-grow-1 px-0">
        <div class="row g-0 h-100">
            <!-- Mitad Izquierda (Imagen/Branding) - Oculta en móviles -->
            <div class="col-md-5 col-lg-6 d-none d-md-flex flex-column justify-content-center align-items-center text-white split-bg p-5">
                <div class="text-center">
                    <div class="display-3 mb-3">🚚</div>
                    <h2 class="fw-bold mb-3">Tu socio logístico inteligente</h2>
                    <p class="lead opacity-75">Accede a tu panel para gestionar tus envíos, visualizar rutas en tiempo real y optimizar la cadena de suministro de tu negocio.</p>
                </div>
            </div>

            <!-- Mitad Derecha (Formulario) -->
            <div class="col-md-7 col-lg-6 d-flex align-items-center justify-content-center p-4 p-sm-5 bg-light">
                <div class="w-100">
                    
                    <?php
                        if (isset($_GET['timeout'])) {
                            echo "<div class='alert alert-warning text-center rounded-4 shadow-sm mb-4 border-0'>⏱️ Tu sesión ha expirado por inactividad.</div>";
                        }
                        if (isset($_SESSION['mensaje'])) {
                            echo "<div class='alert alert-danger text-center rounded-4 shadow-sm mb-4 border-0'>" . htmlspecialchars($_SESSION['mensaje']) . "</div>";
                            unset($_SESSION['mensaje']);
                        }
                    ?>

                    <div class="text-center mb-5">
                        <h3 class="fw-bold text-dark"><?= $tituloPanel ?></h3>
                        <p class="text-muted">Introduce tus credenciales para continuar</p>
                    </div>

                    <form method="post" action="../controladores/login.php" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                        <input type="hidden" name="url" value="<?= htmlspecialchars($urlDestino) ?>">
                        <input type="hidden" name="tipoAcceso" value="Acceso">
                        
                        <!-- Floating Labels para aspecto moderno -->
                        <div class="form-floating mb-4">
                            <input name="usuario" type="email" class="form-control rounded-3" id="inputEmail" placeholder="tu@email.com" required>
                            <label for="inputEmail" class="text-muted">Email / Usuario</label>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input name="clave" type="password" class="form-control rounded-3" id="inputClave" placeholder="Contraseña" required>
                            <label for="inputClave" class="text-muted">Contraseña</label>
                        </div>
                        
                        <div class="d-grid gap-3 mt-2">
                            <button type="submit" class="btn <?= $origen === 'carrito' ? 'btn-primary' : 'btn-dark' ?> btn-lg rounded-pill shadow-sm fw-bold">
                                <?= $btnTexto ?>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-5">
                        <p class="text-muted small mb-2"><?= $textoRegistro ?></p>
                        <a href="registroUsuario.php?url=<?= urlencode($urlDestino) ?>" class="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-medium">
                            <?= $btnRegistro ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>