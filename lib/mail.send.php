<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors" , 1);
//
//require_once("class.phpmailer.php");

function sendMail($to,$subject,$message,$support="Support",$attachment,$path='' , $date_field)
{
	$mail = new PHPMailer();
	
	$body = $message;
	
	$mail->IsSMTP(); // telling the class to use SMTP
	//$mail->Host       = "smtp.gmail.com"; // SMTP server
	$mail->Host       = "220.227.249.163"; // SMTP server
	$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
											   // 1 = errors and messages
											   // 2 = messages only
	//$mail->SMTPAuth   = true;                  // enable SMTP authentication
	//$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
	//$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
	$mail->Host       = "220.227.249.163";      // sets GMAIL as the SMTP server
	$mail->Port       = 25;                   // set the SMTP port for the GMAIL server
	//$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
	//$mail->Username   = "eyepep2010@gmail.com";  // GMAIL username
	//$mail->Password   = "#eyepep2010$";            // GMAIL password
	$mail->Username   = "emrreports@lvpei.org";  // GMAIL username
	$mail->Password   = "emrreports";            // GMAIL password
	//$mail->AddReplyTo('honavar@lvpei.org', 'honavar@lvpei.org');
	//$mail->SetFrom('honavar@lvpei.org', 'honavar@lvpei.org');
	
	$mail->Subject = $subject;

	$mail->MsgHTML($body);

	$find = strpos($to,',');
	if($find)
	{
		$ids = explode(',',$to);
		for($i=0;$i<count($ids);$i++)
		{
			$mail->AddAddress($ids[$i]);
		}
	}
	else
	{
		$mail->AddAddress($to);
	}
	
	//print_r($mail);
 	if($mail->Send())
	{
		//echo 'success';
		return 1;	
	}
	else
	{
		//echo $mail->ErrorInfo;
		return 0;	
	}
}
?>