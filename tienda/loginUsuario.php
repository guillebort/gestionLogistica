<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso y Registro - LogisTFG</title>
    <link rel="icon" type="image/ico" href="../img/icono.ico" sizes="64x64">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <mi-cabecera></mi-cabecera>
    <mi-menu></mi-menu>

    <main class="container my-5">
        <?php
            $mensaje = $_SESSION["mensaje"] ?? null;
            if ($mensaje != null) {
                unset($_SESSION["mensaje"]);
        ?>
            <div class="alert alert-danger text-center mx-auto" style="max-width: 500px;"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php } ?>

        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-header bg-dark text-white text-center py-3">
                        <h4 class="mb-0">Acceso de Clientes</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="../controladores/login.php">
                            <input type="hidden" name="url" value="usuario.php">
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
                                <button type="submit" class="btn btn-dark btn-lg shadow-sm">Entrar al Panel</button>
                                
                                <hr class="my-4">
                                
                                <p class="text-center text-muted small">¿Eres nuevo en LogisTFG?</p>
                                <a href="registroUsuario.php" class="btn btn-outline-dark">Crear mi cuenta</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <mi-pie></mi-pie>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/mis-etiquetas.js"></script>
</body>
</html>