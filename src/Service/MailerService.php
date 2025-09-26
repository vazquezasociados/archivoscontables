<?php

namespace App\Service;

use App\Entity\Archivo;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    private string $appUrl;

    public function __construct(
        private MailerInterface $mailer,
        string $appUrl,
        private string $projectDir )
    {
        $this->appUrl = $appUrl;
    }

    public function sendWelcomeEmail(string $to, string $nombre, string $cuit, string $password): void
    {
        // dd('Hola estoy aqui');
       $email = (new Email())
        ->from('vazquez.cres.asoc@gmail.com') 
        ->to($to)
        ->subject('Bienvenido a la plataforma')
        ->html("
            <div style='background:#f4f4f4; padding:30px; text-align:center;'>
                <div style=\"
                    background:#fff; 
                    max-width:600px; 
                    margin:0 auto; 
                    padding:20px 30px; 
                    border:1px solid #ddd; 
                    border-radius:8px; 
                    box-shadow:0 4px 12px rgba(0,0,0,0.1); 
                    text-align:left;
                    font-family: Arial, sans-serif; 
                    color:#333;
                \">
                    <h2 style='color:#000; text-align:center;'>Hola {$nombre},</h2>
                    <p style='text-align:center;'>🎉 </strong>Bienvenido a nuestra plataforma.</strong></p>
                    <ul style='list-style:none; padding:0; text-align:center;'>
                        <li><strong>Usuario:</strong> {$cuit}</li>
                        <li><strong>Contraseña:</strong> {$password}</li>
                    </ul>
                    <p style='text-align:center;'>Ya puedes acceder con tus credenciales.</p>
                    <p style='text-align:center;'>
                        <a href='{$this->appUrl}/login' target='_blank' 
                        style='display:inline-block; background:#1a73e8; color:#fff; 
                                padding:10px 20px; text-decoration:none; border-radius:5px;'>
                            Iniciar sesión aquí
                        </a>
                    </p>
                    <div style='text-align:center; margin-top:20px;'>
                            <img src='cid:firma' alt='Firma' style='width:100%; max-width:600px; height:auto;'>
                    </div>
                </div>
            </div>
        ")

        // Incluir la imagen como un recurso embebido
        ->embedFromPath($this->projectDir.'/public/img/firma.jpeg', 'firma', 'image/jpeg');
            // dd($email);
        $this->mailer->send($email);
    }
    
    // public function sendArchivoNotification(Archivo $archivo): void
    // {
    //     $cliente = $archivo->getUsuarioClienteAsignado();

    //     if (!$cliente || !$cliente->getEmail()) {
    //         return; // nada que notificar si no hay cliente asignado
    //     }

    //     $email = (new Email())
    //         ->from('vazquez.cres.asoc@gmail.com')
    //         ->to($cliente->getEmail())
    //         ->subject('Archivo nuevo subido para usted')
    //         ->html("
    //             // <h2>Hola {$cliente->getNombre()},</h2>
    //             <p>Se ha subido un nuevo archivo para ti.</p>
    //             <p><b>{$archivo->getTitulo()}</b></p>
    //             <p>
    //             Puede acceder a la lista de sus archivos 
    //             <a href='{$this->appUrl}/admin/archivo' target='_blank' style='color:#1a73e8;'>
    //                 iniciando sesión aquí
    //             </a>.
    //             </p>
    //         ");

    //     $this->mailer->send($email);
    // }
    
    /**
     * Envía notificación para uno o múltiples archivos (compatible con ambos casos)
     */
   public function sendArchivoNotification($archivos): void
    {
        // Si es un solo archivo, convertir a array
        if ($archivos instanceof Archivo) {
            $archivos = [$archivos];
        }

        // Agrupar archivos por cliente
        $archivosPorCliente = [];
        
        foreach ($archivos as $archivo) {
            $cliente = $archivo->getUsuarioClienteAsignado();
            
            if (!$cliente || !$cliente->getEmail()) {
                continue;
            }

            $clienteId = $cliente->getId();
            
            if (!isset($archivosPorCliente[$clienteId])) {
                $archivosPorCliente[$clienteId] = [
                    'cliente' => $cliente,
                    'archivos' => []
                ];
            }
            
            $archivosPorCliente[$clienteId]['archivos'][] = $archivo;
        }

        // Enviar emails con pausa entre cada uno
        $cantidadClientes = count($archivosPorCliente);
        $contador = 0;
        
        foreach ($archivosPorCliente as $datos) {
            $this->enviarEmailCliente($datos['cliente'], $datos['archivos']);
            
            $contador++;
            
            // Pausa de 1 segundo entre emails (excepto el último)
            // if ($contador < $cantidadClientes) {
            //     sleep(1);
            // }
        }
    }
    /**
     * Método privado para enviar email a un cliente específico
     */
    private function enviarEmailCliente($cliente, array $archivos): void
    {
        $cantidadArchivos = count($archivos);
        
        // Determinar subject y mensaje según cantidad
        if ($cantidadArchivos === 1) {
            $subject = 'Nuevos archivos subidos para usted.';
            $mensajeIntro = 'Los siguientes archivos estan disponibles para ser descargados:';
        } else {
            $subject = "Los siguientes archivos estan disponibles para ser descargados:)";
            $mensajeIntro = "Los siguientes archivos estan disponibles para ser descargados:";
        }

        // Generar lista de archivos
        $listaArchivos = '';
        foreach ($archivos as $archivo) {
            $listaArchivos .= "<li style='margin-bottom: 10px;'>";
            $listaArchivos .= "<strong>{$archivo->getTitulo()}</strong>";
            $listaArchivos .= "</li>";
        }

        $email = (new Email())
            ->from('vazquez.cres.asoc@gmail.com')
            ->to($cliente->getEmail())
            ->subject($subject)
            ->html("
                <div style='background:#f4f4f4; padding:30px;'>
                    <div style='
                        background:#fff; 
                        max-width:600px; 
                        margin:0 auto; 
                        padding:30px; 
                        border-radius:8px; 
                        box-shadow:0 2px 10px rgba(0,0,0,0.1);
                        font-family: Arial, sans-serif;
                    '>
                        <h3 style='color:#333; margin-bottom:20px;'>Nuevos Archivos subidos para usted.</h3>
                        
                        <p style='color:#555; font-size:16px; line-height:1.5;'>
                          {$mensajeIntro}
                        </p>
                        
                        <ul style='
                            background:#f8f9fa; 
                            padding:20px; 
                            border-left:4px solid #1a73e8; 
                            list-style:none; 
                            margin:20px 0;
                        '>
                            {$listaArchivos}
                        </ul>
                        
                        <p style='color:#555; font-size:16px; line-height:1.5;'>
                            " . ($cantidadArchivos === 1 ? 
                                'Puede acceder a sus archivos' : 
                                'Puede acceder a sus archivos' 
                            ) . " haciendo clic en el siguiente enlace:
                        </p>
                        
                        <div style='text-align:center; margin:30px 0;'>
                            <a href='{$this->appUrl}/login' 
                               target='_blank' 
                               style='
                                   display:inline-block; 
                                   background:#1a73e8; 
                                   color:#fff; 
                                   padding:12px 25px; 
                                   text-decoration:none; 
                                   border-radius:5px;
                                   font-weight:bold;
                               '>
                                " . ($cantidadArchivos === 1 ? 'Iniciar sesión aquí' : 'Iniciar sesión aquí') . "
                            </a>
                        </div>
                        
                        <div style='text-align:center; margin-top:20px;'>
                            <img src='cid:firma' alt='Firma' style='width:100%; max-width:600px; height:auto;'>
                        </div>
                    </div>
                </div>
            ")
            ->embedFromPath($this->projectDir.'/public/img/firma.jpeg', 'firma', 'image/jpeg');

        $this->mailer->send($email);
    }
}
