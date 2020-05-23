<?php
App::uses('AppController', 'Controller');

/**
 * Users Controller
 */
class UsersController extends AppController {

	var $name = 'Users';
	var $uses = array('User');
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('register', 'updateOtp', 'resendOtp', 'sendOtpMail', 'randomPassword');
		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');
	}

	public function resendOtp(){
		$flag = true;
		if (empty($_POST['userId'])) {
			$flag = false;
			$response = array(
				'code' => '404',
				'message' => 'Data Empty.',
				'data' => ''
			);
		}
		$today = date('Y-m-d H:i:s');
		$userData = $this->User->find('first',array('conditions'=>array('User.id' => $_POST['userId'])));
		$otp = $this->randomPassword(4);
		if ($flag == true) {
			$this->User->updateAll(array('User.otp' => $otp, 'User.modified' => $today), array('User.id' => $_POST['userId']));
			$name = $userData['User']['fname'].' '.$userData['User']['lname'];
			$returnArray = $this->sendOtpMail($userData['User']['email'], $name, $otp);
			if ($returnArray['send'] == '1') {
				$response = array(
					'code' => '200',
					'message' => 'One time password sended to email.',
					'data' => $userData['User']['id']
				);
			}
			if ($returnArray['send'] == '2') {
				$response = array(
					'code' => '403',
					'message' => $returnArray['msg'],
					'data' => ''
				);
			}
		}
		$this->set(array('response' => $response, '_serialize' => 'response'));
	}
	public function updateOtp(){
		$flag = true;
		if (empty($_POST['otp'])) {
			$flag = false;
			$response = array(
				'code' => '404',
				'message' => 'Data Empty.',
				'data' => ''
			);
		}
		$today = date('Y-m-d H:i:s');
		$userData = $this->User->find('first',array('conditions'=>array('User.id' => $_POST['userId']),'fields'=>array('User.id', 'User.otp', 'User.modified')));

		$datetime1 = date_create($userData['User']['modified']);
		$datetime2 = date_create($today);
		$interval = date_diff($datetime1, $datetime2);
		$diff = $interval->format('%i');
		if ($diff > 10) {
			$flag = false;
			$response = array(
				'code' => '401',
				'message' => 'One time Password has expired.',
				'data' => ''
			);
		}
		if ($_POST['otp'] != $userData['User']['otp']) {
			$flag = false;
			$response = array(
				'code' => '403',
				'message' => 'One time password is wrong.',
				'data' => ''
			);
		}
		if ($flag == true) {
			$this->User->updateAll(array('User.status' => 1, 'User.email_verified' => 1), array('User.id' => $userData['User']['id']));
			$userData = $this->User->find('first', array('conditions'=>array('User.id' => $userData['User']['id'])));
			App::import('Controller', 'Authentication');
			$auth = 'AuthenticationController';		
			$authController = new $auth;		
			$userData['User']['token'] = $authController->getToken($userData['User']['id']);
			$response = array(
				'code' => '200',
				'message' => 'User created success.',
				'data' => $userData
			);
		}
		$this->set(array('response' => $response, '_serialize' => 'response'));
	}

	public function register(){
		$flag = true;
		if (empty($_POST)) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Data Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['fname'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'First Name Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['lname'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Last Name Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['gender'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Gender Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['genderinterest'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Gender Interest Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['dateofbirth'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Date of Birth Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['religion'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Religion Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['education'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Education Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['username'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Username Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['password'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Password Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['country'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Country Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['mobile'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Mobile Empty.',
				'data' => ''
			);
		}
		if (empty($_POST['email'])) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Email Empty.',
				'data' => ''
			);
		}

		$emailData = $this->User->find('list',array('conditions'=>array(),'fields'=>array('User.id', 'User.email')));
		$mobileData = $this->User->find('list',array('conditions'=>array(),'fields'=>array('User.id', 'User.mobile')));
		if (in_array($_POST['email'], $emailData)) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Email Duplicate.',
				'data' => ''
			);
		}
		if (in_array($_POST['mobile'], $mobileData)) {
			$flag = false;
			$response = array(
				'code' => '200',
				'message' => 'Mobile Duplicate.',
				'data' => ''
			);
		}
		if ($flag == true) {
			$otp = $this->randomPassword(4);
			$today = date('Y-m-d H:i:s');
			$userData = array();
			$userData['User']['otp'] = $otp;
			$userData['User']['fname'] = $_POST['fname'];
			$userData['User']['lname'] = $_POST['lname'];
			$userData['User']['email'] = $_POST['email'];
			$userData['User']['mobile'] = $_POST['mobile'];
			$userData['User']['password'] = md5($_POST['password']);
			$userData['User']['country'] = $_POST['country'];
			$userData['User']['username'] = $_POST['username'];
			$userData['User']['education'] = $_POST['education'];
			$userData['User']['religion'] = $_POST['religion'];
			$userData['User']['dateofbirth'] = $_POST['dateofbirth'];
			$userData['User']['genderinterest'] = $_POST['genderinterest'];
			$userData['User']['gender'] = $_POST['gender'];
			$userData['User']['created'] = $today;
			$userData['User']['modified'] = $today;
			$transaction = $this->User->getDataSource();
			$transaction->begin($this);
			$this->User->create();
			if (!$this->User->save($userData['User'],false,array('fname','lname','email','mobile','password','country','username','education','religion','dateofbirth','genderinterest','gender','created','modified','otp'))) {
				$flag = false;
				$response = array(
					'code' => '200',
					'message' => 'User not created.',
					'data' => ''
				);
			}
			if ($flag == false) { 
				$transaction->rollback();
			}
			if ($flag == true) {
				$lastId = $this->User->getLastInsertId();
				$name = $_POST['fname'].' '.$_POST['lname'];
				$returnArray = $this->sendOtpMail($_POST['email'], $name, $otp);
				if ($returnArray['send'] == '1') {
					$transaction->commit();
					$response = array(
						'code' => '200',
						'message' => 'One time password sended to email.',
						'data' => $lastId
					);
				}
				if ($returnArray['send'] == '2') {
					$transaction->rollback();
					$response = array(
						'code' => '200',
						'message' => $returnArray['msg'],
						'data' => ''
					);
				}
			}
		}
		$this->set(array('response' => $response, '_serialize' => 'response'));
	}

	public function sendOtpMail($email, $name, $otp) { 		
			$subject = 'One Time Password';
			$content = '<p>Your one time secure password is : '.$otp.'. password valid for next 10 mins.</p>';
			App::import('Vendor', 'PHPMailer', array('file' => 'MailClassess/class.phpmailer.php'));
		    $Mail = new PHPMailer();
		    if(isset($_SERVER['HTTP_HOST']) and $_SERVER['HTTP_HOST']=="localhost"){
				$Mail->Host   = "ssl://smtp.gmail.com";
				$Mail->SMTPAuth = true;
				$Mail->Username = "gpittest@gmail.com";
				$Mail->Password = "team@xceller";
				$Mail->Port = 465;
			}else{
				$Mail->Host = "localhost";
				$Mail->SMTPAuth = false;
				$Mail->Port = 25;
			}
			$Mail->WordWrap = 50;
			$Mail->CharSet = "utf-8";
			$Mail->Mailer = "mail";
			$Mail->IsHTML(true);	
			$Mail->ClearAllRecipients();
			$Mail->ClearReplyTos();
			$Mail->PluginDir =  dirname(dirname(__FILE__)).'/Vendor/MailClassess/';
			$Mail->SMTPKeepAlive = true;
			$currentDateTime = date("Y-m-d H:i:s", strtotime('now'));
			$Mail->Priority = 3;
			$Mail->ClearAllRecipients();
			$Mail->ClearReplyTos();
			$Mail->ClearAttachments();
			$Mail->Sender = 'gpittest@gmail.com';
			$Mail->AddReplyTo('gpittest@gmail.com',"Ashu");
			$Mail->From = 'gpittest@gmail.com';
			$Mail->FromName = 'Ashu';				
			$Mail->Subject = $subject;
			$Mail->AddAddress($email, $name);
			$Mail->Body = $content;
			if($Mail->send()){
				$returnArray['send'] = '1';
				$returnArray['msg'] = '';
			}else{
				$returnArray['send'] = '2';
				$returnArray['msg'] = $Mail->ErrorInfo;
			}
		return $returnArray;
	}

	public function randomPassword($length, $available_chars = '123456789') {
    	$chars = preg_split('//', $available_chars, -1, PREG_SPLIT_NO_EMPTY);
    	$char_count = count($chars);
    	$out = '';
    	for($ii = 0; $ii < $length; $ii++) {
    		$out .= $chars[rand(1, $char_count)-1];
    	}
    	return $out;
  	}
}