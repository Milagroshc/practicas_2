<?php

namespace App\Mail;

class Message
{
    protected $mailer;
    
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }
    
    public function to($address)
    {
        $this->mailer->addAddress($address);   
    }
    public function subject($subject)
    {
        $this->mailer->Subject = $subject;
    }
    public function body($body)
    {
        $this->mailer->Body = $body;
    }
    public function from($from)     // if you want to add different sender email in mailer call.
    {
        $this->mailer->From = $from;
    }
    public function fromName($fromName) // if you want to add different sender name in mailer call.
    {
        $this->mailer->FromName = $fromName;
    }

    public function addReplyTo($address)
    {
        $this->mailer->addReplyTo($address);
    }
    public function addConCopia($address)
    {
        $this->mailer->addCC($address);
    }

    public function addConCopiaOculta($address)
    {
        $this->mailer->addBCC($address);
    }


}