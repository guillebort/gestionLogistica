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

    <mi-cabecera></mi-cabecera>
    <mi-menu data-user="<?php echo htmlspecialchars($nombreUsuario); ?>"></mi-menu>

    <main class="container my-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="display-6 text-primary">Resumen de Contratación</h2>
                <p class="text-muted">Revisa tus servicios antes de finalizar el pedido.</p>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-4">
                <div id="carrito-vacio" class="text-center py-5 d-none">
                    <div class="mb-3" style="font-size: 3rem;">🛒</div>
                    <h4>Tu cesta está vacía</h4>
                    <a href="productos.php" class="btn btn-primary mt-3">Ver servicios disponibles</a>
                </div>

                <div id="tabla-contenedor" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Servicio Logístico</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpo-tabla">
                                </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">TOTAL ESTIMADO:</td>
                                    <td class="text-end fw-bold text-primary fs-5" id="total-pedido">0.00€</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-outline-secondary" onclick="vaciarCarrito()">Vaciar Cesta</button>
                        <button class="btn btn-success btn-lg px-5" onclick="EnviarCarrito('../controladores/procesarPedido_action.php', carrito)">
                            Formalizar Pedido ➔
                        </button>
                    </div> 
                </div>
            </div>
        </div>
    </main>

    <mi-pie></mi-pie>
    <script src="../js/mis-etiquetas.js"></script>
    <script src="../js/carrito.js?v=<?php echo time(); ?>"></script>
    <script src="../js/libjson.js"></script>
</body>
</html>