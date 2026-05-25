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

    public function generarAlbaranPdf($datosPedido) {
        // Habilitar isRemoteEnabled es CRÍTICO para que lea imágenes Base64
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true); 
        $dompdf = new Dompdf($options);
        
        // Cargamos el CSS de forma nativa para que Dompdf lo pueda leer
        $rutaCss = realpath(__DIR__ . '/../css/estilo.css');
        $cssContent = file_get_contents($rutaCss);
        
        $fecha = date('d/m/Y', strtotime($datosPedido['fecha']));
        $firmaHtml = "";

        // Pintar imagen Base64 o texto de pendiente
        if (!empty($datosPedido['firma'])) {
            $firmaHtml = "<img src='{$datosPedido['firma']}' class='pdf-firma-img'>";
        } else {
            $firmaHtml = "<div class='pdf-pendiente'>[Pendiente de rúbrica]</div>";
        }

        $htmlPdf = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>{$cssContent}</style>
        </head>
        <body class='pdf-container'>
            <div class='pdf-header'>
                <h1 class='pdf-title'>LogisTFG</h1>
                <h2 class='pdf-subtitle'>Albarán de Entrega (POD)</h2>
            </div>
            
            <hr class='pdf-divider'>

            <table class='pdf-table-info'>
                <tr><th>Pedido Ref.</th><td>#{$datosPedido['id']}</td></tr>
                <tr><th>Fecha</th><td>{$fecha}</td></tr>
                <tr><th>Cliente</th><td>{$datosPedido['cliente']}</td></tr>
                <tr><th>Recogida</th><td>{$datosPedido['origen']}</td></tr>
                <tr><th>Destino</th><td>{$datosPedido['destino']}</td></tr>
                <tr><th>Importe</th><td><strong>{$datosPedido['importe']} €</strong></td></tr>
            </table>

            <table class='pdf-table-firmas'>
                <tr>
                    <td class='pdf-col-firma'>
                        <div class='pdf-firma-titulo'>Responsable Logístico</div>
                        <div style='height: 85px;'></div> <!-- Espaciador -->
                        <div class='pdf-firma-linea'></div>
                        <div class='pdf-firma-sub'>Firma Autorizada</div>
                    </td>
                    <td class='pdf-col-firma'>
                        <div class='pdf-firma-titulo'>Conformidad del Receptor</div>
                        <div style='height: 85px; text-align: center;'>{$firmaHtml}</div>
                        <div class='pdf-firma-linea'></div>
                        <div class='pdf-firma-sub'>Prueba Digital de Entrega</div>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
        
        $dompdf->loadHtml($htmlPdf);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }
}