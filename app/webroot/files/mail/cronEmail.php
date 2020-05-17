#!/usr/local/bin/php
<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	include('PHPMailer/src/Exception.php');
	include('PHPMailer/src/PHPMailer.php');
	include('PHPMailer/src/SMTP.php');
	$INCLUDE_DIR = "";
	$emailStrToArray = function ($email) {
		preg_match('/(.*)\s?\<(.*\@.*)>/', $email, $matches);
		if (!empty($matches) && !empty($matches[2])) {
			return [$matches[2], $matches[1]];
		}
		else return false;
	};
	$tempPath = dirname(dirname(dirname(dirname(__FILE__)))).'/tmp';
	require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/Vendor/sources/S3.php');
	require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/Config/database.php');
	require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/Config/msh_config.php');
	
	$db_config =  new DATABASE_CONFIG();
	$dbconfig = get_object_vars($db_config);
	$mysqli = new mysqli($dbconfig['default']["host"], $dbconfig['default']["login"], $dbconfig['default']["password"], $dbconfig['default']["database"]);
	
	//require("class.phpmailer.php");
	
	//ini_set('max_execution_time',300);
	ini_set('memory_limit',-1);
	// print_r($_SERVER);
	$Mail = new PHPMailer();
	// Commneted code before mandrill
	/*if($_SERVER['HTTP_HOST']=="localhost"){
		$Mail->Host   = "ssl://smtp.gmail.com";
		$Mail->SMTPAuth = true;
		$Mail->Username = "gpittest@gmail.com";
		$Mail->Password = "team@xceller";
		$Mail->Port = 465;
	}else{
		$Mail->Host = "localhost";
		$Mail->SMTPAuth = false;
		$Mail->Port = 25;
	}*/
	// $Mail->Host   = MANDRILL_SMTP_HOST;
	// $Mail->SMTPAuth = true;
	// $Mail->Username = MANDRILL_SMTP_UNAME;
	// $Mail->Password = MANDRILL_SMTP_PWD;
	// $Mail->Port = MANDRILL_SMTP_PORT;
	$Mail->Host   = 'smtp.mandrillapp.com';
  	$Mail->SMTPAuth = true;
  	$Mail->Username = 'Great Place IT Services';
  	$Mail->Password = '6cMGSSDL5Pb5GEArs1EmXQ';
 	$Mail->Port = '587';
  //End the Mandrill Code

	$Mail->WordWrap = 100;
  	$Mail->CharSet = "utf-8";
  	$Mail->Encoding = "base64";
	$Mail->IsSMTP();
	$Mail->IsHTML(true);	
	$Mail->ClearAllRecipients();
	$Mail->ClearReplyTos();
	//$Mail->Host = "localhost";
	//$Mail->Hostname = "smtp.dynect.net";

	$Mail->PluginDir =  dirname(__FILE__).'/';
	$Mail->SMTPKeepAlive = true;
	// print_r($Mail);
	// print_r($Mail->Error);
	
	$mysqli->query('SET NAMES utf8');
	$currentDateTime = date("Y-m-d H:i:s", strtotime('now'));	
	$limit=0;
	
	while(1){
		$limit1 = $limit+1;
		// find all active 1(new created) from email_jobs table
			$emailJobResult = $mysqli->query("select * from np_email_jobs where active=1 limit {$limit}, {$limit1}");
			if($emailJobResult->num_rows==0)
				break;
				
			$emailJobRow = $emailJobResult->fetch_assoc();
			//print_r($emailJobRow);
			// echo "<br />";
			// find data email_jobs
			$emailJobId = $emailJobRow['id'];
			// echo "<br />";
			$emailPriority = isset($emailJobRow['email_priority'])?$emailJobRow['email_priority']:1;
			$emailTotalSent = $emailJobRow['total']; 	
			$emailSent = $emailJobRow['sent'];	
			
			// update email_jobs set active 3 (in-process)
			$mysqli->query("update email_jobs set active=3 where id=".$emailJobId);
						
			// if set attachment == 1 from email_jobs then find attachment email_job_attachments table
			/*$isAttachment = $emailJobRow['attachment'];
			if($isAttachment==1){		
				$emailJobAttachmentResult = $mysqli->query("select * from email_job_attachments where email_job_id={$emailJobId}");
				$emailJobAttachmentRow = $emailJobAttachmentResult->fetch_assoc();
				//////////////////////
				$apiUsersResult=$mysqli->query("select * from api_users where api_type= 'ca_attachment_amazon_s3' ");
				$apiUsersRow = $apiUsersResult->fetch_assoc();
				$key = '5304767897258698418370730926441924952358918273645';
				$apiUserCafileUserName = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(trim($apiUsersRow['api_username'])), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
				$apiUserCafilePassword = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(trim($apiUsersRow['api_password'])), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
				$apiUserCafileUrl = trim($apiUsersRow['api_url']);
				$apiUserCafileMediaBucketName = trim($apiUsersRow['media_bucket_name']);
				//////////////////////
				$s3 = new S3($apiUserCafileUserName, $apiUserCafilePassword);
				$newS3LogoFileName = "attchment_{$emailJobAttachmentRow['id']}";
				$savePath = $newS3LogoFileName.'.'.$emailJobAttachmentRow['extension'];
				$filePath = $s3->getObject($apiUserCafileUrl.$apiUserCafileMediaBucketName,$newS3LogoFileName,$tempPath.'/'.$savePath);
				//$s3 = new S3(awsAccessKey, awsSecretKey);
//				$newS3LogoFileName = "attchment_{$emailJobAttachmentRow['id']}";
//				$savePath = $newS3LogoFileName.'.'.$emailJobAttachmentRow['extension'];
//				$filePath = $s3->getObject("media.greatrated.com/".ATTACHMENTBUCKETCOMPANIES,$newS3LogoFileName,$tempPath.'/'.$savePath);
				//$tempPath $mediaPath
			}*/
			
			// to find active 1 from email_job_details
			$emailJobDetailResult = $mysqli->query("select * from np_email_job_details where np_email_job_id={$emailJobId} and active=1");

			// if no result email_job_details
			if($emailJobDetailResult->num_rows==0){
				$mysqli->query("update np_email_jobs set sent=0, active=if(total>sent,1,2) where id=".$emailJobId);
				$limit++;
				continue;
			}
			// print_r($emailJobDetailResult);
			// if result email_job_details
			//$sentMail = 0;
			if($emailJobDetailResult->num_rows>0){
				//set emil Priority
				$Mail->Priority = $emailPriority;
				
				while($emailJobDetailRow = $emailJobDetailResult->fetch_assoc()){
					// print_r($emailJobDetailRow);
					$fromName = $emailFrom = $sender = $replayTo = $subject = $emailToName = $emailTo = $emailContent = '';
					$emailJobDetailId = trim($emailJobDetailRow['id']);
					$subject = trim(html_entity_decode($emailJobDetailRow['subject'],ENT_QUOTES,'UTF-8'));
					// $emailToName = trim(html_entity_decode($emailJobDetailRow['email_to_name'],ENT_QUOTES,'UTF-8'));
					// $emailTo = trim(html_entity_decode($emailJobDetailRow['email_to'],ENT_QUOTES,'UTF-8'));
					// $toEmails = explode(',', $emailTo);
					$emailContent = html_entity_decode($emailJobDetailRow['content'],ENT_QUOTES,'UTF-8');
					// $templateName = html_entity_decode($emailJobDetailRow['template_name'],ENT_QUOTES,'UTF-8');
					// if($emailJobDetailRow['from']==''){
					// 	$emailFrom = 'donotreply@greatplacetowork.com';
					// 	$fromName = 'Great Place to Work';
					// }
					if($emailJobDetailRow['from'] !=''){
						$emailFrom = trim(html_entity_decode($emailJobDetailRow['from'],ENT_QUOTES,'UTF-8'));					
						$string = $emailFrom;
						$fromName = '';
						$tok = explode("<",$string);
						if(count($tok)>1){
							$fromName = trim($tok[0]);
							$emailFrom = trim(substr($tok[1], 0, strpos($tok[1], '>')));
						}elseif ($emailJobDetailRow['sender']!='') {
							$emailFrom = $string;
							$fromName = trim(html_entity_decode($emailJobDetailRow['sender'],ENT_QUOTES,'UTF-8'));
						}else{
							$emailFrom = $string;
							$fromName = $emailFrom;
						}	
					}
					// if($emailJobDetailRow['from']==''){
					// 	$sender = 'donotreply@greatplacetowork.com';
					// 	//$sender = 'pravin@greatplaceitservices.com';
					// }
					if($emailJobDetailRow['from']!=''){
						$sender = trim(html_entity_decode($emailJobDetailRow['from'],ENT_QUOTES,'UTF-8'));
					}
					// if($emailJobDetailRow['reply_to']==''){
					// 	$replayTo = 'donotreply@greatplacetowork.com';
					// 	//$replayTo = 'pravin@greatplaceitservices.com';
					// }
					if($emailJobDetailRow['reply_to']!=''){
						$replayTo = trim(html_entity_decode($emailJobDetailRow['reply_to'],ENT_QUOTES,'UTF-8'));
					}
					// to clear to, cc, bcc
					$Mail->ClearAllRecipients();
					// to clear replay to
					$Mail->ClearReplyTos();
					// to clear attchments
					$Mail->ClearAttachments();
					
					$Mail->Sender = $sender;
					$arrSender = $emailStrToArray($sender);
					if (!empty($arrSender)) {
						$Mail->Sender = $arrSender[0];
					}
					$arrReplyTo = $emailStrToArray($replayTo);
					if (!empty($arrReplyTo)) {
						$Mail->AddReplyTo($replayTo,"Great Place to Work");
					}
					$Mail->From = $emailFrom;	
					$Mail->FromName = $fromName;				
					$Mail->Subject = $subject;
					//GR! SHOULD NOT BE CHANGED AS IT IS THE TEMPLATE NAME AnD IT IS NOT DISPLAYED ANYWHERE (15-06-2015) BY PRAVIN SAID
					/*if($templateName == 'GR! Review Preview Email' || $templateName == 'Customized Review Preview Email' || $templateName == 'Published Letter' || $templateName == 'Client Revision Requested Letter' || $templateName == 'Preview Reminder email'){*/
						$emailToDetailsResult = $mysqli->query("SELECT * FROM np_email_to_details WHERE np_email_job_detail_id={$emailJobDetailId}");
						// print_r($emailToDetailsResult);
						while($emailToDetailsRow = $emailToDetailsResult->fetch_assoc()){
							// print_r($emailToDetailsRow);
							$name = $emailToDetailsRow['name'];
							$email = $emailToDetailsRow['email'];
							//echo $name.' '.$email;
							if($emailToDetailsRow['category'] == 1){
								$Mail->AddAddress($email,$name);
							}
							if($emailToDetailsRow['category'] == 2){
								$Mail->AddCC($email,$name);
							}
							if($emailToDetailsRow['category'] == 3){
								$Mail->AddBCC($email,$name);
							}

							// #@12/02/2016 By Vishal, Demo fix not receving BCC
							// if (in_array($emailJobDetailRow['email_template_id'], array(2,3,5))) {
							// 	$Mail->AddBCC('vidhu.vaishnavi@greatplacetowork.com','Vidhu Vaishnavi');
							// 	$Mail->AddBCC('shivanshu@greatplaceitservices.com','Shivanshu Som');
							// 	$Mail->AddBCC('vishal@greatplaceitservices.com','Vishal Deshmukh');
							// }
						}
					/*}else{
						// $Mail->AddAddress($emailTo, $emailToName);
						foreach ($toEmails as $new_mail){
							$Mail->AddAddress($new_mail,$emailToName);
						}
					}*/

					$Mail->Body = $emailContent;
					//print_r($Mail);exit;
					/*if($isAttachment==1 && file_exists($tempPath.'/'.$savePath)){
						$fileAttachmentId = $emailJobAttachmentRow['id'];
						$fileName = $emailJobAttachmentRow['file_attachment'];
						$filePath = $tempPath.'/'.$savePath;
						$Mail->AddAttachment($filePath,$fileName);
					}*/
					//echo $Mail->ErrorInfo;
					if($Mail->send()){
						//$mysqli->query("update email_job_details set active=2,error_Info='{$Mail->ErrorInfo}', sent_date='{$currentDateTime}' where id=".$emailJobDetailId);
						$mysqli->query("update np_email_job_details set active=2, sent_date='{$currentDateTime}' where id=".$emailJobDetailId);
						//echo "mail sent";
					}else{
						$mysqli->query("update np_email_job_details set error_Info='{$Mail->ErrorInfo}' where id=".$emailJobDetailId);
						//echo "mail not sent";
					}
					// print_r($Mail);
					//echo $Mail->ErrorInfo;
					/*if($Mail->ErrorInfo!=''){
						if($Mail->send()){
							$mysqli->query("update email_job_details set active=2, sent_date='{$currentDateTime}' where id=".$emailJobDetailId);
							echo "mail sent";
						}else{
							$mysqli->query("update email_job_details set error_Info='{$Mail->ErrorInfo}' where id=".$emailJobDetailId);
							echo "mail not sent";
						}
					}else{
						$mysqli->query("update email_job_details set error_Info='{$Mail->ErrorInfo}' where id=".$emailJobDetailId);
					}*/
					//$sentMail++;
				}
				/*if($isAttachment==1 && file_exists($tempPath.'/'.$savePath)){
					unlink($tempPath.'/'.$savePath);
				}*/
			}
			
			echo memory_get_usage() . "\n"; // 36744
			echo (memory_get_peak_usage(true) ) . "<br>"; 	
			$emailJobDetailResult->free_result();	
			echo memory_get_usage() . "\n"; // 36744
			echo (memory_get_peak_usage(true) ) . "<br>";
			
			$sentMailCountid = $mysqli->query("select count(id) as count from np_email_job_details where np_email_job_id={$emailJobId} and active=2");
			$sentMailCount = $sentMailCountid->fetch_assoc();
			$mysqli->query("update np_email_jobs set sent={$sentMailCount['count']}, active=if(total>sent,1,2) where id={$emailJobId}");
			$result1 = $mysqli->query("select active from np_email_jobs where id={$emailJobId}");
			$row1 = $result1->fetch_assoc();
			if($row1['active']==1)
			$limit++;
		
			$emailJobResult->free_result();
			
			
	}
	$Mail->SmtpClose();
	$mysqli->close();			
		
?>