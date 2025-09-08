<?php
// src/Service/PdfGeneratorService.php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService
{
    private Dompdf $dompdf;
    private Environment $twig;

    public function __construct(Dompdf $dompdf, Environment $twig)
    {
        $this->dompdf = $dompdf;
        $this->twig = $twig;
        
        // Configurar opciones de DomPDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        
        $this->dompdf->setOptions($options);
    }

    /**
     * Genera un PDF desde una plantilla Twig
     */
    public function generatePdfFromTemplate(string $template, array $data = [], array $options = []): string
    {
        // Renderizar la plantilla
        $html = $this->twig->render($template, $data);
        
        // Configurar orientación y tamaño de papel
        $orientation = $options['orientation'] ?? 'portrait';
        $paperSize = $options['paper_size'] ?? 'A4';
        
        // Generar PDF
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paperSize, $orientation);
        $this->dompdf->render();
        
        return $this->dompdf->output();
    }

    /**
     * Genera una respuesta HTTP con el PDF
     */
    public function generatePdfResponse(string $template, array $data = [], string $filename = 'document.pdf', array $options = []): Response
    {
        $pdfContent = $this->generatePdfFromTemplate($template, $data, $options);
        
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        
        return $response;
    }

    /**
     * Genera PDF inline (para mostrar en navegador)
     */
    public function generatePdfInline(string $template, array $data = [], string $filename = 'document.pdf', array $options = []): Response
    {
        $pdfContent = $this->generatePdfFromTemplate($template, $data, $options);
        
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('inline; filename="%s"', $filename));
        
        return $response;
    }
}