<?php

namespace App\Services;

use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PdfService
{
    /**
     * Generate PDF using dompdf
     */
    public function generatePdf(string $view, array $data = [], string $filename = 'export.pdf'): Response
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Arial',
                'isPhpEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        
        return $pdf->download($filename);
    }

}