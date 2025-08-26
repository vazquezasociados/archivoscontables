<?php

namespace App\Service;

use App\Entity\Archivo;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    private string $appUrl;

    public function __construct(private MailerInterface $mailer,string $appUrl)
    {
        $this->appUrl = $appUrl;
    }

    public function sendWelcomeEmail(string $to, string $nombre): void
    {
        // dd('Hola estoy aqui');
        $email = (new Email())
            ->from('vazquez.cres.asoc@gmail.com') 
            ->to($to)
            ->subject('Bienvenido a la plataforma')
            ->html("
                <h2>Hola {$nombre},</h2>
                <p>🎉 Bienvenido a nuestra plataforma.</p>
                <p>Ya puedes acceder con tus credenciales.</p>
                <a href='{$this->appUrl}/admin/archivo' target='_blank' style='color:#1a73e8;'>
                    iniciando sesión aquí
                </a>.
    
            ");
            // dd($email);
        $this->mailer->send($email);
    }
    
    public function sendArchivoNotification(Archivo $archivo): void
    {
        $cliente = $archivo->getUsuarioClienteAsignado();

        if (!$cliente || !$cliente->getEmail()) {
            return; // nada que notificar si no hay cliente asignado
        }

        $email = (new Email())
            ->from('vazquez.cres.asoc@gmail.com')
            ->to($cliente->getEmail())
            ->subject('Archivo nuevo subido para usted')
            ->html("
                // <h2>Hola {$cliente->getNombre()},</h2>
                <p>Se ha subido un nuevo archivo para ti.</p>
                <p><b>{$archivo->getTitulo()}</b></p>
                <p>
                Puede acceder a la lista de sus archivos 
                <a href='{$this->appUrl}/admin/archivo' target='_blank' style='color:#1a73e8;'>
                    iniciando sesión aquí
                </a>.
                </p>
            ");

        $this->mailer->send($email);
    }
}
