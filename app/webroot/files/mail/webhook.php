<?php

	// Mandrill Dumping Script

	// Include database details
	require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/Config/database.php');
	
	$db_config =  new DATABASE_CONFIG();
	$dbconfig = get_object_vars($db_config);
	$mysqli = new mysqli($dbconfig['default']["host"], $dbconfig['default']["login"], $dbconfig['default']["password"], $dbconfig['default']["database"]);
	require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/Config/msh_config.php');
		echo SER_Test;
	// Check if POST Data is Empty
	if (empty($_POST)) {
		die("No POST Data found.");
	}

	$msgData = isset($_POST['mandrill_events'])?json_decode($_POST['mandrill_events'],true):array();
	
	// Check if Message Response is empty
	if (empty($msgData)) {
		die("No Message Response found.");
	}

	$senderEmailIdsArr = array(default_email);

	foreach ($msgData as $msg) {
		$event = isset($msg['event'])?$msg['event']:'';
		$timestamp = isset($msg['ts'])?date('Y-m-d H:i:s',$msg['ts']):'';
		$messageId = isset($msg['msg']['_id'])?$msg['msg']['_id']:'';
		$subject = isset($msg['msg']['subject'])?$msg['msg']['subject']:'';
		$email = isset($msg['msg']['email'])?$msg['msg']['email']:'';
		$sender = isset($msg['msg']['sender'])?$msg['msg']['sender']:'';
		$open = isset($msg['msg']['opens'])?count($msg['msg']['opens']):0;
		$click = isset($msg['msg']['clicks'])?count($msg['msg']['clicks']):0;
		$response_data = json_encode($msg);
		$created = date('Y-m-d H:i:s');

		if(!in_array($sender, $senderEmailIdsArr))
		continue;

		//Try Block for SQL Operation
		try {
			$emailStatusQuery = 'INSERT INTO `'.$dbconfig['default']['database'].'`.`email_statuses_mandrills` (`message_id`, `subject`, `email_to`, `sender`, `status`, `open`, `click`, `event_ts`, `response_data`, `created`) VALUES ("'.$messageId.'", "'.addslashes($subject).'", "'.$email.'", "'.$sender.'", "'.$event.'", "'.$open.'", "'.$click.'", "'.$timestamp.'", "'.addslashes($response_data).'", "'.$created.'")';
			$mysqli->query($emailStatusQuery);
			$lastemailJobDetailId = $mysqli->insert_id;
			//Catch block for Error Exception 
		} catch (Exception $e) {
			echo "Some Exception has occured while saving data. For ,<br/>{$emailStatusQuery}<br/>";
		}
		
	}

?>