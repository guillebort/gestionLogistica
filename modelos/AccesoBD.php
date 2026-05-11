<?php
// --- Archivo: AccesoBD.php ---
require_once 'Modelos.php';
require_once __DIR__ . '/../includes/config.php';

class AccesoBD {
    private static $instanciaUnica = null;
    private $conexionBD = null;

    public static function getInstance() {
        if (self::$instanciaUnica == null) {
            self::$instanciaUnica = new AccesoBD();
        }
        return self::$instanciaUnica;
    }

    private function __construct() {
        $this->abrirConexionBD();
    }

    public function abrirConexionBD() {
        if ($this->conexionBD == null) {
            // Usamos las constantes definidas en config.php
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            try {
                $this->conexionBD = new PDO($dsn, DB_USER, DB_PASS);
                // Configurar PDO para que lance excepciones ante cualquier error SQL
                $this->conexionBD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("No se ha podido conectar a la base de datos: " . $e->getMessage());
            }
        }
    }

    public function obtenerProductosBD() {
        $productos = [];
        try {
            $stmt = $this->conexionBD->query("SELECT id, descripcion, precio, existencias, imagen, caracteristicas, color_css FROM productos");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $p = new ProductoBD();
                $p->setId($row['id']);
                $p->setDescripcion($row['descripcion']);
                $p->setPrecio($row['precio']);
                $p->setExistencias($row['existencias']);
                $p->setImagen($row['imagen']);
                $p->setCaracteristicas($row['caracteristicas']);
                $p->setColorCss($row['color_css']);
                $productos[] = $p;
            }
        } catch (Exception $e) {}
        return $productos;
    }

    public function comprobarUsuarioBD($usuario, $clave) {
        try {
            // Primero buscamos al usuario por su email/nombre
            $sql = "SELECT id, clave FROM usuarios WHERE usuario=?";
            $stmt = $this->conexionBD->prepare($sql);
            $stmt->execute([$usuario]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Verificamos si la contraseña plana coincide con el hash de la BD
                if (password_verify($clave, $row['clave'])) {
                    return $row['id'];
                }
            }
        } catch (Exception $e) {}
        return -1; // Fallo de autenticación
    }

    public function registrarUsuarioBD($usuario, $clave, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono) {
        try {
            $hashClave = password_hash($clave, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (usuario, clave, nombre, apellidos, domicilio, poblacion, provincia, cp, telefono, activo, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)";
            $stmt = $this->conexionBD->prepare($sql);
            return $stmt->execute([$usuario, $hashClave, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerUsuarioBD($id) {
        try {
            $stmt = $this->conexionBD->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $u = new UsuarioBD();
                $u->setId($row['id']);
                $u->setUsuario($row['usuario']);
                $u->setNombre($row['nombre']);
                $u->setApellidos($row['apellidos']);
                $u->setDomicilio($row['domicilio']);
                $u->setPoblacion($row['poblacion']);
                $u->setProvincia($row['provincia']);
                $u->setCp($row['cp']);
                $u->setTelefono($row['telefono']);
                $u->setRol($row['rol']);
                return $u;
            }
        } catch (Exception $e) {}
        return null;
    }

    public function modificarUsuarioBD($id, $clave, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono) {
        try {
            if (empty(trim($clave))) {
                // EL USUARIO NO QUIERE CAMBIAR LA CONTRASEÑA
                $sql = "UPDATE usuarios SET nombre=?, apellidos=?, domicilio=?, poblacion=?, provincia=?, cp=?, telefono=? WHERE id=?";
                $stmt = $this->conexionBD->prepare($sql);
                return $stmt->execute([$nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono, $id]);
            } else {
                // EL USUARIO SÍ QUIERE CAMBIAR LA CONTRASEÑA -> La encriptamos con Bcrypt
                $hashClave = password_hash($clave, PASSWORD_DEFAULT);
                
                $sql = "UPDATE usuarios SET clave=?, nombre=?, apellidos=?, domicilio=?, poblacion=?, provincia=?, cp=?, telefono=? WHERE id=?";
                $stmt = $this->conexionBD->prepare($sql);
                // Pasamos $hashClave en lugar de la clave plana o el SHA1
                return $stmt->execute([$hashClave, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono, $id]);
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function obtenerExistencias($idProducto) {
        try {
            $stmt = $this->conexionBD->prepare("SELECT existencias FROM productos WHERE id = ?");
            $stmt->execute([$idProducto]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['existencias'];
            }
        } catch (Exception $e) {}
        return 0;
    }

    public function guardarPedido($idUsuario, $importeTotal, $carrito, $textoOrigen, $latOrigen, $lonOrigen, $textoDestino, $latDestino, $lonDestino) {
        try {
            $this->conexionBD->beginTransaction();

            $sqlDir = "INSERT INTO direcciones (calle_texto, latitud, longitud) VALUES (?, ?, ?)";
            $psDir = $this->conexionBD->prepare($sqlDir);
            
            $psDir->execute([$textoOrigen, $latOrigen, $lonOrigen]);
            $idDirOrigen = $this->conexionBD->lastInsertId();

            $psDir->execute([$textoDestino, $latDestino, $lonDestino]);
            $idDirDestino = $this->conexionBD->lastInsertId();

            $sqlPedido = "INSERT INTO pedidos (persona, fecha, importe, estado, id_direccion_origen, id_direccion_destino) VALUES (?, CURDATE(), ?, 1, ?, ?)";
            $psPedido = $this->conexionBD->prepare($sqlPedido);
            $psPedido->execute([$idUsuario, $importeTotal, $idDirOrigen, $idDirDestino]);
            $idPedidoNuevo = $this->conexionBD->lastInsertId();

            $sqlDetalle = "INSERT INTO detalle (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
            $psDetalle = $this->conexionBD->prepare($sqlDetalle);

            $sqlRestarStock = "UPDATE productos SET existencias = existencias - ? WHERE id = ?";
            $psStock = $this->conexionBD->prepare($sqlRestarStock);

            foreach ($carrito as $prod) {
                $psDetalle->execute([$idPedidoNuevo, $prod->getCodigo(), $prod->getCantidad(), $prod->getPrecio()]);
                $psStock->execute([$prod->getCantidad(), $prod->getCodigo()]);
            }

            $this->conexionBD->commit();
            return $idPedidoNuevo;

        } catch (Exception $e) {
            if ($this->conexionBD->inTransaction()) {
                $this->conexionBD->rollBack();
            }
            return -1;
        }
    }

    public function obtenerHistorialDetallado($idUsuario) {
        $lista = [];
        try {
            $sqlPedidos = "SELECT p.id, p.fecha, p.importe, e.descripcion as nombre_estado 
                           FROM pedidos p JOIN estados e ON p.estado = e.id 
                           WHERE p.persona = ? ORDER BY p.fecha DESC";
            $ps = $this->conexionBD->prepare($sqlPedidos);
            $ps->execute([$idUsuario]);
            
            $sqlDetalle = "SELECT pr.descripcion, d.cantidad, d.precio_unitario 
                           FROM detalle d JOIN productos pr ON d.id_producto = pr.id 
                           WHERE d.id_pedido = ?";
            $psDet = $this->conexionBD->prepare($sqlDetalle);

            while ($row = $ps->fetch(PDO::FETCH_ASSOC)) {
                $ped = new PedidoBD();
                $ped->setId($row['id']);
                $ped->setFecha($row['fecha']); // Si lo quieres formatear luego
                $ped->setImporteTotal($row['importe']);
                $ped->setEstado($row['nombre_estado']);

                $psDet->execute([$ped->getId()]);
                $detalles = [];
                while ($rowDet = $psDet->fetch(PDO::FETCH_ASSOC)) {
                    $linea = new DetallePedidoBD();
                    $linea->setCantidad($rowDet['cantidad']);
                    $linea->setPrecio($rowDet['precio_unitario']);
                    
                    $prod = new ProductoBD();
                    $prod->setDescripcion($rowDet['descripcion']);
                    $linea->setProducto($prod);

                    $detalles[] = $linea;
                }
                $ped->setDetalles($detalles);
                $lista[] = $ped;
            }
        } catch (Exception $e) {
            throw new Exception("ERROR EN BASE DE DATOS: " . $e->getMessage());
        }
        return $lista;
    }

    public function cancelarPedido($idPedido, $idUsuario) {
        try {
            $this->conexionBD->beginTransaction();

            $sqlCancelar = "UPDATE pedidos SET estado = 4 WHERE id = ? AND persona = ? AND estado = 1";
            $psCancelar = $this->conexionBD->prepare($sqlCancelar);
            $psCancelar->execute([$idPedido, $idUsuario]);
            
            if ($psCancelar->rowCount() == 0) {
                $this->conexionBD->rollBack();
                return false;
            }

            $sqlDetalle = "SELECT id_producto, cantidad FROM detalle WHERE id_pedido = ?";
            $psDetalle = $this->conexionBD->prepare($sqlDetalle);
            $psDetalle->execute([$idPedido]);

            $sqlDevolverStock = "UPDATE productos SET existencias = existencias + ? WHERE id = ?";
            $psStock = $this->conexionBD->prepare($sqlDevolverStock);

            while ($row = $psDetalle->fetch(PDO::FETCH_ASSOC)) {
                $psStock->execute([$row['cantidad'], $row['id_producto']]);
            }

            $this->conexionBD->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conexionBD->inTransaction()) {
                $this->conexionBD->rollBack();
            }
            return false;
        }
    }

    public function guardarMensajeContacto($nombre, $email, $asunto, $mensaje) {
        try {
            $sql = "INSERT INTO mensajes (nombre, email, asunto, mensaje, fecha) VALUES (?, ?, ?, ?, CURDATE())";
            $stmt = $this->conexionBD->prepare($sql);
            return $stmt->execute([$nombre, $email, $asunto, $mensaje]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function guardarTarjeta($idUsuario, $numero, $titular, $caducidad) {
        try {
            // SIMULACIÓN DE TOKENIZACIÓN (Cumplimiento básico PCI-DSS para el TFG)
            // Eliminamos espacios y cogemos solo los últimos 4 dígitos
            $numeroLimpio = preg_replace('/\D/', '', $numero);
            $ultimos4 = substr($numeroLimpio, -4);
            $numeroEnmascarado = "**** **** **** " . $ultimos4;
            
            // NOTA PARA LA MEMORIA DEL TFG: En un entorno real (Stripe/Redsys), 
            // no se guarda la tarjeta, se guarda un token seguro (ej: tok_1Hh98...).
            
            $sql = "INSERT INTO tarjetas (id_usuario, numero, titular, caducidad) VALUES (?, ?, ?, ?)";
            $stmt = $this->conexionBD->prepare($sql);
            
            // Guardamos el número ya enmascarado
            return $stmt->execute([$idUsuario, $numeroEnmascarado, $titular, $caducidad]);
        } catch (Exception $e) {
            error_log("Error al guardar tarjeta tokenizada: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTarjetasUsuario($idUsuario) {
        $listaTarjetas = [];
        try {
            $sql = "SELECT * FROM tarjetas WHERE id_usuario = ?";
            $stmt = $this->conexionBD->prepare($sql);
            $stmt->execute([$idUsuario]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $t = new TarjetaBD();
                $t->setId($row['id']);
                $t->setIdUsuario($row['id_usuario']);
                $t->setNumero($row['numero']);
                $t->setTitular($row['titular']);
                $t->setCaducidad($row['caducidad']);
                $listaTarjetas[] = $t;
            }
        } catch (Exception $e) {}
        return $listaTarjetas;
    }

    public function asignarRepartidor($idPedido, $idRepartidor, $nuevoEstado) {
        try {
            $sql = "UPDATE pedidos SET id_repartidor = ?, estado = ? WHERE id = ?";
            $stmt = $this->conexionBD->prepare($sql);
            $stmt->execute([$idRepartidor, $nuevoEstado, $idPedido]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // Obtiene todos los pedidos que acaban de entrar (Estado 1 = Pendiente)
    public function obtenerPedidosPendientes() {
        $lista = [];
        try {
            // Hacemos un JOIN para traer también el nombre del cliente y la dirección de destino
            $sql = "SELECT p.id, p.fecha, p.importe, u.nombre as cliente, d.calle_texto as destino 
                    FROM pedidos p 
                    JOIN usuarios u ON p.persona = u.id
                    JOIN direcciones d ON p.id_direccion_destino = d.id
                    WHERE p.estado = 1 
                    ORDER BY p.fecha ASC";
            
            $stmt = $this->conexionBD->query($sql);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lista[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error obteniendo pedidos pendientes: " . $e->getMessage());
        }
        return $lista;
    }

    // Obtiene a los usuarios que son repartidores (Rol = 2)
    public function obtenerRepartidores() {
        $repartidores = [];
        try {
            $sql = "SELECT id, nombre, apellidos FROM usuarios WHERE rol = 2 AND activo = 1";
            $stmt = $this->conexionBD->query($sql);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $repartidores[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error obteniendo repartidores: " . $e->getMessage());
        }
        return $repartidores;
    }

    // --- NUEVAS FUNCIONES PARA EL ADMIN: PRODUCTOS ---

    // 1. Añadir un nuevo servicio/producto
    public function agregarProductoBD($descripcion, $precio, $existencias, $imagen, $caracteristicas, $colorCss) {
        try {
            $sql = "INSERT INTO productos (descripcion, precio, existencias, imagen, caracteristicas, color_css) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conexionBD->prepare($sql);
            return $stmt->execute([$descripcion, $precio, $existencias, $imagen, $caracteristicas, $colorCss]);
        } catch (Exception $e) {
            error_log("Error al añadir producto: " . $e->getMessage());
            return false;
        }
    }

    // 2. Modificar un servicio/producto existente (poner existencias a 0 equivale a borrarlo)
    public function modificarProductoBD($id, $descripcion, $precio, $existencias, $caracteristicas, $colorCss) {
        try {
            // No actualizamos la imagen por simplicidad, pero se podría añadir
            $sql = "UPDATE productos SET descripcion=?, precio=?, existencias=?, caracteristicas=?, color_css=? WHERE id=?";
            $stmt = $this->conexionBD->prepare($sql);
            return $stmt->execute([$descripcion, $precio, $existencias, $caracteristicas, $colorCss, $id]);
        } catch (Exception $e) {
            error_log("Error al modificar producto: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPedidosFiltrados($idUsuario, $idProducto, $fecha, $operadorFecha, $logica = 'AND') {
        $sql = "SELECT p.*, u.nombre as cliente, e.descripcion as estado_nombre 
                FROM pedidos p 
                JOIN usuarios u ON p.persona = u.id 
                JOIN estados e ON p.estado = e.id 
                JOIN detalle d ON p.id = d.id_pedido 
                WHERE 1=1";
        
        $params = [];
        $condiciones = [];

        // Filtro por Usuario
        if (!empty($idUsuario)) {
            $condiciones[] = "p.persona = ?";
            $params[] = $idUsuario;
        }

        // Filtro por Producto
        if (!empty($idProducto)) {
            $condiciones[] = "d.id_producto = ?";
            $params[] = $idProducto;
        }

        // Filtro por Fecha y su operador (<=, =, >=)
        if (!empty($fecha)) {
            $condiciones[] = "p.fecha $operadorFecha ?";
            $params[] = $fecha;
        }

        // Si hay filtros, los unimos con la lógica seleccionada (AND/OR)
        if (count($condiciones) > 0) {
            $sql .= " AND (" . implode(" $logica ", $condiciones) . ")";
        }

        $sql .= " GROUP BY p.id ORDER BY p.fecha DESC";

        try {
            $stmt = $this->conexionBD->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en filtrado admin: " . $e->getMessage());
            return [];
        }
    }

    //  Obtiene la lista completa de usuarios registrados en el sistema.
     
    public function obtenerTodosLosUsuarios() {
        $usuarios = [];
        try {
            $sql = "SELECT id, usuario, nombre, apellidos, rol, activo FROM usuarios ORDER BY id DESC";
            $stmt = $this->conexionBD->query($sql);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error obteniendo la lista de usuarios: " . $e->getMessage());
        }
        return $usuarios;
    }

    /**
     * Cambia el estado 'activo' de un usuario (1 = Activo, 0 = Inactivo/Baja).
     */
    public function cambiarEstadoUsuario($idUsuario, $nuevoEstado) {
        try {
            $sql = "UPDATE usuarios SET activo = ? WHERE id = ?";
            $stmt = $this->conexionBD->prepare($sql);
            return $stmt->execute([$nuevoEstado, $idUsuario]);
        } catch (Exception $e) {
            error_log("Error al cambiar el estado del usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un usuario SOLO si no tiene pedidos registrados en el sistema.
     */
    public function eliminarUsuarioSiSinPedidos($idUsuario) {
        try {
            // 1. Comprobamos si el usuario tiene pedidos asociados
            $sqlCheck = "SELECT COUNT(*) as total FROM pedidos WHERE persona = ?";
            $stmtCheck = $this->conexionBD->prepare($sqlCheck);
            $stmtCheck->execute([$idUsuario]);
            $resultado = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            // Si tiene al menos 1 pedido, denegamos el borrado
            if ($resultado['total'] > 0) {
                return false;
            }

            // 2. Si no tiene pedidos, primero eliminamos sus tarjetas (si las tiene) 
            // para evitar errores de clave foránea (foreign key)
            $sqlTarjetas = "DELETE FROM tarjetas WHERE id_usuario = ?";
            $stmtTarjetas = $this->conexionBD->prepare($sqlTarjetas);
            $stmtTarjetas->execute([$idUsuario]);

            // 3. Finalmente, eliminamos el usuario
            $sqlDelete = "DELETE FROM usuarios WHERE id = ?";
            $stmtDelete = $this->conexionBD->prepare($sqlDelete);
            return $stmtDelete->execute([$idUsuario]);

        } catch (Exception $e) {
            error_log("Error al intentar eliminar el usuario: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarRolUsuario($idUsuario, $nuevoRol) {
        try {
            $sql = "UPDATE usuarios SET rol = ? WHERE id = ?";
            $stmt = $this->conexionBD->prepare($sql);
            return $stmt->execute([$nuevoRol, $idUsuario]);
        } catch (Exception $e) {
            error_log("Error al cambiar el rol del usuario: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerMensajes() {
        $mensajes = [];
        try {
            $stmt = $this->conexionBD->query("SELECT * FROM mensajes ORDER BY fecha DESC, id DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mensajes[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error obteniendo mensajes: " . $e->getMessage());
        }
        return $mensajes;
    }

    // Obtener métricas rápidas para el Dashboard
    public function obtenerEstadisticas() {
        $stats = ['total_entregados' => 0, 'ingresos' => 0, 'total_pendientes' => 0];
        try {
            // Estado 3 = Entregado, 1 = Pendiente (Ajusta según tu tabla de estados)
            $stmt = $this->conexionBD->query("SELECT COUNT(*) as entregados, SUM(importe) as ingresos FROM pedidos WHERE estado = 3");
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['total_entregados'] = $row['entregados'] ?? 0;
                $stats['ingresos'] = $row['ingresos'] ?? 0;
            }
            $stmt2 = $this->conexionBD->query("SELECT COUNT(*) as pendientes FROM pedidos WHERE estado = 1");
            if ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $stats['total_pendientes'] = $row2['pendientes'] ?? 0;
            }
        } catch (Exception $e) {}
        return $stats;
    }

    // Actualizamos la consulta de pendientes para traer LATITUD y LONGITUD para el mapa
    public function obtenerPedidosPendientesMapa() {
        $lista = [];
        try {
            $sql = "SELECT p.id, p.fecha, p.importe, u.nombre as cliente, d.calle_texto as destino, d.latitud, d.longitud 
                    FROM pedidos p 
                    JOIN usuarios u ON p.persona = u.id
                    JOIN direcciones d ON p.id_direccion_destino = d.id
                    WHERE p.estado = 1 
                    ORDER BY p.fecha ASC";
            $stmt = $this->conexionBD->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lista[] = $row;
            }
        } catch (Exception $e) {}
        return $lista;
    }

    // Cuenta el total de productos para calcular las páginas
    public function contarProductos() {
        try {
            $stmt = $this->conexionBD->query("SELECT COUNT(*) as total FROM productos");
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    // Obtiene los productos limitados por página
    public function obtenerProductosPaginados($limite, $offset) {
        $productos = [];
        try {
            // Usamos PDO con parámetros nombrados. IMPORTANTE: Usar bindValue con PARAM_INT
            $sql = "SELECT id, descripcion, precio, existencias, imagen, caracteristicas, color_css 
                    FROM productos LIMIT :limite OFFSET :offset";
            $stmt = $this->conexionBD->prepare($sql);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $p = new ProductoBD();
                $p->setId($row['id']);
                $p->setDescripcion($row['descripcion']);
                $p->setPrecio($row['precio']);
                $p->setExistencias($row['existencias']);
                $p->setImagen($row['imagen']);
                $p->setCaracteristicas($row['caracteristicas']);
                $p->setColorCss($row['color_css']);
                $productos[] = $p;
            }
        } catch (Exception $e) {
            error_log("Error en paginación: " . $e->getMessage());
        }
        return $productos;
    }

    // Obtiene las rutas/pedidos asignados a un repartidor en concreto (Estado 2 = Enviado/En ruta)
    public function obtenerRutasRepartidor($idRepartidor) {
        $lista = [];
        try {
            $sql = "SELECT p.id, p.fecha, p.importe, u.nombre as cliente, u.telefono, 
                           dorigen.calle_texto as origen, dorigen.latitud as lat_origen, dorigen.longitud as lon_origen,
                           ddestino.calle_texto as destino, ddestino.latitud as lat_destino, ddestino.longitud as lon_destino 
                    FROM pedidos p 
                    JOIN usuarios u ON p.persona = u.id
                    JOIN direcciones dorigen ON p.id_direccion_origen = dorigen.id
                    JOIN direcciones ddestino ON p.id_direccion_destino = ddestino.id
                    WHERE p.estado = 2 AND p.id_repartidor = ?
                    ORDER BY p.fecha ASC";
            $stmt = $this->conexionBD->prepare($sql);
            $stmt->execute([$idRepartidor]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lista[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error obteniendo rutas del repartidor: " . $e->getMessage());
        }
        return $lista;
    }

    // Función para que el repartidor cambie el estado del pedido (3 = Entregado) o reporte incidencia
    public function actualizarEstadoReparto($idPedido, $idRepartidor, $nuevoEstado) {
        try {
            // Verificamos que el pedido es suyo antes de actualizar
            $sql = "UPDATE pedidos SET estado = ? WHERE id = ? AND id_repartidor = ?";
            $stmt = $this->conexionBD->prepare($sql);
            $stmt->execute([$nuevoEstado, $idPedido, $idRepartidor]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

}
?>