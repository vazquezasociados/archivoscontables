<?php

namespace App\Controller;

use App\Entity\Memo;
use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PdfController extends AbstractController
{
    #[Route('/admin/memo/{id}/pdf', name: 'admin_memo_pdf')]
    public function generarPdf(Memo $memo, PdfGeneratorService $pdfGenerator): Response
    {
        return $pdfGenerator->generatePdfInline('pdf/memo.html.twig', [
            'memo' => $memo,
            'nombre' => $memo->getUsuario() ? $memo->getUsuario()->getNombre() : 'Sin nombre'
        ], 'memo_'.$memo->getId().'.pdf');
    }
}
