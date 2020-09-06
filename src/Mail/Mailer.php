<?php

namespace App\Mail;
use App\Mail\Mailer;
use App\Mail\Message;
use Slim\Views\Twig;
use PHPMailer\PHPMailer\PHPMailer;
class Mailer
{
	protected $view;
	
	protected $mailer;
	
	public function __construct(Twig $view,PHPMailer $mailer )
	{
		$this->view = $view;
		$this->mailer = $mailer;
	}
	
   static public  function send($view, $mailer, $callback)
	{
		$mailer->isSMTP();
		$mailer->SMTPDebug = 2;

		$message = new Message($mailer);
		
		$message->body($view);
		
		call_user_func($callback, $message);
		
		if (!$mailer->send()) {
			return "Mailer Error: " . $mailer->ErrorInfo;
		} else {
			return "Message sent!";
		}
	}

  static  public function sendEmail($to_email, $to_name, $subject, $message, $template_name='')
  {
	//Set who the message is to be sent to
	var_dump($this->mailer);
	exit;
	$this->mailer->addAddress($to_email, $to_name);
	//Set the subject line
	$this->mailer->Subject = $subject;
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$this->mail->msgHTML(file_get_contents($template_name));
	$this->mailer->msgHTML("<h1>Hola mundo</h1>");
	//Replace the plain text body with one created manually
	$this->mailer->AltBody = $message;
	//send the message, check for errors
	if (!$this->mailer->send()) {
		echo "Mailer Error: " . $this->mailer->ErrorInfo;
	} else {
		echo "Message sent!";
	}
  }

  static  public function enviarEmail($mailer,$data)
  {
  	$mailer->isMail();
	$mailer->isSMTP();
	$mailer->IsHTML(true);
	$mailer->Host = 'smtp-relay.gmail.com';
	$mailer->SMTPAuth = false;
	$mailer->SMTPAutoTLS = false;
	$mailer->Port = 25;
	$mailer->CharSet = 'UTF-8';
	// $mailer->SMTPDebug = 2;
	$mailer->Username = 'noreply';
	$mailer->Password = 'nr*lima0619';
	if ($data["adjuntar"]) {
		$mailer->addAttachment($data["ruta_documento"]);
	}
	$message = new Message($mailer);
	$message->to($data["para"]);
	$message->subject($data["asunto"]);
	$message->from("noreply@munlima.gob.pe");
	$message->fromName("Municipalidad Metropolitana de Lima");
	if ($data["copia"]) {
        $message->addConCopiaOculta($data["copiar_a"]);
    }
	$message->body($data["cuerpo"]);

	if (!$mailer->send()) {
		// echo "Mailer Error: " . $mailer->ErrorInfo;
		$success = false;
	} else {
		// echo "Message sent!";
		$success = true;
	}
	return $success;
  }

}