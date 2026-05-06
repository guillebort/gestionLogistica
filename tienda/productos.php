<%@page language="java" contentType="text/html; charset=UTF-8" import="java.util.*, tienda.*" pageEncoding="UTF-8"%>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios y Tarifas - LogisTFG</title>
    <link rel="icon" type="image/ico" href="img/icono.ico" sizes="64x64">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="bg-light">

    <mi-cabecera></mi-cabecera>
    <mi-menu data-user="${nombreUsuario}"></mi-menu>

    <main class="container my-5">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 text-primary">Nuestras Tarifas de Envío</h2>
                <p class="lead text-secondary">Añade al carrito el servicio que necesites.</p>
            </div>
        </div>

        <div class="row">
            <% 
                List<ProductoBD> lista = (List<ProductoBD>) request.getAttribute("listaProductos"); 
                if (lista != null && !lista.isEmpty()) {
                    for (ProductoBD p : lista) {
                        String claseBoton = "btn-outline-primary"; 
                        String sufijo = "/ paquete";
                        String claseBorde = "shadow-sm"; 
                        
                        if ("primary".equals(p.getColorCss())) {
                            claseBoton = "btn-primary";
                            claseBorde = "shadow border-primary";
                        } else if ("dark".equals(p.getColorCss())) {
                            claseBoton = "btn-outline-dark";
                            sufijo = "/ bulto";
                        }
            %>
            <div class="col-md-4 mb-4">
                <div class="card h-100 <%= claseBorde %>">
                    <div class="card-header bg-<%= p.getColorCss() %> text-white text-center">
                        <h4 class="my-0 font-weight-normal"><%= p.getDescripcion() %></h4>
                    </div>
                    <div class="card-body d-flex flex-column text-center">
                        <h1 class="card-title pricing-card-title"><%= String.format(Locale.US, "%.2f", p.getPrecio()) %>€ <small class="text-muted"><%= sufijo %></small></h1>
                        
                        <ul class="list-unstyled mt-3 mb-4 text-start">
                            <%= p.getCaracteristicas() %>
                        </ul>
                        
                        <button type="button" class="btn btn-lg btn-block <%= claseBoton %> mt-auto" 
                            onclick="anadirCarrito(<%= p.getId() %>, '<%= p.getDescripcion().replace("'", "") %>', <%= p.getPrecio() %>, <%= p.getExistencias() %>)">
                            Añadir al carrito 🛒
                        </button>
                    </div>
                </div>
            </div>
            <% 
                    } 
                } else { 
            %>
            <div class="col-12 text-center">
                <div class="alert alert-warning">No hay tarifas disponibles.</div>
            </div>
            <% } %>
        </div>
    </main>

    <mi-pie></mi-pie>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/mis-etiquetas.js"></script>
    <script src="js/logica.js"></script>
    <script src="js/carrito.js?v=999"></script>
</body>
</html>