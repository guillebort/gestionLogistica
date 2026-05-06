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
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">Identificación para el Pedido</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="../controladores/login.php">
                            <?php 
                                $urlDestino = $_GET["url"] ?? null;
                                if ($urlDestino == null || trim($urlDestino) === '') {
                                    $urlDestino = "productos.php";
                                }
                            ?>
                            <input type="hidden" name="url" value="<?php echo htmlspecialchars($urlDestino); ?>">
                            <input type="hidden" name="tipoAcceso" value="Acceso">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Usuario / Email</label>
                                <input name="usuario" type="email" class="form-control" required/>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Contraseña</label>
                                <input name="clave" type="password" class="form-control" required/>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm">Continuar con la reserva</button>
                                
                                <hr class="my-4">
                                
                                <p class="text-center text-muted small">¿No tienes cuenta de empresa?</p>
                                <a href="registroUsuario.php?url=<?php echo urlencode($urlDestino); ?>" class="btn btn-outline-primary">Registrarse y pagar</a>
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