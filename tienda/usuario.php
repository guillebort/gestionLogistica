<%@page language="java" contentType="text/html; charset=UTF-8" import="tienda.*, java.util.*" pageEncoding="UTF-8"%>
<%
    // REDIRECCIÓN MAESTRA: Si no está logueado, lo mandamos al login nuevo.
    Integer codigoLogueado = (Integer) session.getAttribute("codigo");
    if (codigoLogueado == null || codigoLogueado <= 0) {
        response.sendRedirect("login.html");
        return; // Cortamos la ejecución de esta página
    }
%>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área de Usuario - LogisTFG</title>
    <link rel="icon" type="image/ico" href="img/icono.ico" sizes="64x64">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="bg-light">
    
    <mi-cabecera></mi-cabecera>
    
    <% 
        String nombreMenu = (String) session.getAttribute("nombreUsuario"); 
        String nombreData = (nombreMenu != null) ? nombreMenu : "";
    %>
    <mi-menu data-user="<%= nombreData %>"></mi-menu>

    <main class="container my-5">
        <%
            try {
                String mensaje = (String) session.getAttribute("mensaje");
                if (mensaje != null) {
                    session.removeAttribute("mensaje");
        %>
            <div class="alert alert-info text-center"><%= mensaje %></div>
        <%      } 

                // Como ya sabemos que está logueado, cargamos sus datos directamente
                AccesoBD con = AccesoBD.getInstance();
                UsuarioBD u = con.obtenerUsuarioBD(codigoLogueado);
                
                if (u == null) {
                    out.println("<div class='alert alert-danger text-center'>Error: Perfil no encontrado. <a href='logout.html'>Cerrar sesión</a></div>");
                } else {
                    // AQUÍ LA MAGIA: Llamamos a la función que cruza las 4 tablas
                    ArrayList<PedidoBD> historial = con.obtenerHistorialDetallado(codigoLogueado);
        %>
            
            <div class="row justify-content-center mb-5">
                <div class="col-md-8">
                    <div class="card shadow-sm border-success">
                        <div class="card-body text-center py-5">
                            <h2 class="text-success mb-4">¡Bienvenido, <%= u.getNombre() != null ? u.getNombre() : "Cliente" %>!</h2>
                            
                            <form action="modificarUsuario.php" method="POST" onsubmit="return validarModificacion()" class="text-start">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Email (No modificable)</label>
                                        <input type="text" class="form-control bg-light" value="<%= u.getUsuario() %>" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" name="telefono" value="<%= u.getTelefono() != null ? u.getTelefono() : "" %>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" class="form-control" name="nombre" value="<%= u.getNombre() != null ? u.getNombre() : "" %>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Apellidos</label>
                                        <input type="text" class="form-control" name="apellidos" value="<%= u.getApellidos() != null ? u.getApellidos() : "" %>">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Dirección Principal</label>
                                        <input type="text" class="form-control" name="domicilio" value="<%= u.getDomicilio() != null ? u.getDomicilio() : "" %>">
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Población</label>
                                        <input type="text" class="form-control" name="poblacion" value="<%= u.getPoblacion() != null ? u.getPoblacion() : "" %>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Provincia</label>
                                        <input type="text" class="form-control" name="provincia" value="<%= u.getProvincia() != null ? u.getProvincia() : "" %>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">C.P.</label>
                                        <input type="text" class="form-control" name="cp" value="<%= u.getCp() != null ? u.getCp() : "" %>">
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h5 class="text-secondary mb-3">Seguridad y Contraseña</h5>
                                <p class="text-muted small"><em>* Déjalo en blanco si no quieres cambiar tu contraseña actual.</em></p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="mod_pass1" name="clave1" placeholder="Mínimo 4 caracteres">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Repetir Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="mod_pass2" name="clave2" placeholder="Confirma la contraseña">
                                    </div>
                                </div>
                                <div id="errorModPass" class="alert alert-danger d-none py-2 text-center">
                                    ⚠️ Las contraseñas no coinciden.
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                                    <button type="submit" class="btn btn-success px-4">Guardar Cambios</button>
                                    <a href="productos.jsp" class="btn btn-primary px-4">Ir a Tarifas</a>
                                    <a href="logout.html" class="btn btn-outline-danger px-4">Cerrar Sesión</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container my-5">
                <h3 class="mb-4 text-primary">📦 Historial de Pedidos</h3>

                <%
                    if (historial == null || historial.isEmpty()) {
                %>
                    <div class="alert alert-info">Aún no has realizado ningún pedido.</div>
                <%  } else { %>
                    
                    <div class="accordion shadow-sm" id="acordeonPedidos">
                        <% for (PedidoBD ped : historial) { %>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<%= ped.getId() %>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<%= ped.getId() %>">
                                    <div class="d-flex justify-content-between w-100 pe-3">
                                        <span><strong>Pedido #<%= ped.getId() %></strong> (<%= ped.getFecha() %>)</span>
                                        <span class="badge bg-secondary"><%= ped.getEstado() %></span>
                                        <span class="text-success fw-bold"><%= String.format(Locale.US, "%.2f", ped.getImporteTotal()) %>€</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<%= ped.getId() %>" class="accordion-collapse collapse" data-bs-parent="#acordeonPedidos">
                                <div class="accordion-body bg-light">
                                    <table class="table table-sm table-bordered bg-white mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Unidades</th>
                                                <th class="text-end">Precio Unid.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <% for (DetallePedidoBD linea : ped.getDetalles()) { %>
                                            <tr>
                                                <td><%= linea.getProducto().getDescripcion() %></td>
                                                <td class="text-center"><%= linea.getCantidad() %></td>
                                                <td class="text-end"><%= String.format(Locale.US, "%.2f", linea.getPrecio()) %>€</td>
                                            </tr>
                                            <% } %>
                                        </tbody>
                                    </table>
                                    <%-- INICIO BOTÓN CANCELAR --%>
                                    <% if (ped.getEstado().equalsIgnoreCase("Pendiente")) { %>
                                        <div class="text-end mt-3">
                                            <a href="cancelarPedido.html?id=<%= ped.getId() %>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('¿Estás seguro de que quieres cancelar este pedido?');">
                                               Cancelar Pedido
                                            </a>
                                        </div>
                                    <% } %>
                                    <%-- FIN BOTÓN CANCELAR --%>
                                </div>
                            </div>
                        </div>
                        
                        <% } %>
                    </div>
                <%  } %>
            </div>

        <%      } 
            } catch (Exception e) {
                out.println("<div class='alert alert-danger mt-5 p-4'><h4>⚠️ Error detectado</h4><p>" + e.getMessage() + "</p></div>");
            }
        %>
    </main>

    <mi-pie></mi-pie>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/mis-etiquetas.js"></script>
    <script src="./js/logica.js"></script>
</body>
</html>