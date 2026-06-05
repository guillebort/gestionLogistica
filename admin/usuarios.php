<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin: Usuarios - LogisTFG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif;">
    
    <?php include '../includes/menuAdmin.php'; ?>

    <main class="container my-5">
        
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-0">👥 Gestión del Personal y Clientes</h3>
                <p class="text-muted small mt-1 mb-0">Administra los accesos y roles de toda la plataforma.</p>
            </div>
            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fs-6"><?= count($usuarios) ?> Registros</span>
        </div>

        <?php if (isset($_SESSION['mensajeAdmin'])) { 
            $esError = strpos($_SESSION['mensajeAdmin'], '❌') !== false;
            $claseAlerta = $esError ? 'alert-danger text-danger-emphasis bg-danger-subtle' : 'alert-success text-success-emphasis bg-success-subtle';
        ?>
            <div class="alert <?= $claseAlerta ?> text-center rounded-4 shadow-sm border-0 mb-4 fw-medium">
                <?= $_SESSION['mensajeAdmin']; unset($_SESSION['mensajeAdmin']); ?>
            </div>
        <?php } ?>
        
       <div class="mb-4">
            <button class="btn btn-primary fw-bold px-4 shadow-sm rounded-pill d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#formularioNuevoUsuario" aria-expanded="false">
                ➕ Añadir Nuevo Usuario
            </button>
        </div>

        <div class="collapse mb-5" id="formularioNuevoUsuario">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="../controladoresAdmin/usuariosController.php" method="POST" class="row g-4" id="formNuevoUsuario">
                        <input type="hidden" name="accion" value="crear_personal">
                        
                        <div class="col-12 mb-0">
                            <h6 class="fw-bold text-secondary border-bottom pb-2">Datos de la Cuenta</h6>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Nombre</label>
                            <input type="text" name="nuevo_nombre" class="form-control " required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Apellidos</label>
                            <input type="text" name="nuevo_apellidos" class="form-control " required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Teléfono</label>
                            <input type="tel" name="nuevo_telefono" class="form-control " maxlength="9" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Correo Electrónico</label>
                            <input type="email" name="nuevo_email" class="form-control " required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Contraseña</label>
                            <input type="password" name="nueva_clave" class="form-control " required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Rol</label>
                            <select name="nuevo_rol" class="form-select " required>
                                <option value="" selected disabled>Selecciona un rol...</option>
                                <option value="1">Administrador</option>
                                <option value="2">Repartidor</option>
                            </select>
                        </div>

                        <div class="col-12 mt-4 mb-0">
                            <h6 class="fw-bold text-secondary border-bottom pb-2">📍 Ubicación</h6>
                        </div>
                        
                        <!-- INCORPORACIÓN DE IDS PARA EL AUTOCOMPLETADO DE NOMINATIM -->
                        <div class="mb-3 position-relative">
                            <label class="form-label small fw-bold text-muted">Dirección Completa</label>
                            <input type="text" name="nuevo_domicilio" id="input_direccion_admin" class="form-control " autocomplete="off" required>
                            
                            <!-- Lista oculta donde se renderizarán los resultados AJAX -->
                            <ul id="lista_sugerencias_admin" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></ul>
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label small fw-bold text-muted">Población</label>
                                <input type="text" name="nuevo_poblacion" id="input_poblacion" class="form-control " required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-muted">Provincia</label>
                                <input type="text" name="nuevo_provincia" id="input_provincia" class="form-control " required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold text-muted">C. Postal</label>
                                <input type="text" name="nuevo_cp" id="input_cp" class="form-control " maxlength="5" required>
                            </div>
                        </div>
                        
                        <div class="col-12 text-end mt-4">
                            <button type="button" class="btn btn-light px-4 me-2 rounded-pill" data-bs-toggle="collapse" data-bs-target="#formularioNuevoUsuario">Cancelar</button>
                            <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">💾 Guardar Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 overflow-hidden rounded-4">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3 border-0">ID</th>
                            <th class="py-3 border-0">Email / Usuario</th>
                            <th class="py-3 border-0">Nombre Completo</th>
                            <th class="py-3 border-0 text-center">Estado</th>
                            <th class="py-3 border-0">Rol del Sistema</th>
                            <th class="pe-4 py-3 border-0 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usu) { 
                            $esAdminLogueado = ($usu['id'] == $codigoLogueado);
                        ?>
                            <tr class="border-bottom border-secondary border-opacity-10 <?= $esAdminLogueado ? 'bg-primary bg-opacity-10' : '' ?>">
                                <td class="ps-4 fw-bold text-dark">#<?= $usu['id'] ?></td>
                                <td>
                                    <div class="text-primary fw-medium"><?= htmlspecialchars($usu['usuario']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center text-secondary" style="width: 32px; height: 32px;">👤</div>
                                        <?= htmlspecialchars($usu['nombre'] . ' ' . $usu['apellidos']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if($usu['activo']): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success-emphasis border border-success-subtle rounded-pill px-3 py-2">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger-emphasis border border-danger-subtle rounded-pill px-3 py-2">Inactivo</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <form action="../controladoresAdmin/usuariosController.php" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="accion" value="cambiar_rol">
                                        <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                        <select name="nuevo_rol" class="form-select form-select-sm rounded-pill shadow-none bg-light border-0 px-3 fw-medium">
                                            <option value="0" <?= $usu['rol'] == 0 ? 'selected' : '' ?>>Cliente</option>
                                            <option value="2" <?= $usu['rol'] == 2 ? 'selected' : '' ?>>Repartidor</option>
                                            <option value="1" <?= $usu['rol'] == 1 ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold shadow-sm">Aplicar</button>
                                    </form>
                                </td>

                                <td class="pe-4 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <form action="../controladoresAdmin/usuariosController.php" method="POST">
                                            <input type="hidden" name="accion" value="toggle_activo">
                                            <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                            <input type="hidden" name="estado_actual" value="<?= $usu['activo'] ?>">
                                            <?php if($usu['activo']): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" <?= $esAdminLogueado ? 'disabled' : '' ?>>
                                                    Suspender
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm" <?= $esAdminLogueado ? 'disabled' : '' ?>>
                                                    Reactivar
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <form action="../controladoresAdmin/usuariosController.php" method="POST" class="form-eliminar-usuario">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_usuario" value="<?= $usu['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" <?= $esAdminLogueado ? 'disabled' : '' ?>>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Es importante cargar logica.js primero para disponer de la función activarAutocompletadoUnico() -->
    <script src="../js/logica.js"></script>
    <script src="../js/logicaAdmin.js"></script>
</body>
</html>