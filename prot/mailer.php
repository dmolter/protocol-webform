<?php

/**
 * Script for sending email with attachment via Swift Mailer.
 *
 * @author Dennis Molter
 *
 * @todo removing the Swift Mailer dependency...mailing an 
 * attachment is the only functionality we need from this 
 */


require_once "../../swiftmailer/lib/swift_required.php";



class Mail {
    private $from;
    private $to;

    private $filePath;

    private $subject = "Arbeitsprotokoll";

    private $body = "<p>Sehr geehrter Kunde,<br> 
        anbei erhalten Sie das Arbeitsprotokoll für den ausgeführten 
        Auftrag in ihrem Hause.<br><br>Mit freundlichen Grüßen<br>
        Firma XYZ</p>";




    function __construct($email, $path) {
        $config = parse_ini_file('../config.ini');
        $this->from = $config['email_from'];
        $this->to = $email;
        $this->filePath = $path;
    }


    
    function sendMail() {

        $transport = Swift_MailTransport::newInstance();

        $mailer = Swift_Mailer::newInstance($transport);

        $message = Swift_Message::newInstance()
        ->setFrom(array($this->from))
        ->setTo(array($this->to))
        ->setEncoder(Swift_Encoding::get7BitEncoding())
        ->setSubject($this->subject)
        ->setBody($this->body, 'text/html')
        ->addPart(strip_tags($this->body), 'text/plain')
        ->attach(Swift_Attachment::fromPath($this->filePath))
        ;
        
        return $mailer->send($message);
    }  
    
}

