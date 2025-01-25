<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


/**
 * Classe pour envoyer des e-mails de vérification.
 */
class EmailSender {
    
    /**
     * Envoie un e-mail de vérification.
     *
     * Cette fonction utilise la bibliothèque PHPMailer pour envoyer un e-mail de vérification via un serveur SMTP.
     *
     * @param string $email L'adresse e-mail du destinataire.
     * @param string $subject Le sujet de l'e-mail.
     * @param string $message Le contenu de l'e-mail.
     * @return void
     */
    public static function sendVerificationEmail($email, $subject, $message) {
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';
        require 'PHPMailer/src/Exception.php';

        $mail = new PHPMailer(true);

        try {
            // Paramètres du serveur SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pandartist0@gmail.com'; //Mail d'envoie
            $mail->Password = 'fquqwvfykkcptzqd'; // Mdp à ne pas partager
            $mail->SMTPSecure = 'ssl'; 
            $mail->Port = 465; 

            // Expéditeur et destinataire
            $mail->setFrom('pandartist0@gmail.com', 'Perform vision');
            $mail->addAddress($email);

            // Contenu de l'e-mail
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Envoyer l'e-mail
            $mail->send();
        } catch (Exception $e) {
            // Gérer les erreurs d'envoi d'e-mail
            echo '\n EmailSender: Erreur lors de l\'envoi de l\'e-mail : ', $mail->ErrorInfo ,'\n';
        }
    }
}
