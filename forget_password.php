<?php 
//echo '<pre>'; print_r($_POST); exit;
require '../../classes/database/databaseConnection.php';
require_once("../../lib/class.phpmailer.php");
require_once("../../lib/mail.send.php");
require '../../classes/main/Main.php';
$obj = new Main();
$obj->user_email = mysql_real_escape_string(trim($_POST['email']));
$obj->user_dob = date('Y-m-d',strtotime($_POST['dob']));
$result = $obj->getForgotPasswordUserData();
//echo '<pre>'; print_r($obj->userData); exit;
//echo $result;exit;
if($result == 1)
{
	
	
			$key_username = substr(str_shuffle($obj->userData['tu_first_name']), 0, 5);
			$mobile = substr($obj->userData['tu_mobile'], 1);
			//echo trim($mobile); exit;
			$key_mobile = substr(str_shuffle($mobile), -4);
			$key_email = substr(str_shuffle($obj->userData['tu_email']), 2, 5);
	
			$key = htmlentities(($key_username.$key_email.$key_mobile));
			/*echo $key;  exit;*/
			
			$name = $obj->userData['tu_first_name'].' '. $obj->userData['tu_last_name'];
			$toid = mysql_real_escape_string($_POST['email']);
			$mobile = $obj->userData['tu_mobile'];
			$user_id = $obj->userData['tu_user_id'];
			
			
			$message = '';
			$message = '<div class="m30tb" id="div_for_tool_tip">';
			$message.= '<table width="50%" border="1" cellspacing="1" cellpadding="5" class="dataGrid3">';
			$message.= '<tr><td colspan="2"  style="background-color:#09F;font-weight:bold;color:#FFF;" align="left">OSCAR Password Reset</td></tr>';
			$message.= '<tr><td><strong>  Name </strong></td><td  align="left">'.$name.'</td></tr>';
			$message.= '<tr><td><strong>  Email ID</strong></td><td  align="left">'.$toid.'</td></tr>';
			$message.= '<tr><td><strong>  Mobile</strong></td><td  align="left">'.$mobile.'</td></tr>';
			$message.= '<tr><td><strong>  Password : </strong></td><td  align="left">'.$obj->newpassword.'</td></tr>';
			
			/*$message.= "<tr><td colspan='2'  style='background-color:#09F;font-weight:bold;color:#FFF;' align='left'>Please <a href='https://172.16.154.52/ico-oscar/executes/main/validateforgot_password.php?user=".$user_id."&key=".$key."'>Click here</a> to reset your password</td></tr>";*/
			$message.= '</table>';
			$message .= '</div>';
			//echo $message; exit;
			if(1)
			{
				
				//echo 'if loop';
				$to ='msrikanth@lvpei.org,satyanarayana@lvpei.org,'.$toid;
				
				$subject = " OSCAR Password Reset";
				
				$body = $message;
				
				$body .= " Note : Please do not reply to this mail";
				
				$body .= "<br /><br /><br />Support Team";
				
				$mailSet = sendMail($to ,$subject ,$body ,'' ,'', '', '');
				 //echo $mailSet.' mail'; exit;
				if($result)
				{
					$obj->user_id = $user_id;
					$obj->forget_key = $key;
					$result1 = $obj->forgetPasswordKey();
					header('location:../../index.php?msg=3');
					exit;
				}
				else
				{
					header('location:../../index.php?msg=4');
					exit;
				}
				
			 
			}
}
/*elseif($result == 'mail sent')
{
	header('location:../../index.php?msg=sent');
	exit;
}*/
else
{
	header('location:../../index.php?msg=4');
	exit;
}

?>