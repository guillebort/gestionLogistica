<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Ruta - LogisTFG</title>
    <link rel="icon" type="image/ico" href="img/icono.ico" sizes="64x64">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="bg-light">

    <header class="bg-dark text-white text-center py-3 sticky-top shadow-sm">
        <h5 class="mb-0">🚚 LogisTFG - Modo Repartidor</h5>
    </header>

    <main class="container my-3">
        <div class="alert alert-primary d-flex justify-content-between align-items-center mb-3">
            <div>
                <strong>Ruta Activa:</strong> Zona Norte<br>
                <small>Vehículo: Furgoneta 03 (Matrícula: 1234-LGS)</small>
            </div>
            <span class="badge bg-primary rounded-pill fs-6" id="contador-entregas">0 / 3 Entregados</span>
        </div>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-0">
                <div class="ratio ratio-4x3">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m28!1m12!1m3!1d49265.59016147614!2d-0.42848602506821425!3d39.49216065586616!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m13!3e0!4m5!1s0xd604f56f345ff03%3A0xc3f60fbd841bcfa2!2sETSE-UV%2C%20Avinguda%20de%20la%20Universitat%2C%20Burjassot!3m2!1d39.5126848!2d-0.4243616!4m5!1s0xd6048ab504d60e5%3A0x629161bc844c207a!2sPlaza%20del%20Ayuntamiento%2C%20Valencia!3m2!1d39.4697926!2d-0.37648179999999996!5e0!3m2!1ses!2ses!4v1700000000000" class="mapa-reparto" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
            <div class="card-footer bg-white text-center">
                <button class="btn btn-outline-primary btn-sm w-100">🗺️ Abrir en Google Maps / GPS</button>
            </div>
        </div>

        <h5 class="mb-3 text-secondary">Paradas Pendientes</h5>
        
        <div class="list-group mb-5" id="lista-paradas">
            
            <div class="list-group-item list-group-item-action flex-column align-items-start parada-item" id="parada-1">
                <div class="d-flex w-100 justify-content-between mb-2">
                    <h5 class="mb-1 fw-bold">1. Frutería Paco S.L.</h5>
                    <span class="badge bg-warning text-dark align-self-start">10:30 h</span>
                </div>
                <p class="mb-1 text-muted">📍 Plaza Mayor 2, Valencia</p>
                <p class="mb-2 small">📦 2x Envío Estándar (Referencia: #00102)</p>
                <div class="d-flex gap-2">
                    <button class="btn btn-success flex-grow-1 btn-entregado" data-parada="parada-1">✅ Entregado</button>
                    <button class="btn btn-outline-danger flex-grow-1 btn-incidencia" data-parada="parada-1">⚠️ Incidencia</button>
                </div>
            </div>

            <div class="list-group-item list-group-item-action flex-column align-items-start parada-item" id="parada-2">
                <div class="d-flex w-100 justify-content-between mb-2">
                    <h5 class="mb-1 fw-bold">2. Librería Central</h5>
                    <span class="badge bg-warning text-dark align-self-start">11:15 h</span>
                </div>
                <p class="mb-1 text-muted">📍 Calle Colón 45, Valencia</p>
                <p class="mb-2 small">📦 1x Carga Especial (Referencia: #00105)</p>
                <div class="d-flex gap-2">
                    <button class="btn btn-success flex-grow-1 btn-entregado" data-parada="parada-2">✅ Entregado</button>
                    <button class="btn btn-outline-danger flex-grow-1 btn-incidencia" data-parada="parada-2">⚠️ Incidencia</button>
                </div>
            </div>

        </div>

        <div class="d-grid gap-2 mb-4">
            <button class="btn btn-dark btn-lg">Finalizar Jornada / Ruta</button>
            <a href="index.html" class="btn btn-link text-muted">Salir de la vista de repartidor</a>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/logica.js"></script>
</body>
</html>