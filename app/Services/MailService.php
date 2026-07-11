<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envía correos electrónicos transaccionales.
 *
 * Lee la configuración desde variables de entorno en el momento de
 * construcción; cambiar la configuración no requiere tocar el código.
 */
final class MailService
{
    public function __construct(
        private string $fromAddress = 'noreply@dashboard.com',
        private string $fromName = 'CMS-BUILDER',
        private string $timezone = 'America/Bogota',
    ) {
    }

    public static function fromEnv(): self
    {
        return new self(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@dashboard.com',
            $_ENV['MAIL_FROM_NAME'] ?? 'CMS-BUILDER',
            $_ENV['APP_TIMEZONE'] ?? 'America/Bogota',
        );
    }

    public function send(
        string $subject,
        string $to,
        string $title,
        string $message,
        string $link,
    ): bool|string {
        date_default_timezone_set($this->timezone);

        $mail = new PHPMailer();
        $mail->CharSet = 'utf-8';
        $mail->isMail();
        $mail->UseSendmailOptions = false;
        $mail->setFrom($this->fromAddress, $this->fromName);
        $mail->Subject = $subject;
        $mail->addAddress($to);
        $mail->msgHTML($this->buildBody($title, $message, $link));

        if (!$mail->Send()) {
            return $mail->ErrorInfo;
        }

        return 'ok';
    }

    private function buildBody(string $title, string $message, string $link): string
    {
        return <<<HTML

			<div style="width:100%; background:#eee; position:relative; font-family:sans-serif; padding-top:40px; padding-bottom: 40px;">

				<div style="position:relative; margin:auto; width:600px; background:white; padding:20px">

					<center>

						<h3 style="font-weight:100; color:#999">{$title}</h3>

						<hr style="border:1px solid #ccc; width:80%">

						{$message}

						<a href="{$link}" target="_blank" style="text-decoration: none; mrgin-top:10px">

							<div style="line-height:25px; background:#000; width:60%; padding:10px; color:white; border-radius:5px">Haz clic aquí</div>

						</a>

						<hr style="border:1px solid #ccc; width:80%">

						<h5 style="font-weight:100; color:#999">Si no solicitó el envío de este correo, haga caso omiso de este mensaje.</h5>

					</center>

				</div>

			</div>

HTML;
    }
}
