<%@page language="java" contentType="text/html; charset=UTF-8" import="java.util.*, tienda.*" pageEncoding="UTF-8"%>
<%
    // Recuperamos los datos de la sesión
    ArrayList<ProductoCarrito> listaCarrito = (ArrayList<ProductoCarrito>) session.getAttribute("carritoJSON");
    Float total = (Float) session.getAttribute("totalPedido");
    
    if (listaCarrito == null || listaCarrito.isEmpty()) {
        response.sendRedirect("carrito.jsp");
        return;
    }

    ArrayList<TarjetaBD> misTarjetas = (ArrayList<TarjetaBD>) request.getAttribute("misTarjetas");
%>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pasarela de Pago - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="bg-light">

    <mi-cabecera></mi-cabecera>
    <mi-menu data-user="<%= session.getAttribute("nombreUsuario") %>"></mi-menu>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                
                <%-- Mensajes de Error --%>
                <% String mensaje = (String) session.getAttribute("mensaje");
                   if (mensaje != null) { session.removeAttribute("mensaje"); %>
                    <div class="alert alert-danger text-center shadow-sm mb-4"><%= mensaje %></div>
                <% } %>

                <div class="card shadow border-0">
                    <%-- Estética de checkout.html: Cabecera Verde --%>
                    <div class="card-header bg-success text-white text-center py-3">
                        <h4 class="mb-0">💳 Formalizar Reserva Logística</h4>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        
                        <%-- Resumen del importe (Estilo checkout.html) --%>
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                            <strong>Total a pagar:</strong>
                            <span class="fs-3 fw-bold"><%= String.format(Locale.US, "%.2f", total) %> €</span>
                        </div>

                        <form action="finalizarPedido.php" method="POST" id="formPago">
                            
                            <h5 class="mb-3">Servicios Contratados</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-borderless">
                                    <% for (ProductoCarrito p : listaCarrito) { %>
                                        <tr>
                                            <td><%= p.getDescripcion() %> <span class="text-muted">x<%= p.getCantidad() %></span></td>
                                            <td class="text-end"><%= String.format(Locale.US, "%.2f", p.getPrecio() * p.getCantidad()) %>€</td>
                                        </tr>
                                    <% } %>
                                </table>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Método de Pago</h5>
                            
                            <%-- Selector de Tarjetas Guardadas --%>
                            <% if (misTarjetas != null && !misTarjetas.isEmpty()) { %>
                                <div class="mb-4 p-3 border rounded bg-light border-success">
                                    <label class="form-label fw-bold text-success">Usar una de mis tarjetas:</label>
                                    <select class="form-select border-success" name="tarjetaGuardada" id="tarjetaGuardada">
                                        <option value="NUEVA">➕ Añadir nueva tarjeta...</option>
                                        <% for (TarjetaBD t : misTarjetas) { %>
                                            <option value="<%= t.getId() %>" 
                                                    data-numero="<%= t.getNumero() %>" 
                                                    data-titular="<%= t.getTitular() %>" 
                                                    data-caducidad="<%= t.getCaducidad() %>">
                                                <%= t.getNumeroOculto() %> (<%= t.getTitular() %>)
                                            </option>
                                        <% } %>
                                    </select>
                                </div>
                            <% } %>

                            <div id="seccionNuevaTarjeta">
                                <div class="row g-3">
                                    <div class="col-12 mb-2">
                                        <label class="form-label">Titular de la tarjeta</label>
                                        <input type="text" class="form-control" name="titularTarjeta" id="titularTarjeta" placeholder="Nombre completo">
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label class="form-label">Número de tarjeta</label>
                                        <input type="text" class="form-control" name="numeroTarjeta" id="numeroTarjeta" placeholder="0000 0000 0000 0000" maxlength="19">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Fecha de Caducidad</label>
                                        <input type="text" class="form-control" name="caducidadTarjeta" id="caducidadTarjeta" placeholder="MM/AAAA" maxlength="7">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" placeholder="123" maxlength="3">
                                    </div>
                                </div>

                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="guardarTarjetaCheck" value="SI" id="guardarCheck">
                                    <label class="form-check-label text-success" for="guardarCheck">
                                        <strong>Guardar tarjeta para futuros pedidos</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-5">
                                <a href="datosEnvio.jsp" class="text-muted text-decoration-none">← Volver a ruta</a>
                                <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                    Pagar y Confirmar Pedido 🔒
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
                
                <p class="text-center text-muted mt-4 small">
                    Pago seguro procesado por LogisTFG. Tus datos están cifrados.
                </p>
            </div>
        </div>
    </main>

    <mi-pie></mi-pie>

    <script src="js/mis-etiquetas.js"></script>
    <script src="js/logica.js?v=<%= System.currentTimeMillis() %>"></script>
</body>
</html>