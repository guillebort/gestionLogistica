<?php
session_start();
$nombreUsuario = $_SESSION['nombreUsuario'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cesta - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light" onload="renderizarCarrito()">

    <?php include '../includes/menu.php'; ?>

    <main class="container my-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="display-6 fw-bold text-dark mb-2">Resumen de Contratación</h2>
                <p class="text-muted">Revisa tus servicios logísticos antes de configurar la ruta.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Estado Carrito Vacío -->
                        <div id="carrito-vacio" class="text-center py-5 d-none">
                            <div class="mb-3 opacity-50" >📦</div>
                            <h4 class="fw-bold text-dark">Tu cesta logística está vacía</h4>
                            <p class="text-muted mb-4">Aún no has seleccionado ningún servicio de transporte.</p>
                            <a href="productos.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">Ver tarifas de envío</a>
                        </div>

                        <!-- Estado Carrito con Productos -->
                        <div id="tabla-contenedor" class="d-none">
                            <div class="table-responsive mb-4">
                                <table class="table align-middle text-nowrap">
                                    <thead class="bg-white text-muted">
                                        <tr>
                                            <th class="fw-bold bg-transparent text-secondary border-0">Servicio Logístico</th>
                                            <th class="text-center fw-bold bg-transparent text-secondary border-0">Cantidad</th>
                                            <th class="text-end fw-bold bg-transparent text-secondary border-0">Precio</th>
                                            <th class="text-end fw-bold bg-transparent text-secondary border-0">Subtotal</th>
                                            <th class="text-center bg-transparent border-0"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cuerpo-tabla" >
                                        <!-- Renderizado por JS -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold text-secondary pt-4 border-0">TOTAL ESTIMADO:</td>
                                            <td class="text-end fw-bold text-success fs-4 pt-4 border-0" id="total-pedido">0.00€</td>
                                            <td class="border-0"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top">
                                <button class="btn btn-link text-danger text-decoration-none mb-3 mb-md-0 fw-bold" onclick="vaciarCarrito()">
                                    <small>🗑️ Vaciar Cesta</small>
                                </button>
                                <button class="btn btn-success btn-lg px-5 shadow rounded-pill fw-bold" onclick="EnviarCarrito('../controladores/procesarPedido.php', carrito)">
                                    Configurar Ruta ➔
                                </button>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/pie.php'; ?>
    
    <script src="../js/carrito.js?v=<?php echo time(); ?>"></script>
    <script src="../js/libjson.js"></script>
</body>
</html>