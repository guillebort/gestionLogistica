<?php
namespace servicios;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService {
    public function generarTicketReserva($idPedido, $usuarioDatos, $dirOrigen, $dirDestino, $total) {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        
        $fecha = date('d/m/Y');
        $nombreCliente = $usuarioDatos->getNombre() . ' ' . $usuarioDatos->getApellidos();
        
        // Es recomendable incluso extraer este HTML a una plantilla (ej. vistas/pdf_ticket.php)
        $htmlPdf = "
            <div style='font-family: Helvetica, sans-serif; color: #333;'>
                <h1 style='color: #0d6efd; text-align: center;'>LogisTFG</h1>
                <h2 style='text-align: center;'>Ticket de Reserva</h2>
                <hr>
                <p><strong>Referencia:</strong> REF-LOGIS-{$idPedido}</p>
                <p><strong>Cliente:</strong> {$nombreCliente}</p>
                <p><strong>Fecha:</strong> {$fecha}</p>
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
            </div>";
        
        $dompdf->loadHtml($htmlPdf);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output(); // Devuelve el PDF en memoria
    }
}