<?php
namespace servicios;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/config.php'; // Importamos las constantes

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    public function enviarConfirmacionConTicket($usuarioDatos, $idPedido, $pdfOutput) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST; 
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER; 
            $mail->Password   = MAIL_PASS; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;

            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
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
?>