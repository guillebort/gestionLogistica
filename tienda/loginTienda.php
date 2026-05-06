<%@page language="java" contentType="text/html; charset=UTF-8" import="tienda.*" pageEncoding="UTF-8"%>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso y Registro - LogisTFG</title>
    <link rel="icon" type="image/ico" href="img/icono.ico" sizes="64x64">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
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
                        <form method="post" action="login.php">
                            <% 
                                String urlDestino = request.getParameter("url");
                                if (urlDestino == null || urlDestino.trim().isEmpty()) urlDestino = "productos.html";
                            %>
                            <input type="hidden" name="url" value="<%= urlDestino %>">
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
                                <a href="registroUsuario.jsp?url=<%= urlDestino %>" class="btn btn-outline-primary">Registrarse y pagar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <mi-pie></mi-pie>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/mis-etiquetas.js"></script>
    <script>
        function cambiarModo() {
            var esRegistro = document.getElementById('radioRegistro').checked;
            var divCampos = document.getElementById('campos_registro');
            var btnSubmit = document.getElementById('btn_submit');
            
            if (esRegistro) {
                divCampos.classList.remove('d-none');
                btnSubmit.textContent = 'Crear Cuenta y Pagar';
            } else {
                divCampos.classList.add('d-none');
                btnSubmit.textContent = 'Continuar con el Pago';
            }
        }
    </script>
</body>
</html>