<style type="text/css" >
table{
	border:#000 solid 1px;

}
.dataGridHeader { background-color:#09C; color:white; font-size:13px; font-weight:bold; padding:5px 10px; -webkit-border-top-left-radius:6px; -webkit-border-top-right-radius:6px; -moz-border-radius-topleft:6px; -moz-border-radius-topright:6px; }
</style>
<?php

//error_reporting(E_ALL & ~E_NOTICE);
//ini_set("display_errors", 1);
mysql_connect('172.16.154.52','migration','Lvpapp2216') or die (mysql_error());
mysql_select_db('lvpeieyesmart') or die(mysql_error());

require_once("../../lib/date_field.php");
require_once("../../lib/mail/class.phpmailer.php");
require_once("../../lib/mail/mail.send.php");
require_once("../../lib/data/vvc_report.php");

$obj = new VVCReport();

// DATE RANGE FILTER FOR ALL GRIDS IN VISION MODEULE //
$obj->from_date = $obj->to_date = date("Y-m-d" , strtotime($date_field));
//$obj->from_date = '2017-04-01';
//$obj->to_date = '2017-04-20';

 // ENTIRE NETWROK //
$obj->center_code = '';

$daily_report = $obj->getDailyReport();
// INITIALZING MAIL BODY //
$message = '';
$message = '<div class="m30tb" id="div_for_tool_tip">';
$message.= '<table width="50%" border="1" cellspacing="1" cellpadding="5" class="dataGrid3">';
$message.= '<tr><td colspan="2"  style="background-color:#09F;font-weight:bold;color:#FFF;" align="left">VVC Report (Shorter Version)</td></tr>';
$message.= '<tr><td><strong>  Date </strong></td><td  align="left">'.date("d-m-Y").'</td></tr>';
$message.= '<tr><td><strong> No. of VCs </strong></td><td align="left">'.$daily_report[total_vcs].'</td></tr>';
$message.= '<tr><td><strong>No. of VCs working</td> <td align="left">'.$daily_report[active_vcs].'</td></tr>';
$message.= '<tr><td><strong>No. of Screenings</strong></td><td align="left">'.$daily_report[total_screening].'</td></tr>';
$message.= '<tr><td><strong>Glasses Prescribed</strong></td><td align="left">'.$daily_report[glasss_prescribed].'</td></tr>';
$message.= '<tr><td><strong>Glasses Booked</strong></td><td align="left">'.$daily_report[glasss_prescribed_dispensed].'</td></tr>';
$message.= '<tr><td><strong>Total referral</strong></td><td align="left">'.$daily_report[refer_to_sc].'</td></tr>';
$message.= '<tr><td><strong>Teleophthalmology - initiated</strong></td><td align="left">'.$daily_report[tele_count].'</td></tr>';
$message.= '<tr><td><strong>Teleophthalmology - completed</strong></td><td align="left">'.$daily_report[tele_completed].'</td></tr>';
$message.= '<tr><td><strong>Avg screenig per VC</strong></td><td align="left">'.$daily_report[avg_screening].'</td></tr>';
$message.= '<tr><td><strong>Avg precriptions per VC</strong></td><td align="left">'.$daily_report[avg_glasss_prescribed].'</td></tr>';
$message.= '<tr><td><strong>Avg booking  per VC</strong></td><td align="left">'.$daily_report[avg_prescribed_dispensed].'</td></tr>';
$message.= '<tr><td><strong>Avg referral per VC</strong></td><td align="left">'.$daily_report[avg_refer_to_sc].'</td> </tr>';
$message.= '<tr><td><strong>No. VC closed</strong></td><td align="left">'.$daily_report[inactive_vcs].'</td></tr>';
$message.= '</table>';
$message .= '</div>';

if(1)
{
	//$to="ranganath@lvpei.org,vipin@lvpei.org,aramam@lvpei.org,gnrao@lvpei.org,rohit@lvpei.org,shekar@lvpei.org,uday@lvpei.org , niranjankumar@lvpei.org , nagavardhan@lvpei.org ,sri.marmamula@lvpei.org,sapthagiri@lvpei.org,varsharathi@lvpei.org,gcs@lvpei.org,tpd@lvpei.org,ashalatha@lvpei.org ";
	// 	$to="ranganath@lvpei.org";
	$to="avenkatesh@lvpei.org";
  	
	$date = date("d-m-Y" , strtotime($date_field));
  
	$subject .= " VVC REPORT (Shorter Version)- ".$date;
	
	$body = "Dear All, <br /><br />Please find below VVC Report (Shorter Version) for ".$date."<br /><br />";
	
	$body .= $message;
	
	$body .= "<br /><br /><br />Team EMR<br />L.V.Prasad Eye Institute<br />Hyderabad";
	
	$result = sendMail($to ,$subject ,$body ,'' ,$attachment, $path , $date_field);
 
}
else
{
	//sendMail($to='ranganath@lvpei.org,yasaswi@lvpei.org,mahendra@lvpei.org,vipin@lvpei.org' ,$subject='No VVC Report available' ,'No VVC Report available' ,'' ,'', '' , '');
	//sendMail($to='ranganath@lvpei.org,vipin@lvpei.org' ,$subject='No VVC Report available' ,'No VVC Report available' ,'' ,'', '' , '');
	sendMail($to='avenkatesh@lvpei.org' ,$subject='No VVC Report available' ,'No VVC Report available' ,'' ,'', '' , '');
}

