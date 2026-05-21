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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Admin - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="[https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css](https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css)"/>
    <link rel="stylesheet" href="../css/estilo.css">
    <style>
        .split-bg-admin {
            background: linear-gradient(135deg, rgba(15,23,42,0.95) 0%, rgba(30,41,59,0.85) 100%), url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop') center/cover;
        }
    </style>
</head>
<body class="bg-light d-flex flex-column vh-100" style="font-family: 'Inter', sans-serif;">

    <main class="container-fluid flex-grow-1 px-0">
        <div class="row g-0 h-100">
            <!-- Mitad Izquierda (Imagen/Branding corporativo) -->
            <div class="col-md-5 col-lg-6 d-none d-md-flex flex-column justify-content-center align-items-center text-white split-bg-admin p-5">
                <div class="text-center" style="max-width: 450px;">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 shadow" style="width: 80px; height: 80px; font-size: 2.5rem;">⚙️</div>
                    <h2 class="fw-bold mb-3">Centro de Control Logístico</h2>
                    <p class="lead opacity-75 fs-6">Acceso restringido al personal autorizado. Monitoriza envíos, asigna rutas y gestiona la operativa de LogisTFG.</p>
                </div>
            </div>

            <!-- Mitad Derecha (Formulario Admin) -->
            <div class="col-md-7 col-lg-6 d-flex align-items-center justify-content-center p-4 p-sm-5 bg-light">
                <div class="w-100" style="max-width: 400px;">
                    
                    <!-- Botón volver a la tienda -->
                    <a href="../tienda/index.php" class="text-muted text-decoration-none small fw-medium mb-5 d-inline-block">← Volver a la web pública</a>

                    <div class="mb-5">
                        <h3 class="fw-bold text-dark">Acceso al Sistema</h3>
                        <p class="text-muted">Introduce tus credenciales de administrador</p>
                    </div>

                    <?php if (isset($_SESSION['error_admin'])) { ?>
                        <div class="alert alert-danger text-center rounded-4 shadow-sm mb-4 border-0 fw-medium text-danger-emphasis bg-danger-subtle">
                            <?= $_SESSION['error_admin']; unset($_SESSION['error_admin']); ?>
                        </div>
                    <?php } ?>

                    <form action="../controladores/loginAdminController.php" method="POST" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                        
                        <!-- Floating Labels -->
                        <div class="form-floating mb-4">
                            <input name="usuario" type="email" class="form-control rounded-3 bg-light border-0" id="inputAdminEmail" placeholder="admin@logistfg.es" required>
                            <label for="inputAdminEmail" class="text-muted">Usuario Administrador</label>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input name="clave" type="password" class="form-control rounded-3 bg-light border-0" id="inputAdminClave" placeholder="Contraseña" required>
                            <label for="inputAdminClave" class="text-muted">Contraseña</label>
                        </div>
                        
                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm fw-bold">
                                Acceder al Panel 🔒
                            </button>
                        </div>
                    </form>
                    
                    <p class="text-center text-muted small mt-5 opacity-50">© <?= date('Y') ?> LogisTFG. Acceso Auditado.</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>