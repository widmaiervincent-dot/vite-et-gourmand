<?php
// envoi des mails avec PHPMailer (installé avec composer)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

function envoyerMail($destinataire, $sujet, $contenuHtml, $repondreA = null)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->CharSet = 'UTF-8';
        // si le serveur de mail ne répond pas, on n'attend pas 5 minutes (valeur par défaut)
        $mail->Timeout = 10;

        $mail->setFrom(MAIL_ENTREPRISE, 'Vite & Gourmand');
        $mail->addAddress($destinataire);
        if ($repondreA) {
            $mail->addReplyTo($repondreA);
        }

        $mail->isHTML(true);
        $mail->Subject = $sujet;
        $mail->Body = $contenuHtml;
        // version texte : un retour à la ligne après chaque paragraphe (sinon les liens sont collés au texte)
        $mail->AltBody = trim(strip_tags(preg_replace('#</(p|h1|h2)>|<br ?/?>#i', "\n", $contenuHtml)));

        $mail->send();
        return true;
    } catch (Exception $e) {
        // si le mail ne part pas on ne bloque pas le site, on note juste l'erreur
        error_log('Erreur mail : ' . $mail->ErrorInfo);
        return false;
    }
}
