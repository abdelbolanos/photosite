<?php

class Mail{
	public $mail;

	/*
	* array(
	* 	'address' => to whom
	* 	'subject' => subject
	* 	'body'	  => email content as HTML
	*)
	*/
	public function __construct($params=array()){
		global $SUPPORT_EMAIL;
		global $SUPPORT_EMAIL_TITLE;
		
		$this->mail             = new PHPMailer();
		
		$this->mail->AddReplyTo($SUPPORT_EMAIL, $SUPPORT_EMAIL_TITLE);

		$this->mail->SetFrom($SUPPORT_EMAIL, $SUPPORT_EMAIL_TITLE);

		$this->mail->AddReplyTo($SUPPORT_EMAIL, $SUPPORT_EMAIL_TITLE);
		
		$this->mail->AddAddress($params['address']);

		$this->mail->Subject    = $params['subject'];

		$this->mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

		$this->mail->MsgHTML($params['body']);
	}

	public function send(){
		if(!$this->mail->Send()) {
			$result = "---ERROR--".date('Y-m-d H:i:s')."--MAIL--".PHP_EOL;
			$result .= "---SENDING MAIL---".PHP_EOL;
			$result .= "---ERROR --".$this->mail->ErrorInfo."-".PHP_EOL;
			$result .= "---END---".PHP_EOL;
			$result .= PHP_EOL;
			$f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."ERRORS_MAIL.txt", $result, FILE_APPEND);
		}
	}
	
}