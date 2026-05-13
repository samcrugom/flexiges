<?php

namespace App\Services;

use App\Models\{VentaDocumento, Empresa};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function generarFactura(VentaDocumento $doc): string
    {
        $doc->load(['cliente','lineas.articulo','lineas.tipoIva','ivaDetalle.tipoIva','vendedor','formaPago']);
        $empresa = Empresa::actual();

        $pdf = Pdf::loadView('pdf.factura', compact('doc','empresa'))
            ->setPaper('a4', 'portrait');

        $path = 'facturas/' . $doc->numero . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        $doc->update(['pdf_path' => $path]);
        return $path;
    }

    public function streamFactura(VentaDocumento $doc): \Illuminate\Http\Response
    {
        $doc->load(['cliente','lineas.articulo','lineas.tipoIva','ivaDetalle.tipoIva','vendedor','formaPago']);
        $empresa = Empresa::actual();

        return Pdf::loadView('pdf.factura', compact('doc','empresa'))
            ->setPaper('a4', 'portrait')
            ->stream($doc->numero . '.pdf');
    }
}