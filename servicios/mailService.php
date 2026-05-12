<?php
namespace servicios;

require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    public function enviarConfirmacionConTicket($usuarioDatos, $idPedido, $pdfOutput) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tu_correo_tfg@gmail.com'; 
            $mail->Password   = 'tu_contraseña_de_aplicacion'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@logistfg.es', 'LogisTFG');
            $mail->addAddress($usuarioDatos->getUsuario(), $usuarioDatos->getNombre());

            $mail->addStringAttachment($pdfOutput, "Ticket_Reserva_REF-LOGIS-{$idPedido}.pdf", 'base64', 'application/pdf');

            $mail->isHTML(true);
            $mail->Subject = "Confirmacion de Reserva #REF-LOGIS-{$idPedido}";
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2 style='color: #0d6efd;'>¡Reserva Confirmada!</h2>
                    <p>Hola <strong>{$usuarioDatos->getNombre()}</strong>,</p>
                    <p>Hemos recibido correctamente tu solicitud de transporte. Tienes adjunto en este correo el ticket en formato PDF.</p>
                </div>";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error de PHPMailer: {$mail->ErrorInfo}");
            return false;
        }
    }
}