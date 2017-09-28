<?php

	class VVCReport
	{
		public $center_code;
		public $vc_location;
		public $vt_name;
		
		public $from_date;
		public $to_date;
		
		public $functional_vcs;
		public $no_filter_days;
		public $working_days;
		public $daily_avg_scrrening;
		
		public $total_screening;
		public $referal_from_vc;
		public $refer_sc;
		public $glasses_prescribed;
		public $glasses_dispensed;
		
		public $referal_from = array();
		public $patient_age = array();
		public $iop = array();
		public $no_tx = array();
		public $visual_imp = array();
		public $diagnosis = array();
		
		public $total_records;
 		public $s;
		public $n;
			
		// LABEL VISUAL ACUITY REPORT /
		public $va_label = array(
								'a_1_8'=>'&nbsp;&nbsp;&nbsp;6/6 to 6/18' , 
								'b_9_13' => '&lt; 6/18 to 6/60',
								'c_4_16' => '&lt; 6/60 to 3/60',
								'd_17_24' => '&lt; 3/60',
								);
		
		// LABEL ADVICE REPORT /
		public $advice_label = array(
								'no_treatment'=>'Within Normal Limits - No Rx' , 
								'refer_to_sc' => 'Referred to SC',
								'glasss_prescribed' => 'Prescribed glasses',
								'continue_same_glasses' => 'Continue same glasses',
								'glasss_prescribed_dispensed' => 'Dispensed glasses',
								'refer_to_physician' => 'Other'
								);
								
		public $sc_code;
		public $vision_center;
		
		public $total_referal_count;
		public $referal_count;
		public $referral_converion;

		public $tele_count;
		public $tele_completed;
		public $tele_conversion;
		
		
		
  		public function getReport()
		{
			if($this->center_code == 'VVC Network')$this->center_code='';
			
			$sql = "SELECT DATEDIFF('$this->to_date' , '$this->from_date') AS no_filter_days";
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$this->no_filter_days = (int)$fetch[no_filter_days];
			
			$sql = "
			SELECT 
			(COUNT(DISTINCT tp_created_date) / $this->no_filter_days) AS daily_avg_scrrening ,
			COUNT(DISTINCT tp_created_date) AS working_days
			
			FROM patient_unique_registration 
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$this->working_days = $fetch[working_days];
			//$this->daily_avg_scrrening = $fetch[daily_avg_scrrening];	
					
			$sql = "
			SELECT  COUNT(DISTINCT tp_VisionCenter) AS functional_vcs
 			FROM patient_unique_registration 
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$this->functional_vcs = $fetch[functional_vcs];

			
			$sql = "
 			SELECT 
			
			COUNT(DISTINCT tp_serverSync) AS total_screening ,
			COUNT(DISTINCT trp_sync_id) AS referal_from_vc ,
			SUM(IF(trp_mrno IS NOT NULL , 1, 0)) AS refer_sc 

			FROM patient_unique_registration
			LEFT JOIN tbl_referral_patients ON tp_serverSync=trp_sync_id
 			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";

			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$this->total_screening = (int)$fetch[total_screening];
			$this->referal_from_vc = (int)$fetch[referal_from_vc];
			$this->refer_sc = (int)$fetch[refer_sc];
 			$this->daily_avg_scrrening = round($fetch[total_screening]/($this->no_filter_days+1));
			
 
			
			$sql = "SELECT 
			trf_referralfrom ,
			COUNT(tp_serverSync) AS refer_count ,
			ROUND((COUNT(tp_serverSync)/$this->total_screening)*100 , 2) AS refer_percentage
			FROM patient_unique_registration
			JOIN tbl_master_referralfrom ON tp_referralfrom=ID
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date'";
		
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
				
			$sql .= " GROUP BY tp_referralfrom ORDER BY refer_percentage DESC ";
			
			$resset = mysql_query($sql);
			$referal_from = array();
			
			while($fetch = mysql_fetch_assoc($resset))
			{
				$referal_from[$fetch[trf_referralfrom]] = array('refer_count'=>(int)$fetch[refer_count] , 'refer_percentage'=>$fetch[refer_percentage]);
			}
			
			$this->referal_from = $referal_from;
			
			
			$sql = "
			SELECT 
			
			SUM(IF(tp_gender='Male' , 1 , 0)) AS male ,
			SUM(IF(tp_gender='Female' , 1 , 0)) AS female ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob)<=5) AND tp_gender='Male' , 1 , 0)) AS less_5_male ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob)<=5) AND tp_gender='Female' , 1 , 0)) AS less_5_female ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob) BETWEEN 6 AND 15) AND tp_gender='Male' , 1 , 0)) AS btw_6_5_male ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob) BETWEEN 6 AND 15) AND tp_gender='Female' , 1 , 0)) AS btw_6_5_female ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob) BETWEEN 16 AND 40) AND tp_gender='Male' , 1 , 0)) AS btw_16_40_male ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob) BETWEEN 16 AND 40) AND tp_gender='Female' , 1 , 0)) AS btw_16_40_female ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob) BETWEEN 41 AND 59) AND tp_gender='Male' , 1 , 0)) AS btw_41_59_male ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob) BETWEEN 41 AND 59) AND tp_gender='Female' , 1 , 0)) AS btw_41_59_female ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob) >=60) AND tp_gender='Male' , 1 , 0)) AS greater_60_male ,
			SUM(IF((YEAR(tp_created_date)-YEAR(tp_dob) >=60) AND tp_gender='Female' , 1 , 0)) AS greater_60_female
			
			FROM patient_unique_registration
			
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			//echo $sql.'<br />';
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$patient_age = array();			
			$patient_age[male] = (int)$fetch[male];
			$patient_age[female] = (int)$fetch[female];
			$patient_age[less_5_male] = (int)$fetch[less_5_male];
			$patient_age[less_5_female] = (int)$fetch[less_5_female];
			$patient_age[btw_6_5_male] = (int)$fetch[btw_6_5_male];
			$patient_age[btw_6_5_female] = (int)$fetch[btw_6_5_female];
			$patient_age[btw_16_40_male] = (int)$fetch[btw_16_40_male];
			$patient_age[btw_16_40_female] = (int)$fetch[btw_16_40_female];
			$patient_age[btw_41_59_male] = (int)$fetch[btw_41_59_male];
			$patient_age[btw_41_59_female] = (int)$fetch[btw_41_59_female];
			$patient_age[greater_60_male] = (int)$fetch[greater_60_male];
			$patient_age[greater_60_female] = (int)$fetch[greater_60_female];

			$this->patient_age = $patient_age;
			
			
			$sql = "SELECT 
			
			SUM(IF(tooc_at_od_value <>''  , 1 , 0)) AS od_iop ,
			SUM(IF(tooc_at_os_value <>''  , 1 , 0)) AS os_iop ,
			SUM(IF(tooc_at_od_value <>'' AND tooc_at_od_value<10  , 1 , 0)) AS od_iop_less_10 ,
			SUM(IF(tooc_at_os_value <>'' AND tooc_at_os_value<10  , 1 , 0)) AS os_iop_less_10 ,
			SUM(IF(tooc_at_od_value <>'' AND tooc_at_od_value BETWEEN 10 AND 20  , 1 , 0)) AS od_iop_btw_10_20 ,
			SUM(IF(tooc_at_os_value <>'' AND tooc_at_os_value BETWEEN 10 AND 20  , 1 , 0)) AS os_iop_btw_10_20 ,
			SUM(IF(tooc_at_od_value <>'' AND tooc_at_od_value > 20  , 1 , 0)) AS od_iop_greater_20 ,
			SUM(IF(tooc_at_os_value <>'' AND tooc_at_os_value > 20  , 1 , 0)) AS os_iop_greater_20
			
			FROM patient_unique_registration
			JOIN tbl_ocular_applanation_tonometry ON tp_serverSync=sync
			
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			//echo $sql.'<br />';
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);

			$iop = array();
			
			$iop[od_iop] = (int)$fetch[od_iop];
			$iop[os_iop] = (int)$fetch[os_iop];
			$iop[od_iop_less_10] = (int)$fetch[od_iop_less_10];
			$iop[os_iop_less_10] = (int)$fetch[os_iop_less_10];
			$iop[od_iop_btw_10_20] = (int)$fetch[od_iop_btw_10_20];
			$iop[os_iop_btw_10_20] = (int)$fetch[os_iop_btw_10_20];
			$iop[od_iop_greater_20] = (int)$fetch[od_iop_greater_20];
			$iop[os_iop_greater_20] = (int)$fetch[os_iop_greater_20];
			
			$this->iop = $iop;
			
			
			$sql = "SELECT 
			
			SUM(IF(tprx_no_Tx ='Y'  , 1 , 0)) AS no_treatment ,
 			SUM(IF(tprx_glass_advice ='1'  , 1 , 0)) AS continue_same_glasses ,
			SUM(IF(tprx_glass_advice ='2'  , 1 , 0)) AS glasss_prescribed ,
			SUM(IF(tprx_glass_advice ='3'  , 1 , 0)) AS glasss_prescribed_dispensed ,
			SUM(IF(tprx_referral_advice IN ('4' , '5')  , 1 , 0)) AS refer_to_sc ,
			SUM(IF(tprx_referral_advice=6  , 1 , 0)) AS refer_to_physician
			
			FROM patient_unique_registration
			JOIN tbl_plan_rx ON tp_serverSync=sync
			
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);

			$no_tx = array();
			
			$no_tx[no_treatment] = (int)$fetch[no_treatment];
			$no_tx[continue_same_glasses] = (int)$fetch[continue_same_glasses];
			$no_tx[glasss_prescribed] = (int)$fetch[glasss_prescribed];
			$no_tx[glasss_prescribed_dispensed] = (int)$fetch[glasss_prescribed_dispensed];
			//$no_tx[refer_to_sc] = (int)$fetch[refer_to_sc]; // Overriding till made mandatory
			$no_tx[refer_to_physician] = (int)$fetch[refer_to_physician];
			
			$no_tx[refer_to_sc] = $this->referal_from_vc;

			
			$this->glasses_prescribed = $no_tx[glasss_prescribed];
			$this->glasses_dispensed = (int)$fetch[glasss_prescribed_dispensed];
		
			$this->no_tx = $no_tx;
			
			$visual_imp_fields = array(
 				'OD' => 'BETWEEN 1 AND 24' , 
 				'OD_1_8' => 'BETWEEN 1 AND 8' , 
 				'OD_9_13' => 'BETWEEN 9 AND 13' , 
 				'OD_14_16' => 'BETWEEN 14 AND 16' , 
 				'OD_17_24' => 'BETWEEN 17 AND 24' , 
 			);
			
			$sql = " SELECT ";
			
			foreach($visual_imp_fields as $field_alias => $condition)
			{
				$sql .= "
				
				SUM(
				
				IF(
				od_aided.id<>'' , IF(od_aided.id ".$condition." , 1 , 0) ,
				IF(od.id <> '' , IF(od.id ".$condition." , 1 , 0) , 0)
				)
				
				) AS '".$field_alias."' , 
				
				SUM(
				
				IF(
				os_aided.id<>'' , IF(os_aided.id ".$condition." , 1 , 0) ,
				IF(os.id <> '' , IF(os.id ".$condition." , 1 , 0) , 0)
				)
				
				) AS '".str_replace('OD', 'OS' , $field_alias)."' , 
 				
				";
 			}
			
			$sql .= " 1 "; // just for prevent comma separte issue
			
			$sql .= "
 			FROM patient_unique_registration
			JOIN tbl_refraction_visual_acuity ON tp_serverSync=sync
 			LEFT JOIN meter_index od_aided ON tor_va_od_aided_glasses=od_aided.data
			LEFT JOIN meter_index os_aided ON tor_va_os_aided_glasses=os_aided.data
 			LEFT JOIN meter_index od ON tor_va_od_unaided=od.data
			LEFT JOIN meter_index os ON tor_va_os_unaided=os.data

			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			
 			//echo nl2br($sql).'<br />';
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);

			$unaided = array();
			
			$unaided[OD] = (int)$fetch[OD];
			$unaided[OS] = (int)$fetch[OS];
			
			$unaided[a_1_8][od] = (int)$fetch[OD_1_8];
			$unaided[a_1_8][os] =(int) $fetch[OS_1_8];
			
 			$unaided[b_9_13][od] = (int)$fetch[OD_9_13];
			$unaided[b_9_13][os] = (int)$fetch[OS_9_13];
			
			$unaided[c_4_16][od] = (int)$fetch[OD_14_16];
			$unaided[c_4_16][os] = (int)$fetch[OS_14_16];
			
			$unaided[d_17_24][od] = (int)$fetch[OD_17_24];
			$unaided[d_17_24][os] = (int)$fetch[OS_17_24];
 			
			$this->unaided = $unaided;
			
			$sql = " DROP TABLE IF EXISTS visual_impairment";
			mysql_query($sql);
			//echo $sql.';<br />';
			
			$sql = " CREATE TEMPORARY TABLE visual_impairment(vsync_id int , meter_type VARCHAR(25) , KEY idx_vsync_id(vsync_id) , KEY idx_meter_type(meter_type) ); ";
			mysql_query($sql);
			//echo $sql.';<br />';

			$sql = "			
			INSERT INTO visual_impairment
			SELECT 
			tp_serverSync ,
			IF(
			(tor_better_eye BETWEEN 1 AND 8 ), 'a_1_8',
			IF((tor_better_eye BETWEEN 9 AND 13 ) , 'b_9_13' ,
			IF((tor_better_eye BETWEEN 14 AND 16 ) , 'c_4_16' ,
			IF((tor_better_eye BETWEEN 17 AND 24 ) , 'd_17_24' , NULL 
			)))
			) AS meter_type
			
			FROM patient_unique_registration
			JOIN tbl_refraction_visual_acuity ON tp_serverSync=sync
 			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
				
			$sql .= " GROUP BY tp_serverSync ";
			mysql_query($sql);
			//echo nl2br($sql).';<br />';
			
			$sql = "
			SELECT 
			meter_type , COUNT(vsync_id) AS visual_imp_count
			FROM visual_impairment WHERE meter_type IS NOT NULL GROUP BY meter_type	";
			$resset = mysql_query($sql);
			//echo $sql.';<br />';
			$visual_imp = array();
			
			while($fetch = mysql_fetch_assoc($resset))
			{
 				$visual_imp[$fetch[meter_type]] = (int)$fetch[visual_imp_count];
			}
			
			$this->visual_imp = $visual_imp;
			
			
			$sql = " SELECT
			
			IF(tod_ocular_category IS NULL , 'MANUAL' , IF(tod_ocular_category<>'Others' , tod_ocular_category , 'Z_OTHERS')) AS ocular_category , 
			COUNT(tod_ocular_category) AS diagnosis_count ,
 			SUM(IF(tod_eye='OD' , 1 , 0)) AS od_diagnosis_count ,
			SUM(IF(tod_eye='OS' , 1 , 0)) AS os_diagnosis_count ,
			SUM(IF(tod_eye='OU' , 1 , 0)) AS ou_diagnosis_count ,
			SUM(IF(tod_ocular_category IS NULL  , 1 , 0)) AS null_diagnosis_count
			
			FROM tbl_ocular_diagnosis 
			JOIN patient_unique_registration ON tp_serverSync=tod_server_sync_id
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
				
			$sql .= "  GROUP BY tod_ocular_category ORDER BY ocular_category ASC ";
			$resset = mysql_query($sql);
			//echo $sql.'<br />';
			$diagnosis = array();
			$diagnosis_manual = array();
			while($fetch = mysql_fetch_assoc($resset))
			{
				$diagnosis_count = 0;
				if($fetch[diagnosis_count] > 0)$diagnosis_count = $fetch[diagnosis_count];
				if($fetch[null_diagnosis_count] > 0)$diagnosis_count = $fetch[null_diagnosis_count];
				
				if($fetch[ocular_category] != 'MANUAL')
				{
					$diagnosis[$fetch[ocular_category]] = array(
														'diagnosis_count' => $diagnosis_count ,
														'od_diagnosis_count' => $fetch[od_diagnosis_count] ,
														'os_diagnosis_count' => $fetch[os_diagnosis_count] ,
														'ou_diagnosis_count' => $fetch[ou_diagnosis_count]
													);
				}
				else
				{
									$diagnosis_manual = array(
														'diagnosis_count' => $diagnosis_count ,
														'od_diagnosis_count' => $fetch[od_diagnosis_count] ,
														'os_diagnosis_count' => $fetch[os_diagnosis_count] ,
														'ou_diagnosis_count' => $fetch[ou_diagnosis_count]
													);
				}
 			}
 			
			foreach($diagnosis as $key => $diag_arr)
			{
				if($key == 'Z_OTHERS')
				{
					$diagnosis[$key][diagnosis_count] += $diagnosis_manual[diagnosis_count];
					$diagnosis[$key][od_diagnosis_count] += $diagnosis_manual[od_diagnosis_count];
					$diagnosis[$key][os_diagnosis_count] += $diagnosis_manual[os_diagnosis_count];
					$diagnosis[$key][ou_diagnosis_count] += $diagnosis_manual[ou_diagnosis_count];
				}
			}
			
			
			$this->diagnosis = $diagnosis;
			
			
			$sql = "SELECT 
			
			COUNT(tp_patient_id_Tab) AS total_referal_count ,
			SUM(IF(trp_mrno IS NOT NULL , 1 , 0)) AS referal_count ,
			IF( COUNT(tp_patient_id_Tab) > 0 , ROUND((SUM(IF(trp_mrno<>'' , 1 , 0))/COUNT(tp_patient_id_Tab))*100 , 2) , NULL ) AS referral_converion
			
			FROM patient_unique_registration 
			JOIN tbl_referral_patients ON tp_serverSync=trp_sync_id 
			
			WHERE trp_sent_date BETWEEN '$this->from_date' AND '$this->to_date' AND trp_VTreferralId IS NOT NULL ";
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			
			//echo $sql.'<br />';
			
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$this->total_referal_count = (int)$fetch[total_referal_count];
			$this->referal_count = (int)$fetch[referal_count];
			$this->referral_converion = $fetch[referral_converion];
				
				
			$sql = "SELECT 
			
			COUNT( tp_patient_id_Tab ) AS tele_count  , 
			SUM(IF(feedback_created_time IS NOT NULL , 1 , 0)) AS tele_completed ,
			IF(
			COUNT(tp_patient_id_Tab) > 0 , ROUND((SUM(IF(feedback_created_time IS NOT NULL , 1 , 0))/COUNT(tp_patient_id_Tab))*100 , 2) , NULL
			) AS tele_conversion
			
 			FROM tbl_teleophthalmology 
			JOIN patient_unique_registration ON patient_sync_id = tp_serverSync 
			WHERE  query_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$this->tele_count = (int)$fetch[tele_count];
			$this->tele_completed = (int)$fetch[tele_completed];
			$this->tele_conversion = $fetch[tele_conversion];
  		
		}
		
		public function getDeficiencyReport()
		{
			// CALCULATE START MONTH AND START YEAR DATES FOR DEFICIENCY REPORT //
 			
			$sql = " SELECT 
				DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AS start_month , 
				DATE_SUB(CURRENT_DATE, INTERVAL DAYOFYEAR(CURRENT_DATE)-1 DAY) AS start_year 
			LIMIT 1 ";
			
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$this->month_start = $fetch[start_month];
			$this->year_start = $fetch[start_year];
			
			// CALCULATE VVC CODES ARRAY FOR DEFICIENCY REPORT //
			
			$sql = "SELECT DISTINCT  tp_sc_code  FROM  filter_technician_name WHERE 1 ";
			$resset = mysql_query($sql);

			$vvc_code = array();
			while($fetch = mysql_fetch_assoc($resset))
			{
				$vvc_code[$fetch[tp_sc_code]] = array('today_df' => '--' , 'month_df' => '--' , 'year_df' => '--' , 'today_tele_df' => '--' , 'month_tele_df' => '--' , 'year_tele_df' => '--');
			}
			
			$this->vvc_deficiecy_array = $vvc_code;
			
			//print "<pre>";
			//print_r($this->vvc_deficiecy_array);
			
			$this->referalDeficiencyReport();
			$this->teleDeficiencyreport();
 
		}
		
 		public function referalDeficiencyReport()
		{
			$sql = "SELECT 
			tp_sc_code ,
 			IF( COUNT(tp_patient_id_Tab) > 0 , 100-ROUND((SUM(IF(trp_mrno<>'' , 1 , 0))/COUNT(tp_patient_id_Tab))*100 , 2) , NULL ) AS referral_converion ,
			SUM(IF(trp_mrno<>'' , 1 , 0)) AS refer_count_today ,
			COUNT(tp_patient_id_Tab) AS total_count_today
			
 			FROM patient_unique_registration 
			JOIN tbl_referral_patients ON tp_serverSync=trp_sync_id 
			
			WHERE trp_sent_date='$this->from_date' AND trp_VTreferralId IS NOT NULL GROUP BY tp_sc_code ";
			
			//echo $sql.'<br /><br />';
			
			$resset = mysql_query($sql);
			
 			while($fetch = mysql_fetch_assoc($resset))
			{
				if($fetch[referral_converion] == 100.00 || $fetch[referral_converion] == 0.00)
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][today_df] = (int)$fetch[referral_converion];
				else
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][today_df] = $fetch[referral_converion];
					
				if($fetch[referral_converion] != '--')
				{
  					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][today_df] .= '%';
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][refer_count_today] = $fetch[refer_count_today];
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][total_count_today] = $fetch[total_count_today];
				}
					
			}
			
			$sql = "SELECT 
			tp_sc_code ,
 			IF( COUNT(tp_patient_id_Tab) > 0 , 100-ROUND((SUM(IF(trp_mrno<>'' , 1 , 0))/COUNT(tp_patient_id_Tab))*100 , 2) , NULL ) AS referral_converion_month ,
			SUM(IF(trp_mrno<>'' , 1 , 0)) AS refer_count_month ,
			COUNT(tp_patient_id_Tab) AS total_count_month
			
			FROM patient_unique_registration 
			JOIN tbl_referral_patients ON tp_serverSync=trp_sync_id 
			
			WHERE trp_sent_date BETWEEN '$this->month_start' AND CURRENT_DATE AND trp_VTreferralId IS NOT NULL GROUP BY tp_sc_code ";
			
			//echo $sql.'<br /><br />';
			
			$resset = mysql_query($sql);
			
			while($fetch = mysql_fetch_assoc($resset))
			{
				if($fetch[referral_converion_month] == 100.00 || $fetch[referral_converion_month] == 0.00)
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][month_df] = (int)$fetch[referral_converion_month];
				else
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][month_df] = $fetch[referral_converion_month];
					
				if($fetch[referral_converion_month] != '--')
				{
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][month_df] .= '%';
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][refer_count_month] = $fetch[refer_count_month];
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][total_count_month] = $fetch[total_count_month];
				}
  			}
			
			$sql = "SELECT 
			
			tp_sc_code ,
 			IF( COUNT(tp_patient_id_Tab) > 0 , 100-ROUND((SUM(IF(trp_mrno<>'' , 1 , 0))/COUNT(tp_patient_id_Tab))*100 , 2) , NULL ) AS referral_converion_year ,
			SUM(IF(trp_mrno<>'' , 1 , 0)) AS refer_count_year ,
			COUNT(tp_patient_id_Tab) AS total_count_year
			
			FROM patient_unique_registration 
			JOIN tbl_referral_patients ON tp_serverSync=trp_sync_id 
			
			WHERE trp_sent_date BETWEEN '$this->year_start' AND CURRENT_DATE AND trp_VTreferralId IS NOT NULL GROUP BY tp_sc_code ";
			
			//echo $sql.'<br /><br />';
			
			$resset = mysql_query($sql);
			
 			while($fetch = mysql_fetch_assoc($resset))
			{
				if($fetch[referral_converion_year] == 100.00 || $fetch[referral_converion_year] == 0.00)
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][year_df] = (int)$fetch[referral_converion_year];
				else
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][year_df] = $fetch[referral_converion_year];
					
 				if($fetch[referral_converion_year] != '--')
				{
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][year_df] .= '%';
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][refer_count_year] = $fetch[refer_count_year];
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][total_count_year] = $fetch[total_count_year];
				}
 			}
			
 		}
		
		public function teleDeficiencyreport()
		{
			//TELE DEFECIENCY TODAY PERCENTAGE //
			
			$sql = "SELECT 
			tp_sc_code ,
			IF(
			COUNT(tp_patient_id_Tab) > 0 , 100-ROUND((SUM(IF(feedback_created_time IS NOT NULL , 1 , 0))/COUNT(tp_patient_id_Tab))*100 , 2) , NULL
			) AS tele_conversion_today ,
			SUM(IF(feedback_created_time IS NOT NULL , 1 , 0)) AS tele_count_today,
			COUNT(tp_patient_id_Tab) AS tele_total_today
			
 			FROM tbl_teleophthalmology
			JOIN patient_unique_registration ON patient_sync_id = tp_serverSync 
			WHERE query_created_date='$this->from_date' GROUP BY tp_sc_code ";
			
			//echo $sql.'<br /><br />';
			$resset = mysql_query($sql);
			
			$deficiency_month = array();
			while($fetch = mysql_fetch_assoc($resset))
			{
				if($fetch[tele_conversion_today] == 100.00 || $fetch[tele_conversion_today] == 0.00)
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][today_tele_df] = (int)$fetch[tele_conversion_today];
				else
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][today_tele_df] = $fetch[tele_conversion_today];
					
 				if($fetch[tele_conversion_today] != '--')
				{
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][today_tele_df] .= '%';
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][tele_count_today] = $fetch[tele_count_today];
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][tele_total_today] = $fetch[tele_total_today];
				}
 			}

			//TELE DEFECIENCY MONTH PERCENTAGE //
			
			$sql = "SELECT 
			tp_sc_code ,
			IF(
			COUNT(tp_patient_id_Tab) > 0 , 100-ROUND((SUM(IF(feedback_created_time IS NOT NULL , 1 , 0))/COUNT(tp_patient_id_Tab))*100 , 2) , NULL
			) AS tele_conversion_month,
			SUM(IF(feedback_created_time IS NOT NULL , 1 , 0)) AS tele_count_month,
			COUNT(tp_patient_id_Tab) AS tele_total_month
			
 			FROM tbl_teleophthalmology 
			JOIN patient_unique_registration ON patient_sync_id = tp_serverSync 
			WHERE  query_created_date BETWEEN '$this->month_start' AND CURRENT_DATE GROUP BY tp_sc_code ";
			
			//echo $sql.'<br /><br />';
			$resset = mysql_query($sql);
			
			$deficiency_month = array();
			while($fetch = mysql_fetch_assoc($resset))
			{
				if($fetch[tele_conversion_month] == 100.00 || $fetch[tele_conversion_month] == 0.00)
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][month_tele_df] = (int)$fetch[tele_conversion_month];
				else
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][month_tele_df] = $fetch[tele_conversion_month];
					
 				if($fetch[tele_conversion_month] != '--')
				{
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][month_tele_df] .= '%';
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][tele_count_month] = $fetch[tele_count_month];
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][tele_total_month] = $fetch[tele_total_month];
				}
			}

			//TELE DEFECIENCY YTD PERCENTAGE //
			
			$sql = "SELECT 
			tp_sc_code , 
			IF(
			COUNT(tp_patient_id_Tab) > 0 , 100-ROUND((SUM(IF(feedback_created_time IS NOT NULL , 1 , 0))/COUNT(tp_patient_id_Tab))*100 , 2) , NULL
			) AS tele_conversion_year,
			SUM(IF(feedback_created_time IS NOT NULL , 1 , 0)) AS tele_count_year,
			COUNT(tp_patient_id_Tab) AS tele_total_year
			
 			FROM tbl_teleophthalmology 
			JOIN patient_unique_registration ON patient_sync_id = tp_serverSync 
			WHERE  query_created_date BETWEEN '$this->year_start' AND CURRENT_DATE GROUP BY tp_sc_code ";
			
			//echo $sql.'<br /><br />';
			$resset = mysql_query($sql);

			$deficiency_year = array();
			while($fetch = mysql_fetch_assoc($resset))
			{
				if($fetch[tele_conversion_year] == 100.00 || $fetch[tele_conversion_year] == 0.00)
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][year_tele_df] = (int)$fetch[tele_conversion_year];
				else
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][year_tele_df] = $fetch[tele_conversion_year];
					
 				if($fetch[tele_conversion_year] != '--')
				{
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][year_tele_df] .= '%';
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][tele_count_year] = $fetch[tele_count_year];
					$this->vvc_deficiecy_array[$fetch[tp_sc_code]][tele_total_year] = $fetch[tele_total_year];
				}
			}

 		}
		
		public function performanceBoard()
		{
			if($this->center_code == 'VVC Network')$this->center_code='';
			
			$date_diff = "";
			$time_diff = "";
			$date_diff = "DATEDIFF(MAX(CONCAT(tp_created_date ,' ' , tp_created_time)) , MIN(CONCAT(tp_created_date ,' ' , tp_created_time)))";
			$time_diff = "TIMEDIFF(MAX(CONCAT(tp_created_date ,' ' , tp_created_time)) , MIN(CONCAT(tp_created_date ,' ' , tp_created_time)))";
			
			$sql = "
			SELECT tp_VisionCenter , tp_surveyorName ,
			ROUND((COUNT(DISTINCT tp_serverSync) / ($this->no_filter_days+1)) , 2) AS daily_avg_scrrening ,
			
			MIN(CONCAT(tp_created_date ,' ' , tp_created_time)) , 
			MAX(CONCAT(tp_created_date ,' ' , tp_created_time))   ,
			ROUND(".$date_diff." , 2) AS tdf ,
			IF(
			
			ROUND(".$date_diff." , 2) >1  ,  CONCAT( COUNT(DISTINCT tp_created_date) , ' day(s)') , SEC_TO_TIME(TIME_TO_SEC( ".$time_diff.")) 
			
			) AS working_duration  
 			
			FROM patient_unique_registration 
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			
			$sql .= " GROUP BY tp_VisionCenter , tp_surveyorName ORDER BY daily_avg_scrrening ASC ";
			
			$resset = mysql_query($sql);
 			//echo $sql . '<br />';
			
			$performance = array();
			
 			while($fetch = mysql_fetch_assoc($resset))
			{
 				$key = '';
				if($fetch[daily_avg_scrrening] <=10)$key = 'LOW';
				else
				if($fetch[daily_avg_scrrening] >10 && $fetch[daily_avg_scrrening]<=15)$key = 'MEDIUM';
				else
				if($fetch[daily_avg_scrrening] >15)$key = 'HIGH';
				
				$working_time = $fetch[working_duration];
				if(strpos($fetch[working_duration] , ':') !== false)
					$working_time = substr($fetch[working_duration] , 0 , 5);
				if($fetch[daily_avg_scrrening] == 1)$working_time = '--';
				
				$performance[$key][] = array('vision_center' => $fetch[tp_VisionCenter] , 
										'tp_surveyorName' => $fetch[tp_surveyorName] , 
										'daily_avg_scrrening' => ($fetch[daily_avg_scrrening] < 1 ? $fetch[daily_avg_scrrening] : round($fetch[daily_avg_scrrening])) ,
										'working_duration' => $working_time
										);
 			}
			
			return $performance;			
		}
		
		public function SCPerformanceBoard()
		{
			if($this->center_code == 'VVC Network')$this->center_code='';
			
 			$sql = "
			SELECT tp_sc_code AS sc_code ,
			ROUND((COUNT(DISTINCT tp_serverSync) / ($this->no_filter_days+1))) AS daily_avg_scrrening 
			
			FROM patient_unique_registration 
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			/*if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";*/
			
			$sql .= " GROUP BY tp_sc_code ORDER BY daily_avg_scrrening ASC ";
			
			$resset = mysql_query($sql);
 			//echo $sql . '<br />';
			
			$performance = array();
			
 			while($fetch = mysql_fetch_assoc($resset))
			{
 				$key = '';
				if($fetch[daily_avg_scrrening] <=50)$key = 'LOW';
				else
				if($fetch[daily_avg_scrrening] >50 && $fetch[daily_avg_scrrening]<=100)$key = 'MEDIUM';
				else
				if($fetch[daily_avg_scrrening] >100)$key = 'HIGH';

				$performance[$key][] = array('sc_code' => $fetch[sc_code] , 
 											 'daily_avg_scrrening' => $fetch[daily_avg_scrrening]
 										);
 			}
			
			return $performance;			
		}
		
 		public function getCoverageReport()
		{
			if($this->center_code == 'VVC Network')$this->center_code='';
			
			$sql = "
 			SELECT 
			tp_location AS village ,
			tp_mandal_name AS mandal,
			COUNT(DISTINCT tp_serverSync) AS total_screening ,
 			SUM(IF(tprx_glass_advice=3 , 1, 0)) AS glasses_prescribed ,
			COUNT(DISTINCT trp_sync_id) AS referal_from_vc

			FROM patient_unique_registration
			LEFT JOIN tbl_referral_patients ON tp_serverSync=trp_sync_id
			LEFT JOIN tbl_plan_rx ON tp_serverSync=sync
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
				
			$sql .= " GROUP BY tp_location , tp_mandal_name ORDER BY total_screening DESC ";
				
			//echo $sql;
			$resset = mysql_query($sql);
			
 			return $resset;
 		}
		
		public function getReferalList()
		{
			if($this->center_code == 'VVC Network')$this->center_code='';

			if($this->total_records == 1) //For total records
			{
				$limit = "";
				$fields= " COUNT(tp_created_date) AS total_rows ";
			}
			else//GRID
			{
				if($this->s == -1 && $this->n == -1)
					$limit = "";
				else
					$limit = " LIMIT ".$this->s.",".$this->n;
					
				$fields = " tp_VTreferralId AS referral_id ,
				CONCAT(tp_first_name , ' ' , tp_last_name) AS patient_name ,
				tp_created_date AS exam_date ,
				CONCAT(tp_address , ',\n', tp_location , '\n', tp_mandal_name , ', ' , tp_district) AS address ,
				tp_cell_number AS mobile ,
				IF(trp_fr_corr_od_bcva<>'--Select--' , trp_fr_corr_od_bcva , '') AS od_bcva ,
				IF(trp_fr_corr_os_bcva<>'--Select--' , trp_fr_corr_os_bcva , '') AS os_bcva ,
				CONCAT(tp_sc_code , ', ' , trp_mrno, ', \n' , DATE_FORMAT(trp_seen_date , '%d %b %Y')) AS attended_details ";
			}
			
			$sql = "SELECT 
			
			" . $fields . "
			
			FROM patient_unique_registration 
			JOIN tbl_referral_patients ON tp_serverSync=trp_sync_id 
			LEFT JOIN tbl_refraction_prescription ON tp_serverSync=Sync
			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' AND trp_VTreferralId IS NOT NULL ";
			
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			
 			$sql .= $limit;
 			
			////echo $sql.'<br /><br />';
			
 			$resset = mysql_query($sql);
 			
			if($this->total_records == 1)
			{
 				$fetch = mysql_fetch_assoc($resset);
				
				return $fetch[total_rows];
			}
 			else
	 			return $resset;
		}
		
		public function getDispensedList()
		{
			if($this->center_code == 'VVC Network')$this->center_code='';
			
			if($this->total_records == 1) //For total records
			{
				$limit = "";
				$fields= " COUNT(tp_created_date) AS total_rows ";
			}
			else//GRID
			{
				if($this->s == -1 && $this->n == -1)
					$limit = "";
				else
					$limit = " LIMIT ".$this->s.",".$this->n;
					
				$fields = " tp_temp_mrno AS app_mrno , CONCAT(tp_first_name , ' ' , tp_last_name) AS patient_name ,
				tp_created_date AS exam_date ,
				CONCAT(tp_address , ' ', tp_location , ' ', tp_mandal_name , ' ' , tp_district) AS address ,
				tp_cell_number AS mobile ";
			}
			
			
			$sql = "SELECT 
			
			" . $fields . "
 			
			FROM patient_unique_registration 
			JOIN tbl_plan_rx ON tp_serverSync=sync AND tprx_glass_advice ='3'

			WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			if($this->center_code != '')
				$sql .= " AND tp_sc_code='$this->center_code' ";
			if($this->vc_location != '')
				$sql .= " AND tp_VisionCenter='$this->vc_location' ";
			if($this->vt_name != '')
				$sql .= " AND tp_surveyorName='$this->vt_name' ";
			
 			$sql .= $limit;
 			
			////echo $sql.'<br /><br />';
			
 			$resset = mysql_query($sql);
 			
			if($this->total_records == 1)
			{
 				$fetch = mysql_fetch_assoc($resset);
				
				return $fetch[total_rows];
			}
 			else
	 			return $resset;
		}
		
		public function getFilter()
		{
			$result = array();
			
			$sql = "SELECT  tp_sc_code ,  tp_VisionCenter ,  tp_surveyorName 
			FROM  filter_technician_name 
			WHERE 1 
			GROUP BY  tp_sc_code ,  tp_VisionCenter ,  tp_sc_code ";
			
			$resset = mysql_query($sql);
			
			$i=0;
			while($fetch = mysql_fetch_assoc($resset))
			{
				$result[$fetch[tp_sc_code]]['VC_NAME'][$i++] = $fetch[tp_VisionCenter];
   			}
			
			return $result;
		}
		
		public function getVTName()
		{
			$result = array();
			
			$sql = " SELECT  tp_surveyorName
			FROM  filter_technician_name 
			WHERE tp_sc_code='$this->sc_code' AND tp_VisionCenter ='$this->vision_center' ";
			
			$resset = mysql_query($sql);
			
			$i=0;
 			while($fetch = mysql_fetch_assoc($resset))
			{
				$result[$i] = $fetch[tp_surveyorName];
				$i++;
   			}
			
			return $result;
		}
		
		public function getDailyReport()
		{
			$report = array();
			
			// Vision Centers
			$sql = " SELECT COUNT(tp_VisionCenter) AS total_vcs, SUM( if(status = 'ACTIVE',1,0)) AS active_vcs , SUM( if(status = 'INACTIVE',1,0)) AS inactive_vcs FROM  centers ";
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$report['total_vcs'] = $fetch['total_vcs'];
			$report['active_vcs'] = $fetch['active_vcs'];
			$report['inactive_vcs'] = $fetch['inactive_vcs'];
			
			//Total Screening
			$sql = " SELECT 
					 COUNT(DISTINCT tp_serverSync) AS total_screening,
					 SUM(IF(tprx_glass_advice IN ('2','3')  , 1 , 0)) AS glasss_prescribed ,
					 SUM(IF(tprx_glass_advice ='3'  , 1 , 0)) AS glasss_prescribed_dispensed  ,
					 SUM(IF(tprx_referral_advice IN ('4' , '5')  , 1 , 0)) AS refer_to_sc 
					 FROM patient_unique_registration
					 JOIN tbl_plan_rx ON tp_serverSync=sync
					 WHERE tp_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$report['total_screening'] = $fetch['total_screening'];
			$report['avg_screening'] =  round(($fetch['total_screening']/$report['active_vcs']),2);
			
			$report['glasss_prescribed'] = $fetch['glasss_prescribed'];
			$report['avg_glasss_prescribed'] =  round(($fetch['glasss_prescribed']/$report['active_vcs']),2);
			
			$report['glasss_prescribed_dispensed'] = $fetch['glasss_prescribed_dispensed'];
			$report['avg_prescribed_dispensed'] =  round(($fetch['glasss_prescribed_dispensed']/$report['active_vcs']),2);
			
			$report['refer_to_sc'] = $fetch['refer_to_sc'];
			$report['avg_refer_to_sc'] =  round(($fetch['refer_to_sc']/$report['active_vcs']),2);
			
			$sql = " SELECT 
					 COUNT( tp_patient_id_Tab ) AS tele_count  , 
					 SUM(IF(feedback_created_time IS NOT NULL , 1 , 0)) AS tele_completed 
					 FROM tbl_teleophthalmology 
					 JOIN patient_unique_registration ON patient_sync_id = tp_serverSync 
					 WHERE query_created_date BETWEEN '$this->from_date' AND '$this->to_date' ";
			
			$resset = mysql_query($sql);
			$fetch = mysql_fetch_assoc($resset);
			
			$report['tele_count'] = $fetch['tele_count'];
			$report['tele_completed'] = $fetch['tele_completed'];
			
			return $report;
			
			
		}
		
	}
	
	


?>