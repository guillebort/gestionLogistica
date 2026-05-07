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
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white text-center">
                        <h4>Registro de Nuevo Cliente</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../controladores/registro.php" onsubmit="return verificarPasswords()">
                        <?php 
                            // Recogemos la ruta por si viene de comprar en la tienda
                            $urlDest = $_GET["url"] ?? null; 
                            if($urlDest != null) { 
                        ?>
                            <input type="hidden" name="url" value="<?php echo htmlspecialchars($urlDest); ?>">
                        <?php } ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Nombre:</label>
                                    <input name="nombre" type="text" class="form-control" required/>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Apellidos:</label>
                                    <input name="apellidos" type="text" class="form-control" required/>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label>Dirección (Domicilio):</label>
                                <input name="domicilio" type="text" class="form-control" required/>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Población:</label>
                                    <input name="poblacion" type="text" class="form-control" required/>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Provincia:</label>
                                    <input name="provincia" type="text" class="form-control" required/>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Código Postal:</label>
                                    <input name="cp" type="text" class="form-control" maxlength="5" required/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Teléfono:</label>
                                    <input name="telefono" type="text" class="form-control" maxlength="9" required/>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Email (Será tu usuario):</label>
                                    <input name="usuario" type="email" class="form-control" required/>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Contraseña:</label>
                                    <input id="pass1" name="clave" type="password" class="form-control" required/>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Repetir Contraseña:</label>
                                    <input id="pass2" name="clave2" type="password" class="form-control" required/>
                                </div>
                            </div>
                            <div id="errorPass" class="text-danger mb-3" style="display: none;">Las contraseñas no coinciden.</div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg">Crear Cuenta</button>
                                <a href="loginUsuario.php" class="btn btn-outline-secondary">Cancelar y volver</a>
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