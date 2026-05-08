<?php
require_once '../modelos/AccesoBD.php';
require_once '../modelos/Modelos.php';

// Cargar el autoloader de Composer para PHPMailer
require '../vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

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
            
            // 1. CONFIGURAR Y GENERAR EL TICKET EN PDF
            $options = new Options();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            
            // Construimos el HTML que irá dentro del PDF
            $htmlPdf = "
                <div style='font-family: Helvetica, sans-serif; color: #333;'>
                    <h1 style='color: #0d6efd; text-align: center;'>LogisTFG</h1>
                    <h2 style='text-align: center;'>Ticket de Reserva</h2>
                    <hr>
                    <p><strong>Referencia:</strong> REF-LOGIS-{$idNuevoPedido}</p>
                    <p><strong>Cliente:</strong> {$usuarioDatos->getNombre()} {$usuarioDatos->getApellidos()}</p>
                    <p><strong>Fecha:</strong> " . date('d/m/Y') . "</p>
                    <br>
                    <h3>Detalles del Servicio:</h3>
                    <ul>
                        <li><strong>Origen:</strong> {$dirOrigen}</li>
                        <li><strong>Destino:</strong> {$dirDestino}</li>
                    </ul>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                        <tr style='background-color: #f8f9fa;'>
                            <th style='padding: 10px; border: 1px solid #ddd;'>Descripción</th>
                            <th style='padding: 10px; border: 1px solid #ddd;'>Total</th>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd;'>Servicios Logísticos Contratados</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'><strong>{$total} €</strong></td>
                        </tr>
                    </table>
                    <br>
                    <p style='text-align: center; font-size: 12px; color: #777;'>Gracias por confiar en nuestra red de transporte.</p>
                </div>
            ";
            
            $dompdf->loadHtml($htmlPdf);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // Obtenemos el contenido del PDF generado en una variable (en memoria)
            $pdfOutput = $dompdf->output();

            // 2. CONFIGURAR EL CORREO Y ADJUNTAR EL PDF
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'tu_correo_tfg@gmail.com'; // Sustituir por el tuyo
                $mail->Password   = 'tu_contraseña_de_aplicacion'; // Sustituir
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinatarios
                $mail->setFrom('no-reply@logistfg.es', 'LogisTFG');
                $mail->addAddress($usuarioDatos->getUsuario(), $usuarioDatos->getNombre());

                // Adjuntar el PDF generado en memoria
                // addStringAttachment recibe: (el_contenido_en_crudo, el_nombre_del_archivo, codificación, tipo_MIME)
                $mail->addStringAttachment($pdfOutput, "Ticket_Reserva_REF-LOGIS-{$idNuevoPedido}.pdf", 'base64', 'application/pdf');

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = "Confirmacion de Reserva #REF-LOGIS-{$idNuevoPedido} y Ticket de Compra";
                
                $cuerpoCorreo = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                        <h2 style='color: #0d6efd;'>¡Reserva Confirmada!</h2>
                        <p>Hola <strong>{$usuarioDatos->getNombre()}</strong>,</p>
                        <p>Hemos recibido correctamente tu solicitud de transporte. Tienes adjunto en este correo el ticket en formato PDF con los detalles de tu reserva.</p>
                        <p>En breve asignaremos tu envío a uno de nuestros repartidores. ¡Gracias por confiar en LogisTFG!</p>
                    </div>";
                
                $mail->Body = $cuerpoCorreo;
                $mail->send();
                
            } catch (Exception $e) {
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