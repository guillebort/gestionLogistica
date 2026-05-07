<?php
session_start();
// Si ya hay sesión iniciada como admin, redirigir al panel directamente
if (isset($_SESSION['codigo']) && isset($_SESSION['rol']) && $_SESSION['rol'] == 1) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Admin - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">⚙️ Panel de Control</h4>
                    </div>
                    <div class="card-body p-4 text-dark bg-light">
                        <?php if (isset($_SESSION['error_admin'])) { ?>
                            <div class="alert alert-danger text-center"><?= $_SESSION['error_admin']; unset($_SESSION['error_admin']); ?></div>
                        <?php } ?>
                        <form action="../controladores/loginAdminController.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Usuario Administrador</label>
                                <input type="email" name="usuario" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Contraseña</label>
                                <input type="password" name="clave" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg">Acceder al Sistema</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>