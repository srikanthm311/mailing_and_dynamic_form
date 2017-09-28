 <?php 
 error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors" , 1);
class Main
{
	public $total_records;
	public $result_set;
	public $rows_result;
	public $s;
	public $n;
	
	public $user_id;
	public $user_fname;
	public $user_lname;
	public $user_mobile;
	public $user_dob;
	public $user_org_id;
	public $user_org_name;
	public $user_email;
	public $user_role;
	public $user_gender;
	public $user_address;
	public $user_country;
	public $user_password;
	public $user_varifiacation;
	public $user_approval;
	public $user_status;
	public $ipaddress;
	public $establish_date;
	
	public $step_id;
	public $step_details= array();
	
	public $feedback_user_id;
	public $feedback;
	
	public $data= array();
	public $playlist = array();
	public $forget_key;
	public $created_date;
	public $created_time;
	public $newpassword;
	public $old_password;
	

	
	public $userData = array();
	public $err_msg;
	public $success_msg;
		
	public  function __construct()
	{
		
		/*
			-Database connection
		*/
		//$dbconnect = new dbConnection();
		//$this->err_msg = $dbconnect->dbConnect_err;
	}
	public function getOrganization($org)
	{
		//echo $org;
		  $sql = "select tom_organization_id from tbl_organizations_master WHERE tom_organization_name ='$org'";
		$result = mysql_query($sql);
		$is_exits =mysql_num_rows($result);
		//echo count($result);
		if($is_exits >0)
		{
		    return 1;
		}
		else
		{
		   return 0;	
		}
	}
	
	/*
		- To Register user
		- To use Registration form
		- Tables used tbl_user
		- Input : Form date given by User.
		- Output : registration success or fail. 
	*/
	
	public function user_registration()
	{
		date_default_timezone_set("Asia/Bangkok");
		 $sql="INSERT into tbl_organizations_master SET
		      tom_organization_name= '$this->user_org_name',
			  tom_location= '$this->user_address',
			  tom_ipaddress= '$this->ipaddress',
			  tom_status='ACTIVE',
			  tom_created_date= CURRENT_DATE(),
			  tom_country_id = '$this->user_country',
			  tom_created_time= TIME(NOW())
			  ";
		$result = mysql_query($sql);
		$id = mysql_insert_id();
	
		$sql = "
			INSERT INTO tbl_users SET
			tu_organization_id = '$id',
			tu_first_name = '$this->user_fname',
			tu_last_name = '$this->user_lname',
			tu_email = '$this->user_email',
			tu_password = '$this->user_password',
			tu_mobile = '$this->user_mobile',
			tu_date_of_birth = '".date('Y-m-d',strtotime($this->user_dob))."',
			tu_address = '$this->user_address',
			tu_country = '$this->user_country',
			tu_role='$this->user_role',
			tu_gender = '$this->user_gender',
			tu_ipaddress = '$this->ipaddress',
			tu_status = 'ACTIVE',
			tu_created_date = CURRENT_DATE(),
			tu_created_time = TIME(NOW())
		";
		//echo $sql; exit;
		$result = mysql_query($sql);
		
		
		
		if($result)
		{
			$this->err_msg = 'Registered successfully';
			return true;
		}
		else
		{
			$this->err_msg = 'something went wrong '. mysql_error();
			return false;
		}
	}
	
	
	/*
		- TO check weither user exist or Not
		- To used in the singup form
		- To table used tbl_users
	
	*/
	public function userExists()
	{
		$sql = "SELECT tu_user_id FROM tbl_users WHERE tu_email = '$this->user_email' LIMIT 1";
		$result = mysql_query($sql) or die(mysql_error());
		if(mysql_num_rows($result) > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/*
		- To valid first login of the user 
		- TO uses user login form
		- uses tbl_users table
		- inputs: username and password.
		- output : rerurns user detsils if valid user 
		- or return false in valid user
	
	*/
	public function firstLogin()
	{
		$sql = "SELECT tu_user_id FROM tbl_users WHERE tu_email = '$this->user_email' AND tu_password = '$this->user_password' LIMIT 1";
		//echo $sql; exit;
		$result = mysql_query($sql) or die(mysql_error());
		if(mysql_num_rows($result)>0)
		{
		$fetch = mysql_fetch_assoc($result);
		//echo '<pre>'; print_r($fetch);exit;
		$this->user_id=$fetch['tu_user_id'];
		return true;
		}
		else
		{
			return false;

		}
	}
	
	
	public function organizationExists()
	{
		$sql = "SELECT tom_organization_id FROM tbl_organizations_master WHERE tom_organization_name = '$this->user_org_name' LIMIT 1";
		$result = mysql_query($sql) or die(mysql_error());
		if(mysql_num_rows($result) > 0)
		{
			return false;	
		}
		else
		{
			return true;
		}
	}
	
	
	/*
		- To valid login trainne
		- TO uses user login form
		- uses tbl_users table
		- inputs: username and password.
		- output : rerurns user detsils if valid user 
		- or return false in valid user
	
	*/
	
	public function userLogin()
	{
		$sql = "
			SELECT * FROM tbl_users 
			
			WHERE 
			tu_email = '$this->user_email' AND 
			tu_password = '$this->user_password' AND 
			tu_status = 'ACTIVE'
			
			LIMIT 1
			";
			/*echo $sql;
			exit;*/
		$resset = mysql_query($sql);
		
		if(mysql_num_rows($resset) > 0)
		{
			$this->userData = mysql_fetch_assoc($resset);
			if(!$this->checkVerifyuser($this->userData['tu_user_id']))
			{
				$this->err_msg = 2;
				return false;
			}
			else{
			
				
			//echo '<pre>'; print_r($this->userData);
			$sql = "INSERT INTO tbl_login_session_log SET
					
					tlsl_user_id = ".$this->userData['tu_user_id'].",
					tlsl_session_type = 'LOGIN',
					tlsl_session_date = CURRENT_DATE(),
					tlsl_session_time = TIME(NOW()),
					tlsl_ipaddress = '".$this->get_client_ip()."'
					";
			//echo $sql; exit;
			$result = mysql_query($sql);
			
			return true;
		}
		}
		else
		{
			$this->err_msg = 0;
			return false;
		}
	}
	
	public function logout()
	{
		$sql = "INSERT INTO tbl_login_session_log SET
				
				tlsl_user_id = ".$this->user_id.",
				tlsl_session_type = 'LOGOUT',
				tlsl_session_date = CURRENT_DATE(),
				tlsl_session_time = TIME(NOW()),
				tlsl_ipaddress = '".$this->get_client_ip()."'
				";
		//echo $sql; exit;
		$result = mysql_query($sql);
		if($result)
		{
			return true;
		}
	}
	
	/*
		Start of update password function
		updates the existing password with new password
	*/
	public function changePassword()
	{
		
		$sql = "UPDATE tbl_users SET
				tu_password = '$this->newpassword',
				tu_updated_date = CURRENT_DATE(),
				tu_updated_time = TIME(NOW()),
				tu_ipaddress = '$this->ip_address'
				
				WHERE tu_user_id = $this->user_id
				";
				
			if(isset($this->old_password))
			{
				$sql .= " AND tu_password = '".$this->old_password."'";
			}	
		//echo $sql; exit;
		$resset = mysql_query($sql);
		if(mysql_affected_rows())
		{
			return true;
		}
		else
		{
			$this->error = mysql_error();
			return false;
		}
	}
	//End of update password function
	
	/*
		-To check weither account varified not not
		-To uses table tbl_users.
		-input user id.
		-Output true or false.
	
	*/
	
	public function checkVerifyuser($user_id = '')
	{
		//echo $user_id;
		$sql = "SELECT tu_approval_status FROM tbl_users WHERE tu_user_id = $user_id";
		// AND tu_varification_status = 'VERIFIED'
		//echo $sql; exit;
		$resset = mysql_query($sql) or die (mysql_error());
		
		$fetch = mysql_fetch_array($resset);
		if($fetch['tu_approval_status'] != 'APPROVED')
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/*
		To uses in forgot password
		uses tbl_users 
		inputs : Email ID, dateofbirth,
		output : send the mail to user Email ID if provided valid details
		
	*/
	
	public function forgetPassword()
	{
		require_once("../../lib/class.phpmailer.php");
		//require('../../PHPMailer/PHPMailer-master/class.phpmailer.php');
		require_once("../../lib/mail.send.php");
		$sql = "SELECT * FROM tbl_users WHERE tu_email = '$this->user_email' AND tu_date_of_birth = '".$this->user_dob."' LIMIT 1";
		
		echo $sql;
		$resset = mysql_query($sql)or die(mysql_error());
		echo mysql_num_rows($resset);
		if(mysql_num_rows($resset) > 0)
		{
			$fetch = mysql_fetch_assoc($resset);
			$key = strtoupper(substr($fetch['tu_first_name'],0,5)) .'_'.substr($fetch['tu_mobile'],0,5).'_'.strtoupper(substr($fetch['tu_email'],2,5)).$fetch['tu_user_id'];
			
			
			$this->userData['fromid'] = $fetch['tu_email'];
			$this->userData['name'] = $fetch['tu_first_name'].' '.$fetch['tu_last_name'];
			$this->userData['mobile'] = $fetch['tu_mobile'];
			
			/*echo '<pre>'; print_r($fetch); exit;
			echo $key; exit;*/
			
			
			/*require('../../PHPMailer/PHPMailer-master/class.phpmailer.php');
			$mail=new PHPMailer();
			$mail->CharSet = 'UTF-8';
			$fromid = $fetch['tu_email'];
			$name = $fetch['tu_first_name'].' '.$fetch['tu_last_name'];
			$mobile = $fetch['tu_mobile'];
			
			
			
			//From email address and name
			$mail->From = 'msrikanth@lvpei.org';
			$mail->FromName = 'Oscar';
			//To address and name
			///$mail->addAddress("msrikanth@lvpei.org");
			$mail->addAddress($fromid);
			//CC and BCC
			$mail->addCC("srikanthm311@gmail.com");
			//Send HTML or Plain Text email
			$mail->isHTML(true);
			$mail->Subject = 'OSCAR Password Reset';
			$mail->Body = "<i>OSCAR Password Reset:</i><br> Name: ".$name.'<br>Email-id: '.$fromid.'<br>Mobile: '.$mobile;
			$mail->Body .= "<br> Reset Link: <a href='oscar/change_password.php?user=".$fetch['tu_user_id']."'>please Click here</a>";*/
			//echo '<pre>'; print_r($mail); exit;
			
			$message = '';
			$message = '<div class="m30tb" id="div_for_tool_tip">';
			$message.= '<table width="50%" border="1" cellspacing="1" cellpadding="5" class="dataGrid3">';
			$message.= '<tr><td colspan="2"  style="background-color:#09F;font-weight:bold;color:#FFF;" align="left">OSCAR Password Reset</td></tr>';
			$message.= '<tr><td><strong>  Name </strong></td><td  align="left">'.$name.'</td></tr>';
			$message.= '<tr><td><strong>  Email ID</strong></td><td  align="left">'.$fromid.'</td></tr>';
			$message.= '<tr><td><strong>  Mobile </strong></td><td  align="left">'.$mobile.'</td></tr>';
			$message.= '<tr><td colspan="2"  style="background-color:#09F;font-weight:bold;color:#FFF;" align="left"><a href="oscar/change_password.php?user="'.$fetch["tu_user_id"].'">please Click here</a></td></tr>';
			$message.= '</table>';
			$message .= '</div>';
			echo $message;
			if(1)
			{
				
				echo 'if loop';
				//$to="ranganath@lvpei.org,vipin@lvpei.org,aramam@lvpei.org,gnrao@lvpei.org,rohit@lvpei.org,shekar@lvpei.org,uday@lvpei.org , niranjankumar@lvpei.org , nagavardhan@lvpei.org ,sri.marmamula@lvpei.org,sapthagiri@lvpei.org,varsharathi@lvpei.org,gcs@lvpei.org,tpd@lvpei.org,ashalatha@lvpei.org ";
				// 	$to="ranganath@lvpei.org";
				$to="msrikanth@lvpei.org";
				
				//$date = date("d-m-Y" , strtotime($date_field));
			  	
				$subject = " OSCAR Password Reset";
				
				$body = $message;
				
				$body .= " Note : This link is valid onluy for 30 minuts<br>";
				
				$body .= "<br /><br /><br />Team EMR<br />L.V.Prasad Eye Institute<br />Hyderabad";
				
				$result = sendMail($to ,$subject ,$body ,'' ,'', '', '');
			 
			}
			else
			{
				
				echo 'else loop';
				//sendMail($to='ranganath@lvpei.org,yasaswi@lvpei.org,mahendra@lvpei.org,vipin@lvpei.org' ,$subject='No VVC Report available' ,'No VVC Report available' ,'' ,'', '' , '');
				//sendMail($to='ranganath@lvpei.org,vipin@lvpei.org' ,$subject='No VVC Report available' ,'No VVC Report available' ,'' ,'', '' , '');
				$result = sendMail($to='msrikanth@lvpei.org' ,$subject='No VVC Report available' ,'No VVC Report available' ,'' ,'', '' , '');
			}
			
			if(!$result) 
			{
				 return 0;
			} 
			else 
			{
				
				$sql = "INSERT INTO tbl_forget_password_logs SET
				
					tfpl_user_id = ".$fetch['tu_user_id'].",
					tfpl_key = '".$key."',
					tfpl_created_date = CURRENT_DATE(),
					tfpl_created_time = TIME(NOW())
				";
				
				$resset1 = mysql_query($sql);
				if($resset1)
				{
					return true;
				}
			}
		}
		else
		{
			//echo 'error'.mysql_error();
			return 0;	
			return false;
		}
	}

	public function forgetPasswordKey()
	{
		$sql = "INSERT INTO tbl_forget_password_logs SET
				
					tfpl_user_id = ".$this->user_id.",
					tfpl_key = '".$this->forget_key."',
					tfpl_created_date = CURRENT_DATE(),
					tfpl_created_time = TIME(NOW())
				";
		//echo $sql; exit;
		$resset1 = mysql_query($sql);
		if($resset1)
		{
			return 1;
		}
		else
		{
			return 0;	
		}
	}
	
	
	public function verifyForgetKey()
	{
		$sql = "SELECT * FROM tbl_forget_password_logs 
		WHERE 
		
		tfpl_user_id = $this->user_id 
		AND tfpl_key='$this->forget_key' 
		AND tfpl_created_date = CURRENT_DATE AND (TIME_TO_SEC(timediff(CURRENT_TIME,tfpl_created_time))/60) <= 30";
		//echo $sql; exit;
		$resset = mysql_query($sql)or die(mysql_error());
		if(mysql_num_rows($resset) > 0)
		{
			return true;	
		}
		else
		{
			return false;	
		}
		
		//echo $sql; exit; 
	}
	
	/*
		To get the list of organizations
		uses tbl_organizations_master 
	*/
	
	public function getOrganizations()
	{
		$sql = "SELECT 
		tom_organization_name AS organization_name,
		tom_organization_id AS organization_id,
		tom_location AS location 
		
		FROM 
		
		tbl_organizations_master 
		
		WHERE tom_status = 'ACTIVE'";
		$resset = mysql_query($sql);
		if($resset)
		{
			while($fetch = mysql_fetch_assoc($resset))
			{
				$this->data['organizations'][] = $fetch;
			}
			return true;
		}
		else
		{
			$this->err_msg = "something went wrong". mysql_error();
			return false;
		}
	}
	
	
	/*
		To get the list of Countries
		uses tbl_countries 
	*/
	public function getCountries()
	{
		$sql = "SELECT * FROM tbl_countries WHERE 1";
		$resset = mysql_query($sql);
		if($resset)
		{
			while($fetch = mysql_fetch_assoc($resset))
			{
				$this->data['countries'][] = $fetch;
			}
			return true;
		}
		else
		{
			$this->err_msg = "something went wrong". mysql_error();
			return false;
		}
			
	}
	
	/*
		To get the list of procedure steps
		uses tbl_procedure_steps_master
		usedd in the oscar creation 
	*/
	
	public function getStepDetails()
	{
		$sql = "SELECT * FROM tbl_procedure_steps_master WHERE tpsm_step_id = $this->step_id LIMIT 1";
		
		$resset = mysql_query($sql);
		if($resset)
		{
			$fetch = mysql_fetch_assoc($resset);
			$this->step_details = array('stepID'=>$fetch['tpsm_step_id'],'stepName'=>$fetch['tpsm_step_name'],'stepCreatedAt'=>$fetch['tpsm_created_date']);
			return true;
		}
		else
		{
			$this->err_msg = "something went wrong". mysql_error();
			return false;
		}
	}
	
	
	/*
		To get the list of procedure
		uses tbl_procedures_master
		usedd in the Header
	*/
	
	public function headerContent()
	{
		$sql = " SELECT * FROM tbl_procedures_master WHERE tpm_status = 'ACTIVE'";
		$resset = mysql_query($sql);
		if($resset)
		{
			while($fetch = mysql_fetch_assoc($resset))
			{
				$this->data['surgeries'][] = $fetch;
			}
			return true;
		}
		else
		{
			$this->err_msg = "something went wrong". mysql_error();
			return false;
		}
	}
	
	
	/*
		To get the active user data
		uses tbl_users
	*/
	
	public function getUserData()
	{
		$sql = "
			SELECT 
			
			user.tu_first_name AS first_name ,
			user.tu_last_name AS last_name,
			user.tu_address AS address,
			tom_organization_name AS organization_name
			
			FROM tbl_users user
			
			JOIN tbl_organizations_master ON tom_organization_id = user.tu_organization_id
			
			WHERE tu_user_id = $this->user_id
			
			LIMIT 1
		";
		$resset = mysql_query($sql);
		if($resset)
		{
			while($fetch = mysql_fetch_assoc($resset))
			{
				$this->userData = $fetch;
			}
			return true;
		}
		else
		{
			$this->err_msg = "something went wrong". mysql_error();
			return false;
		}
	}
	
	
	public function getForgotPasswordUserData()
	{
		$sql = "
			SELECT  *
			
			FROM tbl_users
			
			WHERE tu_email = '$this->user_email' AND tu_date_of_birth = '".$this->user_dob."'
			
			LIMIT 1
		
		";
		//echo $sql; exit;
		$resset = mysql_query($sql) or die(mysql_error());
		
		if(mysql_num_rows($resset)>0)
		{
			while($fetch = mysql_fetch_assoc($resset))
			{
				$this->userData = $fetch;
				$newpassword = 'Oscar#'.date('Y',strtotime($this->userData['tu_date_of_birth']));
				$this->newpassword = $newpassword;
				//echo $this->newpassword; exit;
				
				$sql ="UPDATE tbl_users SET
				tu_password = '".$this->newpassword."'
				
				WHERE tu_user_id = ".$fetch['tu_user_id']." AND tu_email = '$this->user_email'
				";
				//echo $sql; exit;
				$resset = mysql_query($sql) or die(mysql_error());
				if(mysql_affected_rows())
				{
					return 1;
				}
				/*else
				{
					return 'mail sent';	
				}*/
				//echo '<pre>'; print_r($this->userData); exit;
			}
			return 1;
		}
		else
		{
			//$this->err_msg = "something went wrong". mysql_error();
			return 0;
		}
	}
	
	
	/*
		To check and insert the feedbacks
		uses tbl_feedback
		input : user id , feedback,
		output : success message or fail if feedback maximum limit exceeds.
	*/
	
	public function feedback()
		{
			$sql = "
					SELECT COUNT(tf_feedbackId) AS feedback_count 
					
					FROM tbl_feedback
					
					WHERE tf_user_id = $this->feedback_user_id AND tf_created_date = CURRENT_DATE()
					
			";
			
			$resset = mysql_query($sql) or die(mysql_error());
			$fetch = mysql_fetch_assoc($resset);
			
			if
			($fetch['feedback_count'] < 5)
			{ 
			$sql = "
					INSERT INTO tbl_feedback SET 
					
					tf_user_id = $this->feedback_user_id,
					tf_feedback = '$this->feedback',
					tf_created_date = CURRENT_DATE(),
					tf_created_time = TIME(NOW()),
					tf_ipaddress = '".$this->get_client_ip()."'";
			//echo $sql; exit;
			$resset = mysql_query($sql);
			if($resset)
			{
				return 1;
			}
			else
			{
				$this->err_msg = mysql_error();
				return false;	
			}
		}
			return 2;
		}
	/*
	
		- To get required data to display the palylist in the Trainee module
		- To see videos wich are added by superadmin and admin.
		- To uses tables : tbl_playlist
		- input : organization id
		- To output : data for playlist
	*/
	
	public function getPlaylist()
	{
		if($this->total_records == 1) //For total records
		{
			$limit = "";
			$fields= "tp.tp_playlist_id ";
		}
		else//GRID
		{
			if($this->s == -1 && $this->n == -1)
			$limit = "";
			else
			$limit = " LIMIT ".$this->s.",".$this->n;
			
			$fields = " tp.*, tpm.tpm_procedure_name AS surgery ";
		}
		
		
		$sql = "SELECT "; 
		$sql.= $fields;
		
		
		$sql .= "FROM tbl_playlist tp 
				
				INNER JOIN tbl_procedures_master tpm ON tpm.tpm_procedure_id = tp.tp_procedure_id
				
				WHERE tp.tp_status = 'ACTIVE' AND tp_accessibility = 'PUBLIC'
		";
		
		if($this->user_org_id != '')
		{
			$sql .= " AND tp.tp_organization_id = $this->user_org_id";
		}
		
		$sql .= $limit;
		//echo $sql; exit;
		$resset = mysql_query($sql);
		
		if($resset)
		{
			$totalresult = mysql_num_rows($resset);
			
			if($this->total_records == 1) //For total records of pagination check
			{
				$this->rows_result = $totalresult;
			}
			elseif($this->total_records == 0)
			{
	
				while($fetch = mysql_fetch_assoc($resset))
				{
					$this->playlist[] = $fetch;
				}
				//exit;
				return true;
			}
		}
		else
		{
			$this->err_msg = mysql_error();
			return false;	
		}
	}
	
	public function get_client_ip() 
	{
    $this->ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $this->ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $this->ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $this->ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $this->ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $this->ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $this->ipaddress = getenv('REMOTE_ADDR');
    else
        $this->ipaddress = 'UNKNOWN';
    return $this->ipaddress;
	}
	
}


?>