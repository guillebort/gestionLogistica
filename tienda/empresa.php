<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestra Empresa - LogisTFG</title>
    <link rel="icon" type="image/ico" href="img/icono.ico" sizes="64x64">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="bg-light">

    <mi-cabecera></mi-cabecera>
    
<mi-menu data-user="<%= (nombreMenu != null) ? nombreMenu : "" %>"></mi-menu>

    <main class="container my-5">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 text-primary">Sobre LogisTFG</h2>
                <p class="lead text-secondary">Más de 10 años conectando negocios locales con sus clientes.</p>
            </div>
        </div>

        <div class="row mb-5 align-items-center">
            <div class="col-md-6">
                <h3>Nuestra Historia y Misión</h3>
                <p>Nacimos en 2016 como un pequeño proyecto universitario con un objetivo claro: ofrecer a las PYMES una solución logística a la altura de las grandes multinacionales, pero con un trato cercano y tarifas adaptadas.</p>
                <p>Nuestra misión es optimizar las rutas de reparto urbanas e interurbanas, reduciendo la huella de carbono y garantizando entregas rápidas y seguras.</p>
            </div>
            <div class="col-md-6">
                <div class="p-5 bg-secondary text-white text-center rounded shadow">
                    <i>[Imagen de almacén logístico o flota de furgonetas]</i>
                </div>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-white">
                    <div class="card-body">
                        <h4 class="card-title text-success">Control de Calidad</h4>
                        <p class="card-text">Nuestra flota cuenta con seguimiento GPS en tiempo real. Auditamos el 100% de las rutas para asegurar que se cumplen los tiempos estimados.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-white">
                    <div class="card-body">
                        <h4 class="card-title text-success">Organización</h4>
                        <p class="card-text">Disponemos de una red de 5 nodos logísticos estratégicos. Nuestro equipo se divide en gestión de tráfico, atención al cliente y operaciones de almacén.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 bg-white">
                    <div class="card-body">
                        <h4 class="card-title text-success">Sostenibilidad</h4>
                        <p class="card-text">El 40% de nuestros vehículos de última milla son 100% eléctricos, ayudando a crear ciudades con un aire más limpio.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <mi-pie></mi-pie>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/mis-etiquetas.js"></script>
    <script src="./js/logica.js"></script>
</body>
</html>