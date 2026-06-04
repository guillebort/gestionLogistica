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
    public function enviarRespuestaMensaje($emailCliente, $asuntoOriginal, $respuesta) {
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
            
            // ==========================================
            // OJO AQUÍ: Si quieres que llegue a un correo de prueba, 
            // comenta la línea de abajo y pon tu correo a mano.
            // Ej: $mail->addAddress('tucorreodeprueba@gmail.com');
            // ==========================================
            $mail->addAddress($emailCliente);

            $mail->isHTML(true);
            $mail->Subject = "RE: " . $asuntoOriginal . " - Soporte LogisTFG";
            
            // Diseño profesional del correo
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px;'>
                    <h2 style='color: #0ea5e9;'>Respuesta a tu consulta en LogisTFG</h2>
                    <p>Hola,</p>
                    <p>En respuesta a tu mensaje original con asunto: <strong>{$asuntoOriginal}</strong></p>
                    
                    <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #0ea5e9; margin: 20px 0;'>
                        <p style='margin: 0;'>" . nl2br($respuesta) . "</p>
                    </div>
                    
                    <hr style='border: none; border-top: 1px solid #cbd5e1; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #64748b;'>Atentamente,<br>El equipo de soporte de LogisTFG.</p>
                </div>";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando respuesta de soporte: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>