<?php
// --- Archivo: AccesoBD.php ---
require_once 'Modelos.php';

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
            $host = 'localhost';
            $port = '3306'; // Cambia si usas el 3305 de tu Java
            $db   = 'gestionlogistica';
            $user = 'root';
            $pass = 'root'; // Pon aquí tu contraseña real
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
            try {
                $this->conexionBD = new PDO($dsn, $user, $pass);
                // Configurar PDO para que lance excepciones
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

    private function encriptarSHA1($clave) {
        return sha1($clave);
    }

    public function comprobarUsuarioBD($usuario, $clave) {
        try {
            $sql = "SELECT id FROM usuarios WHERE usuario=? AND clave=?";
            $stmt = $this->conexionBD->prepare($sql);
            $stmt->execute([$usuario, $this->encriptarSHA1($clave)]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['id'];
            }
        } catch (Exception $e) {}
        return -1;
    }

    public function registrarUsuarioBD($usuario, $clave, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono) {
        try {
            $sql = "INSERT INTO usuarios (usuario, clave, nombre, apellidos, domicilio, poblacion, provincia, cp, telefono, activo, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)";
            $stmt = $this->conexionBD->prepare($sql);
            return $stmt->execute([$usuario, $this->encriptarSHA1($clave), $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono]);
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
                $sql = "UPDATE usuarios SET nombre=?, apellidos=?, domicilio=?, poblacion=?, provincia=?, cp=?, telefono=? WHERE id=?";
                $stmt = $this->conexionBD->prepare($sql);
                return $stmt->execute([$nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono, $id]);
            } else {
                $sql = "UPDATE usuarios SET clave=?, nombre=?, apellidos=?, domicilio=?, poblacion=?, provincia=?, cp=?, telefono=? WHERE id=?";
                $stmt = $this->conexionBD->prepare($sql);
                return $stmt->execute([$this->encriptarSHA1($clave), $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono, $id]);
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
            $sql = "INSERT INTO tarjetas (id_usuario, numero, titular, caducidad) VALUES (?, ?, ?, ?)";
            $stmt = $this->conexionBD->prepare($sql);
            return $stmt->execute([$idUsuario, $numero, $titular, $caducidad]);
        } catch (Exception $e) {
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
}
?>