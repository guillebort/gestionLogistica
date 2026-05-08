<?php
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

// Cargar el autoloader de Composer para PHPMailer
require '../vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['codigo'] ?? null;
    $carrito = $_SESSION['carritoJSON'] ?? null;

    if ($idUsuario == null || $carrito == null) {
        header("Location: ../tienda/productos.php");
        exit;
    }

    $con = AccesoBD::getInstance();

    $tipoTarjeta = $_POST['tarjetaGuardada'] ?? 'NUEVA';
    $guardarTarjeta = $_POST['guardarTarjetaCheck'] ?? '';

    // Guardado de la tarjeta tokenizada (usando la nueva función segura)
    if ($tipoTarjeta === 'NUEVA' && $guardarTarjeta === 'SI') {
        $numero = $_POST['numeroTarjeta'] ?? '';
        $titular = $_POST['titularTarjeta'] ?? '';
        $caducidad = $_POST['caducidadTarjeta'] ?? '';
        
        if (!empty($numero) && !empty($titular) && !empty($caducidad)) {
            $con->guardarTarjeta($idUsuario, $numero, $titular, $caducidad);
        }
    }

    // Recuperamos datos de ruta
    $total = $_SESSION['totalPedido'];
    $dirOrigen = $_SESSION['direccionOrigen'];
    $latOrigen = $_SESSION['latOrigen'];
    $lonOrigen = $_SESSION['lonOrigen'];
    $dirDestino = $_SESSION['direccionDestino'];
    $latDestino = $_SESSION['latDestino'];
    $lonDestino = $_SESSION['lonDestino'];

    // Guardamos el pedido en la BD
    $idNuevoPedido = $con->guardarPedido($idUsuario, $total, $carrito, $dirOrigen, $latOrigen, $lonOrigen, $dirDestino, $latDestino, $lonDestino);

    if ($idNuevoPedido > 0) {
        
        // ==========================================
        // LÓGICA DE ENVÍO DE CORREO (PHPMailer)
        // ==========================================
        $usuarioDatos = $con->obtenerUsuarioBD($idUsuario);
        
        if ($usuarioDatos != null) {
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP (Ejemplo usando Gmail)
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'tu_correo_tfg@gmail.com'; // Pon tu correo de pruebas
                $mail->Password   = 'tu_contraseña_de_aplicacion'; // Contraseña de aplicación de Google
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinatarios
                $mail->setFrom('no-reply@logistfg.es', 'LogisTFG');
                $mail->addAddress($usuarioDatos->getUsuario(), $usuarioDatos->getNombre());

                // Contenido
                $mail->isHTML(true);
                $mail->Subject = "Confirmacion de Reserva #REF-LOGIS-{$idNuevoPedido}";
                
                // Cuerpo del correo con un poco de HTML para que quede profesional
                $cuerpoCorreo = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                        <h2 style='color: #0d6efd;'>¡Gracias por confiar en LogisTFG!</h2>
                        <p>Hola <strong>{$usuarioDatos->getNombre()}</strong>,</p>
                        <p>Hemos recibido correctamente tu solicitud de transporte.</p>
                        <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #198754; margin-bottom: 20px;'>
                            <p><strong>Referencia:</strong> REF-LOGIS-{$idNuevoPedido}</p>
                            <p><strong>Importe Total:</strong> {$total} €</p>
                            <p><strong>Recogida:</strong> {$dirOrigen}</p>
                            <p><strong>Entrega:</strong> {$dirDestino}</p>
                        </div>
                        <p>En breve asignaremos tu envío a uno de nuestros repartidores y podrás ver la actualización en tu panel de cliente.</p>
                        <hr>
                        <p style='font-size: 12px; color: #777;'>Este es un correo automático, por favor no respondas a esta dirección.</p>
                    </div>";
                
                $mail->Body = $cuerpoCorreo;
                $mail->send();
            } catch (Exception $e) {
                // Si el correo falla, no rompemos la web, solo dejamos un log para depurar
                error_log("Error de PHPMailer: {$mail->ErrorInfo}");
            }
        }

        // Limpieza de sesión tras éxito
        unset($_SESSION['carritoJSON'], $_SESSION['totalPedido'], $_SESSION['direccionOrigen'], $_SESSION['direccionDestino']);
        $_SESSION['mensaje'] = "REF-LOGIS-" . $idNuevoPedido;
        header("Location: ../tienda/pedidoCompletado.php");
    } else {
        $_SESSION['mensaje'] = "Error técnico al procesar el envío.";
        header("Location: ../tienda/procesarPedido.php");
    }
    exit;
}
?>