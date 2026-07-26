<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

/** Convierte vistas Blade internas en descargas PDF controladas. */
class PdfService
{
    /**
     * Renderiza una vista Blade como PDF sin habilitar recursos remotos.
     *
     * @param  array<string, mixed>  $viewData
     */
    public function download(string $view, array $viewData, string $filename, string $orientation = 'portrait'): Response
    {
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        // Bloquear recursos remotos impide que el PDF solicite URLs externas inesperadas.
        $options->set('isRemoteEnabled', false);

        $pdf = new Dompdf($options);
        $pdf->loadHtml(view($view, $viewData)->render(), 'UTF-8');
        $pdf->setPaper('A4', $orientation);
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
