<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cuenta - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <mi-cabecera></mi-cabecera>
    <mi-menu></mi-menu>

    <main class="container my-5">
        <?php
            // Miramos si hay un código de usuario en la sesión
            $codigoLogueado = $_SESSION["codigo"] ?? 0;
            $mensaje = $_SESSION["mensaje"] ?? null;
            if ($mensaje != null) {
                unset($_SESSION["mensaje"]);
        ?>
            <div class="alert alert-info text-center"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php } ?>

        <?php if ($codigoLogueado <= 0) { ?>
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white text-center">
                            <h4>Identificación de Usuario</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="../controladores/login.php">
                                <input type="hidden" name="url" value="usuario.php">
                                <input type="hidden" name="tipoAcceso" value="Acceso">
                                
                                <div class="mb-3">
                                    <label class="form-label">Email / Usuario:</label>
                                    <input name="usuario" type="email" class="form-control" required/>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña:</label>
                                    <input name="clave" type="password" class="form-control" required/>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Entrar</button>
                                    <hr>
                                    <p class="text-center">¿Aún no tienes cuenta?</p>
                                    <a href="registroUsuario.php" class="btn btn-outline-secondary">Crear cuenta nueva</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php } else { ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm border-success">
                        <div class="card-body text-center py-5">
                            <h2 class="text-success">¡Bienvenido de nuevo!</h2>
                            <p class="lead">Has accedido correctamente a tu panel de gestión logística.</p>
                            <div class="mt-4">
                                <a href="productos.php" class="btn btn-primary btn-lg">Ver Tarifas y Enviar</a>
                                <a href="#" onclick="limpiarCarritoLocal(event)" class="btn btn-outline-danger px-4">Cerrar Sesión</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </main>

    <mi-pie></mi-pie>
    <script src="../js/mis-etiquetas.js"></script>
</body>
</html>