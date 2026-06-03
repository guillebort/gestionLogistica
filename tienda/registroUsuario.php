<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                
                <div class="text-center mb-5">
                    <div class="display-6 mb-2">👋</div>
                    <h2 class="fw-bold text-dark">Crear cuenta de cliente</h2>
                    <p class="text-muted">Rellena tus datos para empezar a enviar con LogisTFG</p>
                </div>

                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <form method="post" action="../controladores/registro.php">
                        <?php 
                            $urlDest = $_GET["url"] ?? null; 
                            if($urlDest != null) { 
                        ?>
                            <input type="hidden" name="url" value="<?php echo htmlspecialchars($urlDest); ?>">
                        <?php } ?>

                            <!-- SECCIÓN 1: Credenciales -->
                            <h5 class="text-primary fw-bold mb-3"><span class="badge bg-primary rounded-circle me-2">1</span>Credenciales de Acceso</h5>
                            <div class="row g-3 mb-4 bg-light p-3 rounded-3 border-0">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input name="usuario" type="email" class="form-control" id="regEmail" placeholder="Email" required>
                                        <label for="regEmail">Correo Electrónico (Será tu usuario)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="pass1" name="clave" type="password" class="form-control" placeholder="Contraseña" required>
                                        <label for="pass1">Contraseña</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input id="pass2" name="clave2" type="password" class="form-control" placeholder="Repetir Contraseña" required>
                                        <label for="pass2">Repetir Contraseña</label>
                                    </div>
                                </div>
                                <div id="errorPass" class="col-12 text-danger small fw-medium mt-1">⚠️ Las contraseñas no coinciden.</div>
                            </div>

                            <!-- SECCIÓN 2: Datos Personales -->
                            <h5 class="text-primary fw-bold mb-3 mt-4"><span class="badge bg-primary rounded-circle me-2">2</span>Datos Personales</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input name="nombre" type="text" class="form-control" id="regNombre" placeholder="Nombre" required>
                                        <label for="regNombre">Nombre o Empresa</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input name="apellidos" type="text" class="form-control" id="regApe" placeholder="Apellidos" required>
                                        <label for="regApe">Apellidos</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input name="telefono" type="tel" class="form-control" id="regTel" maxlength="9" placeholder="Teléfono" required>
                                        <label for="regTel">Teléfono Móvil</label>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 3: Dirección -->
                            <h5 class="text-primary fw-bold mb-3 mt-4"><span class="badge bg-primary rounded-circle me-2">3</span>Dirección de Facturación</h5>
                            <div class="mb-3 position-relative">
                                <label class="form-label text-muted small fw-medium">Dirección Completa / Domicilio</label>
                                <input type="text" name="domicilio" id="input_direccion" class="form-control bg-light border-0 rounded-3" autocomplete="off" required>
                                
                                <ul id="lista_sugerencias" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></ul>
                            </div>

                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label class="form-label text-muted small fw-medium">Población</label>
                                    <input type="text" name="poblacion" id="input_poblacion" class="form-control bg-light border-0 rounded-3" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-muted small fw-medium">Provincia</label>
                                    <input type="text" name="provincia" id="input_provincia" class="form-control bg-light border-0 rounded-3" required>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-muted small fw-medium">C. Postal</label>
                                    <input type="text" name="cp" id="input_cp" class="form-control bg-light border-0 rounded-3" maxlength="5" required>
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                                <a href="login.php" class="text-muted text-decoration-none fw-medium">← Volver al login</a>
                                <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow-sm fw-bold">Registrar mi cuenta ✨</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
    <script src="../js/logica.js"></script>
    
</body>
</html>