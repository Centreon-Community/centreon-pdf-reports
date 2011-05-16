<?php


function mailer($from,$to,$subject,$body,$server, $files, $name){
require_once("lib/PHPMailer/class.phpmailer.php");

$mail = new PHPMailer();

$mail->IsSMTP();  // telling the class to use SMTP
$mail->Host     = $server; // SMTP server

$mail->From     = "$from";

if (count($to) > 1 ) {
  
  foreach ($to as $email) 
    $mail->AddBCC($email);
  
} else {
  
  $mail->AddAddress($to[0]);
}


//$mail->AddAddress($to);

//$mail->Subject  = "Rapport de supervision de FAN pour SOlution Linux 2011";
$mail->Subject  = $subject;
$mail->Body     = $body;
$mail->WordWrap = 50;


      foreach ($files as $key => $file ) { 

	    if (file_exists($file["url"])) {

	    $_fileType  = filetype($file["url"]);
	    $_fileContent = file_get_contents($file["url"]);
	      if (!$_fileContent) {
		die('Impossible de lire le fichier ' . $file["url"]);
	      }

	      $mail->AddAttachment($file["url"]);	    

	    }
    }


  if(!$mail->Send()) {
    echo "Report '$name' for ".  implode(", ",$to) ." was not sent\n";
    echo 'Mailer error: ' . $mail->ErrorInfo;
  } else {
    echo "Report '$name' for ".  implode(", ",$to) ." has been sent\n";
  }

}


   function mailPdf($from,$to,$subject, $files) {  

$_boundary = md5(uniqid(microtime(), TRUE));
$_headers  = 'From: '.$from."\r\n";
$_headers .= 'Mime-Version: 1.0' . "\r\n" .
	     'Content-type: multipart/mixed; boundary=' . $_boundary . "\r\n";
$_to	=	$to;
$_subject =	$subject;
//message html
$_message  = '--' . $_boundary . "\r\n" .
	     'Content-type: text/plain; charset=UTF-8' . "\r\n";
$_message .= 'Voici le rapport de supervision de la semaine dernière.';
$_message .= "\r\n";

//PJ
   

      foreach ($files as $key => $file ) { 

	    if (file_exists($file["url"])) {

	    $_fileType  = filetype($file["url"]);
	    $_fileContent = file_get_contents($file["url"]);
	      if (!$_fileContent) {
		die('Impossible de lire le fichier ' . $file["url"]);
	      }

	    $_fileContent = chunk_split(base64_encode($_fileContent));


	    $_message .= '--' .$_boundary . "\r\n" .
			'Content-type: application/pdf' . '; name=' . $key .
			  "\r\n"  .
			'Content-transfer-encoding: base64' . "\r\n";
	    $_message .= $_fileContent . "\r\n";
	    

	    }

      $_message .= '--' . $_boundary . "\r\n";

      

    }

  mail($_to,$_subject,$_message,$_headers);

}
      

?>
