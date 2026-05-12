<?php
// --- Archivo: Modelos.php ---

class ProductoBD {
    private $id, $descripcion, $precio, $existencias, $imagen, $caracteristicas, $colorCss;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function getPrecio() { return $this->precio; }
    public function setPrecio($precio) { $this->precio = $precio; }
    public function getExistencias() { return $this->existencias; }
    public function setExistencias($existencias) { $this->existencias = $existencias; }
    public function getImagen() { return $this->imagen; }
    public function setImagen($imagen) { $this->imagen = $imagen; }
    public function getCaracteristicas() { return $this->caracteristicas; }
    public function setCaracteristicas($caracteristicas) { $this->caracteristicas = $caracteristicas; }
    public function getColorCss() { return $this->colorCss; }
    public function setColorCss($colorCss) { $this->colorCss = $colorCss; }
}

class ProductoCarrito {
    private $codigo, $descripcion, $precio, $cantidad;

    public function getCodigo() { return $this->codigo; }
    public function setCodigo($codigo) { $this->codigo = $codigo; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function getPrecio() { return $this->precio; }
    public function setPrecio($precio) { $this->precio = $precio; }
    public function getCantidad() { return $this->cantidad; }
    public function setCantidad($cantidad) { $this->cantidad = $cantidad; }
}

class UsuarioBD {
    private $id, $rol, $usuario, $nombre, $apellidos, $domicilio, $poblacion, $provincia, $cp, $telefono;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function getDomicilio() { return $this->domicilio; }
    public function setDomicilio($domicilio) { $this->domicilio = $domicilio; }
    public function getPoblacion() { return $this->poblacion; }
    public function setPoblacion($poblacion) { $this->poblacion = $poblacion; }
    public function getProvincia() { return $this->provincia; }
    public function setProvincia($provincia) { $this->provincia = $provincia; }
    public function getCp() { return $this->cp; }
    public function setCp($cp) { $this->cp = $cp; }
    public function getTelefono() { return $this->telefono; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function getUsuario() { return $this->usuario; }
    public function setUsuario($usuario) { $this->usuario = $usuario; }
    public function getRol() { return $this->rol; }
    public function setRol($rol) { $this->rol = $rol; }
}

class TarjetaBD {
    private $id, $idUsuario, $numero, $titular, $caducidad;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getIdUsuario() { return $this->idUsuario; }
    public function setIdUsuario($idUsuario) { $this->idUsuario = $idUsuario; }
    public function getNumero() { return $this->numero; }
    public function setNumero($numero) { $this->numero = $numero; }
    public function getTitular() { return $this->titular; }
    public function setTitular($titular) { $this->titular = $titular; }
    public function getCaducidad() { return $this->caducidad; }
    public function setCaducidad($caducidad) { $this->caducidad = $caducidad; }
    
    public function getNumeroOculto() {
        if ($this->numero != null && strlen($this->numero) >= 4) {
            return "**** **** **** " . substr($this->numero, -4);
        }
        return $this->numero;
    }
}

class DetallePedidoBD {
    private $producto, $cantidad, $precio;

    public function getProducto() { return $this->producto; }
    public function setProducto($producto) { $this->producto = $producto; }
    public function getCantidad() { return $this->cantidad; }
    public function setCantidad($cantidad) { $this->cantidad = $cantidad; }
    public function getPrecio() { return $this->precio; }
    public function setPrecio($precio) { $this->precio = $precio; }
}

class PedidoBD {
    private $id, $idUsuario, $idRepartidor;
    private $fecha, $origen, $destino;
    private $importeTotal, $latitud, $longitud;
    private $estado;
    private $detalles = [];

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getIdUsuario() { return $this->idUsuario; }
    public function setIdUsuario($idUsuario) { $this->idUsuario = $idUsuario; }
    public function getFecha() { return $this->fecha; }
    public function setFecha($fecha) { $this->fecha = $fecha; }
    public function getImporteTotal() { return $this->importeTotal; }
    public function setImporteTotal($importeTotal) { $this->importeTotal = $importeTotal; }
    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }
    public function getDetalles() { return $this->detalles; }
    public function setDetalles($detalles) { $this->detalles = $detalles; }
    public function getIdRepartidor() { return $this->idRepartidor; }
    public function setIdRepartidor($idRepartidor) { $this->idRepartidor = $idRepartidor; }
    public function getOrigen() { return $this->origen; }
    public function setOrigen($origen) { $this->origen = $origen; }
    public function getDestino() { return $this->destino; }
    public function setDestino($destino) { $this->destino = $destino; }
}
?>