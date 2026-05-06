<?php
session_start();
$nombreMenu = $_SESSION["nombreUsuario"] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - LogisTFG</title>
    <link rel="icon" type="image/ico" href="../img/icono.ico" sizes="64x64">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light">

    <mi-cabecera></mi-cabecera>
    <mi-menu data-user="<?php echo ($nombreMenu != null) ? htmlspecialchars($nombreMenu) : ""; ?>"></mi-menu>

    <main class="container my-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="display-5 text-primary">Atención al Cliente</h2>
                <p class="lead text-secondary">¿Tienes dudas sobre una ruta o un pedido? Escríbenos o ven a visitarnos a nuestra central.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Envíanos un mensaje</h4>
                        <?php
                            $msjContacto = $_SESSION["mensajeContacto"] ?? null;
                            if ($msjContacto != null) {
                                unset($_SESSION["mensajeContacto"]);
                        ?>
                            <div class="alert alert-info text-center"><?php echo htmlspecialchars($msjContacto); ?></div>
                        <?php } ?>
                        <form action="../controladores/contactoController.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre_contacto" class="form-label">Nombre o Empresa</label>
                                <input type="text" class="form-control" id="nombre_contacto" name="nombre" required placeholder="Tu nombre">
                            </div>
                            <div class="mb-3">
                                <label for="email_contacto" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email_contacto" name="email" required placeholder="correo@ejemplo.com">
                            </div>
                            <div class="mb-3">
                                <label for="asunto_contacto" class="form-label">Asunto</label>
                                <select class="form-select" id="asunto_contacto" name="asunto" required>
                                    <option value="" disabled selected>Selecciona un motivo...</option>
                                    <option value="presupuesto">Solicitar Presupuesto</option>
                                    <option value="incidencia">Incidencia con un envío</option>
                                    <option value="duda">Duda general</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="mensaje_contacto" class="form-label">Mensaje</label>
                                <textarea class="form-control" id="mensaje_contacto" name="mensaje" rows="4" required placeholder="Escribe aquí los detalles..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Enviar Mensaje ✉️</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Nuestra Central Logística</h4>
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2"><strong>📍 Dirección:</strong> Av. de la Universidad, s/n, 46100 Burjassot, Valencia</li>
                            <li class="mb-2"><strong>📞 Teléfono:</strong> <a href="tel:+34900123456" class="text-decoration-none">900 123 456</a></li>
                            <li class="mb-2"><strong>✉️ Email:</strong> <a href="mailto:info@logistfg.es" class="text-decoration-none">info@logistfg.es</a></li>
                            <li class="mb-2"><strong>🕒 Horario:</strong> Lunes a Viernes de 08:00 a 20:00 h.</li>
                        </ul>

                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3078.653426749712!2d-0.4268682846313837!3d39.50529557948145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd604f434a179375%3A0xaad6585bd02c918!2sEscola%20T%C3%A8cnica%20Superior%20d'Enginyeria%20(ETSE-UV)!5e0!3m2!1ses!2ses!4v1680000000000!5m2!1ses!2ses" class="mapa-contacto" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
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