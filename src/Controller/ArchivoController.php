<?php

namespace App\Controller;

use App\Repository\ArchivoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

namespace App\Controller;

use App\Entity\Archivo;
use App\Repository\ArchivoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ArchivoController extends AbstractController
{
    #[Route('/descargar/{id}', name: 'app_archivo_descargar')]
    public function descargarArchivo(Archivo $archivo): Response
    {
        // Si NO tiene permiso para publicar → mostrar vista de acceso denegado
        if (!$archivo->isPermitidoPublicar()) {
            return $this->render('archivo/denegado.html.twig', [
                'archivo' => $archivo
            ]);
        }

        // Si tiene permiso → mostrar vista de previsualización
        return $this->render('archivo/previsualizar.html.twig', [
            'archivo' => $archivo
        ]);
    }

    // #[Route('/previsualizar-pdf/{id}', name: 'app_archivo_previsualizar_pdf')]
    // public function previsualizarPdf(Archivo $archivo): Response
    // {
    //     // 📂 Obtiene la ruta completa del archivo
    //     $ruta = $this->getParameter('archivos_directory') . '/' . $archivo->getNombreArchivo();

    //     // Si el archivo no existe, lanza un error 404
    //     if (!file_exists($ruta)) {
    //         throw $this->createNotFoundException('El archivo no se encuentra en el servidor.');
    //     }

    //     // Crea una respuesta que sirve el archivo
    //     // NOTA: No usamos ResponseHeaderBag::DISPOSITION_ATTACHMENT para que el navegador lo muestre en lugar de descargarlo
    //     return $this->file($ruta);
    // }

    #[Route('/archivo/descargar-directo/{id}', name: 'app_archivo_descargar_directo')]
    public function descargarDirecto(Archivo $archivo): Response
    {
        $ruta = $this->getParameter('archivos_directory') . '/' . $archivo->getNombreArchivo();
        if (!file_exists($ruta)) {
            throw $this->createNotFoundException('El archivo no se encuentra en el servidor.');
        }

        // Usar el título para el nombre de la descarga
        return $this->file($ruta, $archivo->getTitulo() . '.' . pathinfo($archivo->getNombreArchivo(), PATHINFO_EXTENSION), ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}

