<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('NpUser','NpLoginLog','StudyParticipant','NpCompanyUserSegment','NpEmailTemplate','NpUserType','User','UsersRole','Role','UsersRolesEmailTemplate');

	public function beforeFilter(){
		$this->nonAuthorizedActions = array('primary_login', 'frontend_login', 'forgotPassword', 'login', 'logOff');
		
		parent::beforeFilter();

		$this->Security->unlockedActions = array('primary_login','frontend_login','forgotPassword','returnRoles');
	}

	//login window for the admin at Primary Stage
	public function primary_login(){
		//to check who is Login and redirect as per Type
			
		$this->layout = 'login';
		if($this->Session->check('User'))
			$this->Session->destroy();
		if(!empty($this->request->data)){
			if($this->request->is('post')){
				if($this->Session->check('User'))
					$this->Session->delete('User');
				$uname = $this->stripTagsAndSpace($this->request->data['Page']['uname']);
			
				$roleFlag = false;
				$login = $this->User->find('first',array(
					'conditions'=>array(
							'User.email'=> $uname,
					),
					'joins'=>array(
						array(
							'alias' => 'UserRole',
				          	'table' => 'users_roles',
				          	'conditions' => 'User.id = UserRole.user_id'
				        ),
			        	array(
							'alias' => 'Role',
				          	'table' => 'roles',
				          	'conditions' => 'UserRole.role_id = Role.id'
			        	),
					),
					'fields'=>array('User.*','Role.role'),
					'order'=>array('Role.id'),
				));
				$userName = $login['User']['name'];
				$roleNameArr = $roleArr = array();
				$roleArr = $this->returnRoles($login['User']['id']);

				$clientIpAddress = $this->getRealIpAddr(false);
				$completeDate = date("Y-m-d H:i:s", strtotime('now'));

				foreach ($roleArr as $roleArrKey => $roleArrValue) {
					$roleNameArr[$roleArrValue['Role']['role']] = $roleArrValue['Role']['role'];
				}

				if ( in_array('rp_admin', $roleNameArr) || in_array('rp_reviewer', $roleNameArr) || in_array('rp_consultant', $roleNameArr) ) {
					$roleFlag = true;
				}
				//to check the UserName
				if(empty($this->request->data['Page']['uname']) || empty($login)){
					$this->companyMessage('Invalid username/password',0,'loginError');
				}
				elseif ( $roleFlag == false ) {
					$this->companyMessage('You have not given access of report portal, kindly contact administration.',0,'loginError');
				}
				//to check the Active Status

				elseif($login['User']['active'] == 0){
					$this->companyMessage('This user is inactve, Please contact administrator.',0,'loginError');
				}
				elseif($login['User']['active'] != 1){
					$this->companyMessage('This user is blocked, Please contact administrator.',0,'loginError');
				}

				//to check the Password
				elseif(empty($this->request->data['Page']['pword']) || md5($login['User']['pword'].$_SESSION['salt']) != trim($this->request->data['Page']['pword'])){
					$this->request->data['Page']['pword'] = '';
					$this->companyMessage('Invalid username/password',0,'loginError');
					//to check the Session
					if($this->Session->check('invalid'))
						$a = $this->Session->read('invalid');
					else
						$a = array();
					$a[] = $this->request->data['Page']['uname'];
					$this->Session->write('invalid', $a);
					$total = array_count_values($this->Session->read('invalid'));

					if(array_key_exists($this->request->data['Page']['uname'], $total)){
						if($total[$this->request->data['Page']['uname']] >= 10){
							$primData = array();
							$primData['User']['active']=2;
							$primData['User']['id'] = $login['User']['id'];

							$this->User->save($primData['User'],false,array('id','active'));
							$this->log_entry('10 Times Primary Login Fail',11);
							$a = $this->Session->read('invalid');
							foreach($a as $key=>$value){
								if($value == $this->request->data['Page']['uname'])
									unset($a[$key]);							
							}
							$this->Session->write('invalid', $a);
						}
					}
					$this->companyMessage('Invalid username/password',0,'loginError');
				}
				//to write in the Session and allow to Login
				elseif(!empty($this->request->data['Page']['uname']) && !empty($this->request->data['Page']['pword'])){
					if (isset($_SESSION['salt'])) {
						unset($_SESSION['salt']);
					}
					$this->Session->destroy();
					$this->Session->renew();
					$this->Session->write('User.id', $login['User']['id']);
					$this->Session->write('condition', '');
					$this->Session->write('User.name', $userName);
					$loginLogUser = md5($this->randomPassword(12));
					$this->Session->write('loginLogUser', $loginLogUser);
					$userId = $login['User']['id'];

					//lang setting
					$langPref = $this->getDefaultLang();
					
					$this->langKeyDefault = isset($langPref['PREFERRED']) ? $langPref['PREFERRED']['language_short_code'] : $langPref['Default']['language_short_code'];
					$this->langIdDefault = isset($langPref['PREFERRED']) ? $langPref['PREFERRED']['id'] : $langPref['Default']['id'];

					Configure::write('Config.language', $this->langKeyDefault);
					$this->Session->write('Config.language', $this->langKeyDefault);
					$this->Session->write('Config.languageId', $this->langIdDefault);

					// Login log
					$this->NpLoginLog->query("INSERT INTO np_login_logs(user_id,ip,ip_address,np_user_agent,start_time) VALUES ({$userId},INET_ATON('".$_SERVER['REMOTE_ADDR']."'),'{$clientIpAddress}','{$loginLogUser}','{$completeDate}')");
					
					return $this->redirect(array('controller' => 'companies', 'action' => 'index'),null,true);
				}
				
				$this->request->data['Page']['pword'] = '';
				$this->request->data['Page']['pword1'] = '';
			}
		}else{
			$this->Session->renew();
		}

		$this->render('primary_login',null,null);
	}

	public function frontend_login(){
		$this->layout = 'login';
		if($this->Session->check('User'))
			$this->Session->destroy();

		$data = array();
		$msg = '';
		$flag = true;

		$clientIpAddress = $this->getRealIpAddr(false);
		$completeDate = date("Y-m-d H:i:s", strtotime('now')); 

		// Get Username and password
		$username = isset($this->request->data['Page']['uname'])?$this->request->data['Page']['uname']:'';
		$password = isset($this->request->data['Page']['pword'])?$this->request->data['Page']['pword']:'';
		$salt = '';
		if (isset($_SESSION) && !empty($_SESSION['salt'])) {
			$salt = $_SESSION['salt'];
		}
		// Check if username, password empty
		if(!empty($this->request->data)){
			if ( empty($username) || empty($password) ) {
				$flag = false;
				$msg = __d('front_end', 'Please enter username/password');
			}
			$roleFlag = false;
			$cmUserData = $this->User->find('all',array(
				'conditions'=>array(
						'User.email'=> $username,
				),
				'joins'=>array(
					array(
						'alias' => 'UserRole',
			          	'table' => 'users_roles',
			          	'conditions' => 'User.id = UserRole.user_id'
			        ),
		        	array(
						'alias' => 'Role',
			          	'table' => 'roles',
			          	'conditions' => 'UserRole.role_id = Role.id'
		        	),
		        	array(
						'alias' => 'UserCompany',
			          	'table' => 'user_companies',
			          	'conditions' => 'User.id = UserCompany.user_id'
		        	),
				),
				'fields'=>array('User.*','Role.role','Role.id','UserCompany.company_id'),
				'order'=>array('User.id'),
			));
			$login = array();
			foreach ($cmUserData as $key => $value) {
				$login['User'] = $value['User'];
				$login['Role'][$value['Role']['role']] = $value['Role']['role'];
				$login['UserCompany'][$value['UserCompany']['company_id']] = $value['UserCompany']['company_id'];
			}
			if ( in_array('rp_company_admin', $login['Role']) || in_array('rp_group_company_admin', $login['Role']) || in_array('rp_segment_leader', $login['Role']) ) {
					$roleFlag = true;
			}
			//study Id
			$studyPData = $this->StudyParticipant->find('first',array(
				'conditions'=>array('company_id'=>$login['UserCompany']),
				'joins'=>array(
					array(
						'alias' => 'Study',
			          	'table' => 'studies',
			          	'conditions' => 'Study.id = StudyParticipant.study_id'
		        	),
		    	),
		    	'fields'=> array('study_id'),
		    	'order'=>array('list_year desc')
			));

			if(!empty($login)){
				$userName = $login['User']['name'];
			}

			if(empty($login)){ 
				$flag = false;
				$msg = __d('front_end', 'Email doesnot exist');
			}
			elseif ( $roleFlag == false ) {
				$flag = false;
				$msg = __d('front_end', 'You have not given access of report portal, kindly contact administration.');
			}
			elseif ($login['User']['active'] == 0) {
				$flag = false;
				$msg = __d('front_end', 'This user is inactive, Please contact administrator.');
			}
			elseif ($login['User']['active'] != 1) {
				$flag = false;
				$msg = __d('front_end', 'This user is blocked, Please contact administrator.');
			}elseif (md5($login['User']['pword']) != trim($password)) { 
				$this->request->data['password'] = '';

				//to check the Session
				if($this->Session->check('invalid'))
					$a = $this->Session->read('invalid');
				else
					$a = array();
				$a[] = $username;
				$this->Session->write('invalid', $a);
				
				$total = array_count_values($this->Session->read('invalid'));			
				if(array_key_exists($username, $total)){
					if($total[$username] >= 10){
						$primData = array();
						$primData['User']['active']=2;
						$primData['User']['id'] = $login['User']['id'];
						$this->User->save($primData['User'],false,array('id','active'));
						$this->log_entry('10 Times Login Fail',11);
						$a = $this->Session->read('invalid');
						foreach($a as $key=>$value){
							if($value == $username)
								unset($a[$key]);							
						}
						$this->Session->write('invalid', $a);
					}
				}
				$flag = false;
				$msg = __d('front_end', 'Invalid Password');
			}
			elseif (!empty($username) && !empty($password)) {
				if (!in_array('rp_group_company_admin',$login['Role'])) {
					$participantData = $this->StudyParticipant->find('first', array('conditions' => array('StudyParticipant.company_id' => $login['UserCompany'], 'StudyParticipant.is_report_portal_allowed' => 1), 'joins' =>array(array('alias'=>'Study','table'=>'studies','type' => 'inner','conditions'=>'StudyParticipant.study_id = Study.id')),
				    'fields' => array('StudyParticipant.id', 'StudyParticipant.study_id'),'order' => array('Study.list_year DESC'),));
					$latestStudyYear = $participantData['StudyParticipant']['study_id'];
				}
				else{
					$participantData = $this->StudyParticipant->find('all', array('conditions' => array('StudyParticipant.company_id' => $login['UserCompany'], 'StudyParticipant.is_report_portal_allowed' => 1), 'joins' =>array(array('alias'=>'Study','table'=>'studies','type' => 'inner','conditions'=>'StudyParticipant.study_id = Study.id')),
				    'fields' => array('StudyParticipant.id', 'StudyParticipant.study_id'),'order' => array('Study.list_year DESC')));
					$latestStudyYear = $participantData[0]['StudyParticipant']['study_id'];
				}
				if (in_array('rp_segment_leader',$login['Role'])) {
					$segments= $this->NpCompanyUserSegment->find('first',
						array('conditions'=> 
							array('NpCompanyUserSegment.user_id' => $login['User']['id']
							),
							'fields' => array('NpCompanyUserSegment.id', 'NpCompanyUserSegment.segment_id', 'StudyParticipant.id', 'StudyParticipant.study_id'),
						 	'order' => array('NpCompanyUserSegment.id' => 'asc'),
							'joins' =>array(
								array('alias'=>'StudyParticipant',
							  		'table'=>'study_participants',
							 		'type' => 'inner',
							  		'conditions'=>'StudyParticipant.id = NpCompanyUserSegment.study_participant_id'
							  	)
							),
					    )
				
					);

					//If a user is not a company_admin he is not allowed to without segement
					$segmentId = '';
					if ($flag ==true && empty($segments)) {
						$flag = false;
						$msg = __d('front_end', 'Invalid user, segment permissions not given');
					}else{
						$latestStudyYear = $segments['StudyParticipant']['study_id'];
						$segmentId = $segments['NpCompanyUserSegment']['segment_id'];
					}
					
				}

				unset($_SESSION['salt']);
				$this->Session->destroy(); 
				$this->Session->renew();$this->Session->write('User.id', $login['User']['id']);
				$this->Session->write('User.study_id', $latestStudyYear);
				$this->Session->write('User.name', $userName);
				$loginLogUser = md5($this->randomPassword(12));
				$this->Session->write('loginLogUser', $loginLogUser);
				$data['user_id'] = $login['User']['id'];
				$data['study_id'] = $studyPData['StudyParticipant']['study_id'];
				if($flag == true){
					if (!in_array('rp_company_admin',$login['Role'])) {
						$data['segment_id'] = $segmentId;
					} else {
						$data['segment_id'] = '0';
					}
				$resetArray = $login['UserCompany'];
				reset($resetArray);
				$companyId =  (!empty($login['Role']) and (in_array('rp_company_admin', $login['Role']) || (in_array('rp_segment_leader', $login['Role']) ))) ? key($resetArray) : 0 ;
				$rp_group_company_admin_flag =  (!empty($login['Role']) and (in_array('rp_group_company_admin', $login['Role'] ))) ? 1 : 0 ;
				$this->Session->write('UserSegment.segment_id', $data['segment_id']);

				$forSurveyData = $this->StudyParticipant->find('first', array('conditions' => array('StudyParticipant.company_id' => $companyId, 'StudyParticipant.study_id' => $latestStudyYear),'fields' => array('StudyParticipant.id', 'StudyParticipant.survey_id')));

					$user = array(
						'id' => $login['User']['id'],
						'study_id'=>$latestStudyYear,
						'company_id'=> $companyId,
						'segmentId'=> $data['segment_id'],
						'survey_id'=> $forSurveyData['StudyParticipant']['survey_id'],
						'group_company_admin_flag' => $rp_group_company_admin_flag,

					);
					$path = '/';
					$json = json_encode($user);
					/* Since server is creating secure and http only cookie, Angular at frontend is not able to access it. So keeping same details in session so that later it can be access anywhere.*/
					$this->Session->write('userDetails', $json);
					$user = serialize($user); 
				}
				

				$userId = $login['User']['id'];
				
				
				//lang setting
				$langPref = $this->getDefaultLang();
				$this->langKeyDefault = isset($langPref['PREFERRED']) ? $langPref['PREFERRED']['language_short_code'] : $langPref['Default']['language_short_code'];
				$this->langIdDefault = isset($langPref['PREFERRED']) ? $langPref['PREFERRED']['id'] : $langPref['Default']['id'];

				Configure::write('Config.language', $this->langKeyDefault);
				$this->Session->write('Config.language', $this->langKeyDefault);
				$this->Session->write('Config.languageId', $this->langIdDefault);
			}
			
				if($flag == true){
					
					$this->NpLoginLog->query("INSERT INTO np_login_logs(user_id,ip,ip_address,np_user_agent,start_time) VALUES ({$userId},INET_ATON('".$_SERVER['REMOTE_ADDR']."'),'{$clientIpAddress}','{$loginLogUser}','{$completeDate}')");
					$this->redirect('/files/ui-component/dist/#/resumen');
				}
				else{
					$this->companyMessage($msg,0,'loginErr');
				}
		}
			$this->render('frontend_login',null,null);
		
	}
	//logout function for admin and other users
	public function logoff() {
		if($this->Session->check('User')){
			$userId = $this->Session->read('User.id');
			$loginLogUser = $this->Session->read('loginLogUser');
			if(in_array('rp_admin',$this->userRoles)|| in_array('rp_consultant', $this->userRoles)||in_array('rp_reviewer', $this->userRoles)){
				$this->Session->destroy();
				$this->NpLoginLog->query("INSERT INTO np_login_logs(user_id,end_time,np_user_agent) VALUES ({$userId},now(),'{$loginLogUser}') on duplicate key update `end_time`= values(`end_time`)");
				$this->redirect('/admin');		
			}
			if(in_array('rp_group_company_admin', $this->userRoles) || in_array('rp_company_admin', $this->userRoles) || in_array('rp_segment_leader', $this->userRoles)){
				$this->Session->destroy();
				$this->NpLoginLog->query("INSERT INTO np_login_logs(user_id,end_time,np_user_agent) VALUES ({$userId},now(),'{$loginLogUser}') on duplicate key update `end_time`= values(`end_time`)");
				$this->redirect('/');
			}
		}else{
			$this->redirect('/');
		}
	}

	// if user is not valid then refresh and redirect him to login page
	public function refresh($message, $page=null){
		$this->layout = "default";	
		$this->set('message',$message);
		$this->set('page',$page);
		if (!$this->RequestHandler->isAjax()){
			$this->render('refresh1',null,null);
		}else{
			$this->render('refresh',null,null);
		}
	}

	public function forgotPassword($tempId = null){

		if (!empty($this->request->data)) {

			$submittedEmail = $this->request->data['ForgotPassword']['email'];
		
			//$existingData = $this->User->find('first',array('conditions'=>"NpUser.email='$submittedEmail'", 'fields'=>array('id','email','name')));
			$roleArrr = array();
			$cmUserData = $this->User->find('all',array(
					'conditions'=>array(
							'User.email'=> $submittedEmail,
					),
					'joins'=>array(
						array(
							'alias' => 'UserRole',
				          	'table' => 'users_roles',
				          	'conditions' => 'User.id = UserRole.user_id'
				        ),
			        	array(
							'alias' => 'Role',
				          	'table' => 'roles',
				          	'conditions' => 'UserRole.role_id = Role.id'
			        	),
			        	array(
							'alias' => 'UserCompany',
				          	'table' => 'user_companies',
				          	'conditions' => 'UserCompany.user_id = User.id'
			        	)
					),
					'fields'=>array('User.*','Role.role','Role.id'),
					'order'=>array('User.id'),
				));

			$assignCompanyArr = array();
			foreach ($cmUserData as $key => $value) {
				$existingData['User'] = $value['User'];
				$existingData['Role'][$value['Role']['role']] = $value['Role']['role'];
				$roleArrr  = $value['Role']['id'];
				$assignCompanyArr[$value['UserCompany']['company_id']]  = $value['UserCompany']['company_id'];
			}

			$existingName = $existingData['User']['name'];
			$assignCompanyStr = implode(",", $assignCompanyArr);

			$frontEndArr = array('rp_group_company_admin','rp_segment_leader','rp_company_admin');
			$backEndArr = array('rp_admin','rp_consultant','rp_reviewer');

			$flag=true;
			if(empty($existingData)){
				$flag = false;
				$msg = __('Please enter valid email id.');	
			}
			else{
				$validFrontUser = !empty(array_intersect($existingData['Role'], $frontEndArr));
				$validAdminUser = !empty(array_intersect($existingData['Role'], $backEndArr));
				
				if( $tempId == 1 && $validFrontUser != true ){
					$flag = false;
					$msg = __('Invalid Email');
				}
				if( $tempId == 4 && $validAdminUser != true ){
					$flag = false;
					$msg = __('Invalid Email');
				}
			}
			
			$user = $this->User->getDataSource();
			$user->begin($this);			
			if($flag==true && !empty($existingData)){
				$existingEmail = $existingData['User']['email'];
				$newPassword = 'NP#';
				$newPassword .= $this->randomPassword(8);
				$newPassword .= '$';
				
				//TO UPDATE User
				$this->NpUser->create();
				$this->request->data['User']['id'] = $existingData['User']['id'];
				$this->request->data['User']['pword'] = md5($newPassword);
				if(!$this->User->save($this->request->data['User'],true,array('id','pword'))){
				$flag = false;
				return $this->setError(__('Password could not be changed.', true));
				}
				if($flag==true){
					if (!empty($existingData['User']['id'])) {
			       	$returnArray = $this->sendLoginMail($existingData['User']['id'],'GPTW Chile',$tempId,$newPassword);
			       	$msg = __("New Password has been sent on your mail id.");
					$user->commit($this);
					$this->logEntry("Forget Password of User {$existingName}.", 2, $assignCompanyStr);
					$flag = $returnArray;
					//Redirection
					}										
				}
			}
			if ($flag == true) { 
				$user->commit($this);				
				if($tempId == 1 ){
					$this->companyMessage($msg,3,'loginErr');
					return $this->redirect(array('action' => 'frontend_login'));
				}
				else{					
					$this->companyMessage($msg,3,'loginError');
					return $this->redirect(array('action' => 'primary_login'));
				}
			}
			else {
				$user->rollback($this);
				if($tempId == 1 ){
					$this->companyMessage($msg,0,'loginErr');
					return $this->redirect(array('action' => 'frontend_login'));
				}
				else{
					$this->companyMessage($msg,0,'loginError');
					return $this->redirect(array('action' => 'primary_login'));
				}
			}
		}
		$this->render('forgotPassword');
	}

	public function sendLoginMail($npUserLastId = null,$admin ,$templateId = null,$password = '') {
		
		$data = array('id' => $npUserLastId);
		// Initialization of Data
		$this->userId =  $npUserLastId;
		$id = $this->userId;
		$options = array(
			'fields' => array('User.id'),
			'conditions' => array(
				'id' => $id,
			),
		);
		$npUserData = $this->User->find('all', $options);

		if (empty($npUserData)) {
			return $this->setError('Invalid User Id.');
		}
		$emailData= $this->NpEmailTemplate->find('first', array('conditions'=>array('NpEmailTemplate.id'=>$templateId )));
		$subject = $emailData['NpEmailTemplate']['subject'];
		$content = $emailData['NpEmailTemplate']['content'];

		$flag = true;
		$msg = __('Login details has been sent successfully.');
		$fields = array('User.name','User.email', 'User.phone');
		$userData = $this->User->read($fields,$id);
		//Data to save
		$userData['User']['subject']=$subject;
		$userData['User']['content']=$content;
		$userData['User']['user_id']=$id;
		$userData['User']['password']= $password;
		$userData['User']['created'] = date('Y-m-d H:i:s');
		$result = array();
	    $ReceiverArr['user_id'] = $id;
	    $ReceiverArr['tempName'] = $emailData['NpEmailTemplate']['name'];
	    $ReceiverArr['user_name'] = $admin;
	    $to[] = $userData['User'];
	    $ReceiverArr['to'] = json_encode($to);
	    $ReceiverArr['content_data'] = $userData['User'];
		App::import('Vendor', 'SendEmail', array('file' => 'classes/SendEmail.php'));
	    $sendingMail = new SendEmail();
	    $result = $sendingMail->send_email_process($ReceiverArr);
	    $emailToDetails = $result['details'];
	    $lastJobDetailId = $result['lastInsertId'];
	    if (!empty($lastJobDetailId)) {
	      	App::import('Controller', 'SaveEmailDetails');
	      	$EventController = new SaveEmailDetailsController;
	      	$returnArr = $EventController->sendMailProcess($emailToDetails,$lastJobDetailId);
			// Redirection
			if ($flag == true) {
				//$message= __('Login Details Sent to newly created User');
				return true;
			} 
			else {
				return false;
					//$message= __('Failed to send Login Details');
			}
		}
	}

	public function login($value='') {

		if(!$this->RequestHandler->isAjax()){ //deprecated 
			$this->layout = 'login';
		}

		$this->render('login');
	}

	public function index_list(){

		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'admin-no-year';
		}

		 $this->render('index_list');

	}

	public function business_list(){

		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'admin-no-year';
		}

		 $this->render('business_list');

	}

	public function search(){

	}
	public function edit_user(){
		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'admin-no-sidebar';
		}

		 $this->render('edit_user');
	}


	public function create_index(){
		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'admin-no-sidebar';
		}

		 $this->render('create_index');
	}

	public function create_user(){
		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'admin-no-sidebar';
		}

		 $this->render('create_user');
	}

	public function company_data(){
		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'admin-no-sidebar';
		}

		 $this->render('company_data');
	}

	public function benchmarks_authorization(){
		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'admin0';
		}

		 $this->render('company_data');
	}
	public function my_profile(){
		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'admin-no sheader';
		}

		 $this->render('my_profile');
	}

	/* While login user details are kept in session.
	   This function returns those.
	*/
	public function userDetails()
	{
		$this->autoRender = false;
		if ($this->Session->check('userDetails')) {
			$data = $this->Session->read('userDetails');
		}

		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data
			);
		
		return json_encode(array('response'=>$response),TRUE);
		
	}
	function getUserRoles(){
		return $this->userRoles;
	}

	function returnRoles($userId){
		$roleArr = $this->Role->find('all', array('conditions' => array('UserRole.user_id' => $userId), 'joins' =>array(array('alias'=>'UserRole','table'=>'users_roles','type' => 'inner','conditions'=>'Role.id = UserRole.role_id')),'fields' => array('Role.id', 'Role.role')));
		return $roleArr;
	}

	/**
* Get the IP the client is using, or says they are using.
*
* @param bool $safe Use safe = false when you think the user might manipulate their HTTP_CLIENT_IP
*   header. Setting $safe = false will also look at HTTP_X_FORWARDED_FOR
* @return string The client IP.
*/
public function getRealIpAddr($safe = true) {
	if (!$safe && env('HTTP_X_FORWARDED_FOR')) {
		$ipaddr = preg_replace('/(?:,.*)/', '', env('HTTP_X_FORWARDED_FOR'));
	} elseif (!$safe && env('HTTP_CLIENT_IP')) {
		$ipaddr = env('HTTP_CLIENT_IP');
	} else {
		$ipaddr = env('REMOTE_ADDR');
	}
	return trim($ipaddr);
}

}