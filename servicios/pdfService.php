<?php
// Incluye tu librería FPDF (Ajusta la ruta según dónde la tengas guardada)
require_once '../librerias/fpdf/fpdf.php'; 

class PdfService extends FPDF {

    // Cabecera profesional del PDF
    function Header() {
        // Logotipo (Si tienes una imagen, descomenta la siguiente línea)
        // $this->Image('../img/logo.png', 10, 8, 33);
        
        // Arial bold 15
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(13, 110, 253); // Color azul primario (Bootstrap)
        $this->Cell(100, 10, utf8_decode('LogisTFG - Operador Logístico'), 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(90, 10, utf8_decode('ALBARÁN DE TRANSPORTE'), 0, 1, 'R');
        
        // Línea divisoria
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.5);
        $this->Line(10, 22, 200, 22);
        $this->Ln(10);
    }

    // Pie de página
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, utf8_decode('Página ').$this->PageNo().'/{nb} - LogisTFG (Proyecto Fin de Grado) - Documento con validez legal', 0, 0, 'C');
    }

    // Método principal que construye el ticket
    public function generarTicket($pedido, $detalles) {
        $this->AliasNbPages();
        $this->AddPage();
        
        // 1. BLOQUE DE DATOS DEL PEDIDO (Nº de Seguimiento y Fecha)
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(240, 240, 240);
        $this->Cell(95, 8, utf8_decode(' TRACKING / Nº RESERVA: #') . str_pad($pedido['id'], 6, '0', STR_PAD_LEFT), 1, 0, 'L', true);
        $this->Cell(95, 8, utf8_decode(' FECHA EMISIÓN: ') . date('d/m/Y H:i', strtotime($pedido['fecha'])), 1, 1, 'R', true);
        $this->Ln(8);

        // 2. CAJAS DE ORIGEN Y DESTINO (Ruta logística)
        $yInicial = $this->GetY();
        
        // Caja Origen
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(90, 7, utf8_decode('📍 DATOS DE RECOGIDA (ORIGEN)'), 'LTR', 1, 'L');
        $this->SetFont('Arial', '', 10);
        // MultiCell permite que si la dirección es larga, salte de línea sin romper la caja
        $this->MultiCell(90, 6, utf8_decode($pedido['direccion_origen'] ?? 'Sede Central'), 'LR', 'L');
        $this->Cell(90, 4, '', 'LBR', 0, 'L'); // Cierre de la caja
        
        // Posicionarnos para la caja de destino a la derecha
        $this->SetXY(110, $yInicial);
        
        // Caja Destino
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(90, 7, utf8_decode('🎯 DATOS DE ENTREGA (DESTINO)'), 'LTR', 1, 'L');
        $this->SetXY(110, $this->GetY());
        $this->SetFont('Arial', '', 10);
        $this->MultiCell(90, 6, utf8_decode($pedido['direccion_destino'] ?? 'Dirección del cliente'), 'LR', 'L');
        $this->SetXY(110, $this->GetY());
        $this->Cell(90, 4, '', 'LBR', 1, 'L'); // Cierre de la caja
        $this->Ln(10);

        // 3. TABLA DE DESGLOSE DE SERVICIOS
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(13, 110, 253); // Cabecera Azul
        $this->SetTextColor(255, 255, 255);
        $this->Cell(100, 8, utf8_decode(' Servicio / Concepto'), 1, 0, 'L', true);
        $this->Cell(30, 8, utf8_decode('Bultos'), 1, 0, 'C', true);
        $this->Cell(30, 8, utf8_decode('P. Unitario'), 1, 0, 'C', true);
        $this->Cell(30, 8, utf8_decode('Subtotal'), 1, 1, 'C', true);

        // Contenido de la tabla
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        
        $totalCalculado = 0;
        foreach ($detalles as $d) {
            $subtotal = $d['cantidad'] * $d['precio_unitario'];
            $totalCalculado += $subtotal;
            
            $this->Cell(100, 8, utf8_decode(' ' . $d['nombre_producto']), 1, 0, 'L');
            $this->Cell(30, 8, $d['cantidad'], 1, 0, 'C');
            $this->Cell(30, 8, number_format($d['precio_unitario'], 2) . utf8_decode(' €'), 1, 0, 'C');
            $this->Cell(30, 8, number_format($subtotal, 2) . utf8_decode(' €'), 1, 1, 'C');
        }
        
        // 4. TOTAL Y ESTADO DE PAGO
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 12);
        // Desplazamos a la derecha
        $this->Cell(130, 10, utf8_decode('TOTAL PAGADO (IVA INCLUIDO):'), 0, 0, 'R');
        
        $this->SetTextColor(0, 150, 0); // Verde para indicar pagado
        $this->Cell(60, 10, number_format($pedido['importe'], 2) . utf8_decode(' €'), 0, 1, 'R');
        
        // Sello de pagado
        $this->SetTextColor(0, 0, 0);
        $this->Ln(15);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, utf8_decode('Estado Económico: PAGADO mediante pasarela electrónica.'), 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, utf8_decode('Este documento acredita la correcta recepción y pago del servicio logístico asociado.'), 0, 1, 'C');

        // Salida del PDF (I = mostrar en el navegador, D = forzar descarga)
        $this->Output('I', 'Albaran_LogisTFG_' . $pedido['id'] . '.pdf');
    }
}
?>