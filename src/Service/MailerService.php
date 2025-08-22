<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function sendWelcomeEmail(string $to, string $nombre): void
    {
        // dd('Hola estoy aqui');
        $email = (new Email())
            ->from('no-reply@tusitio.com') // cambia esto por tu remitente
            ->to($to)
            ->subject('Bienvenido a la plataforma')
            ->html("
                <h2>Hola {$nombre},</h2>
                <p>🎉 Bienvenido a nuestra plataforma.</p>
                <p>Ya puedes acceder con tus credenciales.</p>
            ");
            // dd($email);
        $this->mailer->send($email);
    }
}
