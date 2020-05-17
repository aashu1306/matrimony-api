<?php
App::uses('AppController', 'Controller');

use \Firebase\JWT\JWT;
/**
 * Users Controller
 */
class UsersController extends AppController {
	var $uses = array('NpUserType','NpUser','Company','NpUserPostion','NpUserCompany','NpEmailTemplate','User','UserCompany','UsersRole','Configuration');

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('index','edit_user','create_user','user_profile','send_logins','getToken');
	}
	//----------------------------------index page------------------------------------
	public function index(){
		return $this->redirect(array('controller' => 'companies', 'action' => 'index'),null,true);
		$this->checkGeneric(array('rp_admin'));

		if(!$this->RequestHandler->isAjax()){
			$this->layout = 'index';
		}
		if (!empty($this->request->data)) { //$this->db($this->request->data);exit;
			if(!empty($this->request->data['StudyParticipant']['company_name'])){
				$this->request->params['named']['name'] = $this->stripTagsAndSpace($this->request->data['StudyParticipant']['company_name']);
			}
			if(!empty($this->request->data['NpUserType']['type'])){
				$this->request->params['named']['name'] = $this->stripTagsAndSpace($this->request->data['StudyParticipant']['company_name']);						
			}

		}

		$condition = "NpUser.study_participant_id = 0 and NpUser.active in ( 0, 1) ";

		if(!empty($this->request->params['named'])){
			if(!empty($this->request->params['named']['name']) /*|| !empty($this->request->params['named']['name'])*/){
				if($condition !=''){
					$condition .=' and ';
				}
				 $condition .= ' ( NpUser.name LIKE "%'.$this->request->params['named']['name'].'%" || NpUserType.type LIKE "%'.$this->request->params['named']['name'].'%" )';
			}

		}
	
		$this->NpUser->bindModel(array('belongsTo' => array('NpUserType'=>array('conditions'=>array(),'fields'=>array('id','type')))),false);
		$this->paginate = array('conditions'=>array($condition), 'limit'=>'20', 'order'=>'NpUser.id DESC');
		$npUserData = $this->paginate($this->NpUser);
		$totalNpUserData = $this->NpUser->find('count',array('conditions'=>array($condition)));
		
		$this->set('npUserData', $npUserData);
		$this->set('totalNpUserData',$totalNpUserData);
		$this->render('index');
	}		

	function getType($npUser=null){ //print_r($npUser);
		$npUserData=$this->NpUserType->find('first',array('conditions'=>array('NpUserType.id'=>$npUser),'fields'=>array('type')));
		//print_r($npUserData);
		$npUserType= $npUserData['NpUserType']['type'];
		return $npUserType;
		$this->set('npkpiData', $npkpiData);
	}
		//------------------------------Create User----------------------------------
	public function create_user(){
		$this->checkAdmin();
		$user_id = $this->Session->read('User.id');
		if(!$this->RequestHandler->isAjax()){

			 $this->layout = 'user';
		}
		//$this->db($this->request->post);
		//---------------------------to display user type------------------------------------------
		$type = $this->NpUserType->find('list', array('conditions'=>array('NpUserType.frontend_type'=>0),
       	'fields' => array('NpUserType.id','NpUserType.type'),
       	));
		
		 $this->set('type', $type);

		 //---------------------------to display user position------------------------------------------
		$position = $this->NpUserPostion->find('list', array(
      	 'fields' => array('NpUserPostion.id','NpUserPostion.position'),
       	));
		
		 $this->set('position', $position);

		 //---------------------------to display companiies------------------------------------------
		$company = $this->Company->find('list', array(
       	'fields' => array('Company.id','Company.company_name'),
       	'conditions' => array('Company.status' => 1)));
		 // print_r($statements);
		 $this->set('company', $company);
		 
		//-----------------------------------------------------------------------------

		if ($this->request->is('post')) { //$this->db($this->request->data);
			//exit;
			$flag = true;
			$msg = __('You have successfully created new user.');
			$today = date('Y-m-d H:i:s');
			$emailData = $this->NpUser->find('first',array('conditions'=>array('NpUser.email'=>$this->request->data['NpUser']['email']),'fields'=>array('email')));
			//debug($emailData);exit;
			
			$npUserData = array();
			
			$Companies = isset($this->request->data['NpUser']['check'])?$this->request->data['NpUser']['check']:array();
			$npUserData['NpUser']['study_participant_id'] = 0;
			$npUserData['NpUser']['name'] = $this->request->data['NpUser']['name'];
			$npUserData['NpUser']['email'] = $this->request->data['NpUser']['email'];
			$npUserData['NpUser']['pword_txt'] = $this->request->data['NpUser']['pword'];
			$npUserData['NpUser']['pword'] = md5($this->request->data['NpUser']['pword']);
			$npUserData['NpUser']['phone'] = $this->request->data['NpUser']['phone'];
			$npUserData['NpUser']['active'] = 1;
			$npUserData['NpUser']['np_user_type_id'] = $this->request->data['NpUser']['type'];
			$npUserData['NpUser']['np_user_position_id'] = $this->request->data['NpUser']['position'];
			$npUserData['NpUser']['created'] = $today;
			if ($flag == true && !empty($emailData)) {
				$flag = false;
				$msg = __('Please enter unique email id.');
			}//debug($this->request->data['NpUser']['email']);exit;
			$transaction = $this->NpUser->getDataSource();
			$transaction->begin($this);

			$this->NpUser->create();

			if ($flag == true && !$this->NpUser->save($npUserData['NpUser'],false,array('study_participant_id','name','email','pword_txt','pword','phone','active','np_user_type_id','np_user_position_id','created'))) {
				$flag = false;
				$msg = __('User couldn\'t be created');
			}
			else {
				$npUserLastId= $this->NpUser->getLastInsertId();
				$npUserData = array();
				if (!empty($this->request->data['NpUser']['check'])) {
					foreach ($this->request->data['NpUser']['check'] as $company =>$checked) {
						if ($checked == 0) continue;
						$npUserData [] = array(
							'user_id' => $npUserLastId,
							'company_id' => $company,
							'created' => $today
						);

					}			
				}
				// foreach ($Companies as $row => $check) { //print_r($check);
				// 	if ($check == 0) continue;

				// 	$npUserData[] = array(
				// 		'np_user_id' => $npUserLastId,
				// 		'company_id' => $row,
				// 		'created' => $today,
				// 	);
				// }
				//$this->db($npUserData);//exit;
				if (count($npUserData) > 0) {
					$this->NpUserCompany->deleteAll(array('NpUserCompany.user_id' => $npUserLastId));
					if ($flag == true && !$this->NpUserCompany->saveMany($npUserData)) {
						$flag = false;
						$msg = __('User is not added.');
					}
					

				}
			}
			if($flag == true && !$this->logEntry("created user at admin side.",1,0)){
      			$flag = false;
      			$msg = __('Unable to create user.');
      		}
			if ($flag == true) { 
				$transaction->commit();
				$this->companyMessage($msg,3,'user');
				return $this->redirect(array('action' => 'index'));
			}else {
				$transaction->rollback();
				$this->companyMessage($msg,0,'addUser');
			}
		}
	
		$this->set('title_for_layout', __('Create User'));
	 	$this->render('create_user');
	}

		//--------------------------------Edit User------------
	public function edit_user($user_id){

		if(!$this->RequestHandler->isAjax()){
			$this->layout = 'user';
		}
		
		//$this->db($this->request->post);
		//---------------------------to display user type------------------------------------------
		$type = $this->NpUserType->find('list', array('conditions'=>array('NpUserType.frontend_type'=>0),
       	'fields' => array('NpUserType.id','NpUserType.type'),
       	));
		
		 $this->set('type', $type);

		 

		 //---------------------------to display companiies------------------------------------------
		$company = $this->Company->find('list', array(
       	'fields' => array('Company.id','Company.company_name'),
       	'conditions' => array('Company.status' => 1)));
		 // print_r($statements);
		 $this->set('company', $company);

		 //---------------------------to display user position------------------------------------------
		$position = $this->NpUserPostion->find('list', array(
      	 'fields' => array('NpUserPostion.id','NpUserPostion.position'),
       	));
		
		 $this->set('position', $position);
		//-----------------------------------------------------------------------------

		if ($this->request->is('post')) { 
			$flag = true;
			$msg = __('User has been saved.');
			$today = date('Y-m-d H:i:s');
			$npData = $this->NpUser->read(array('id','name','active'),$user_id);
			//debug($npData) ;exit;
		
			if (empty($npData)) {
				$flag = false;
				$msg = __('Invalid user!!');
				
			}
			if($npData['NpUser']['active'] != 1){
		    	$flag = false;
		    	$msg = __('%s is not an active user',$npData['NpUser']['name']);
			}

			$npUserData = array();
			$Companies = isset($this->request->data['NpUser']['check'])?$this->request->data['NpUser']['check']:array();
			$npUserData['NpUser']['id'] = $this->request->data['NpUser']['id'];
			$npUserData['NpUser']['name'] = $this->request->data['NpUser']['name'];
			$npUserData['NpUser']['email'] = $this->request->data['NpUser']['email'];
			$npUserData['NpUser']['phone'] = $this->request->data['NpUser']['phone'];
			$npUserData['NpUser']['np_user_type_id'] = $this->request->data['NpUser']['type'];
			$npUserData['NpUser']['np_user_position_id'] = $this->request->data['NpUser']['position'];

			$npUserData['NpUser']['created'] = $today;

			$transaction = $this->NpUser->getDataSource();
			$transaction->begin($this);
			$this->NpUser->create();
			if ($flag == true && !$this->NpUser->save($npUserData['NpUser'],false,array('id','np_user_type_id','name','email','phone','np_user_position_id','created'))) {
				$flag = false;
				$msg = __('User couldn\'t be created');
			}else {
				$npUserLastId= $this->NpUser->getLastInsertId();

				// $npUserData = array();
				// foreach ($Companies as $row => $check) { //print_r($check);
				// 	if ($check == 0) continue;

				// 	$npUserData[] = array(
				// 		'np_user_id' => $this->request->data['NpUser']['id'],
				// 		'company_id' => $row,
				// 		'created' => $today,
				// 	);
				// }
				$npUserData = array();
				if (!empty($this->request->data['NpUser']['check'])) {
					foreach ($this->request->data['NpUser']['check'] as $company =>$checked) {
						if ($checked == 0) continue;
						$npUserData [] = array(
							'user_id' => $this->request->data['NpUser']['id'],
							'company_id' => $company,
							'created' => $today
						);

					}		

				}
				//var_dump($npUserData); exit;
				if (count($npUserData) > 0) {
					if($flag == true && !$this->NpUserCompany->deleteAll(array('NpUserCompany.user_id' =>$this->request->data['NpUser']['id']))){
						$flag = false;
					}

					if ($flag == true && !$this->NpUserCompany->saveMany($npUserData)) {
						$flag = false;
						$msg = __('User is not added.');
					}

				}
			}
			if($flag == true && !$this->logEntry("Edited user at admin side.",1,0)){
      			$flag = false;
      			return $this->setError(__('Edited user at admin side'));
     		 }

			if ($flag == true) {
				$transaction->commit();
				$this->companyMessage($msg,3,'user');
				return $this->redirect(array('action' => 'index'));
			}else {
				$transaction->rollback();
				$this->companyMessage($msg,0,'user');
			}
		}
		$this->set('position', $position);
		//-------------------------------------------------------------------------------
		if(empty($this->request->data)) {
			$this->request->data = $this->NpUser->read(null, $user_id);
			$checkData = $this->NpUserCompany->find('all',array('conditions' => array('NpUserCompany.user_id' => $user_id)));
			$a = array();
			//debug($checkData);
			foreach ($checkData as $key => $value)  {
				$this->request->data['NpUser']['check'][$key] = $value['NpUserCompany']['company_id'];
			}
		}

	    $this->set('user_id',$user_id);
		$this->set('title_for_layout', __('Edit User'));
		$this->render('edit_user');
	}

		//---------------------------Delete Method-------------

	function delete($user_id = null){ //echo $np_User_id ;
		$this->checkAdmin();
		$flag = true;
		$msg = "";
		$npUserData = $this->NpUser->read(null,$user_id);
		//echo $npUserData ;exit;
	
		if (empty($npUserData)) {
			$flag = false;
			$msg = __('Record Deleted Successfully!!');
			
		}

		/*if($userData['NpUser']['active'] != 1){
	    	$flag = false;
	    	$msg = __('%s is not an active user',$userData['NpUser']['name']);
		}*/

		//-----------------Delete NpKpiDetail Table's Records-----------------------------	
			// $conditions = array('NpUserCompany.np_user_id' => $np_User_id );
			// if ($flag == true && !$this->NpUserCompany->deleteall($conditions)) { 
			// 	$flag = false;
			// 	$msg = __('Error while Emptying NpUserCompany Table!!');
				
			// }
			// if (empty($npUserData)) {
			// 	$flag = false;
			// 	$msg = __('Empty!!');			
			// }
		//-----------------Delete NpUser Table's Records-----------------------------
			//$this->NpUser->id = $np_User_id;
			//$delData = array();
			$delData = array(
					'id' => $user_id,
					'email' => null,
					'active' => 3,
				);
			//$this->NpUser->email = $np_email = '';
			if ($flag == true && !$this->NpUser->save($delData,false,array('id','email','active'))) {
				$flag = false;
				$msg = __('Error while deleting user!!');				
			}
		//---------------------Check if Data is Deleted-----------------------------
			if($flag == true){
				$msg = __('Successfully Deleted Record .');
			}
		//--------------------Display Flash Message----------------------------------
		if($flag == true && !$this->logEntry("Deleted user.",1,0) ){

      		$flag = false;
      		$msg = __('Unable to delete user.');$flag = false;
      	}
      	if($flag == true && !$this->logEntry(" Email id {$npUserData['NpUser']['email']} of user {$npUserData['NpUser']['name']} is now changed to null.",1,0)){
      		$flag = false;
      		$msg = __('Unable to set email = null.');
      	}
			if ($flag == true) {
				$this->companyMessage($msg,3,'user');
			}else {
				$this->companyMessage($msg,0,'user');
			}
			
			return $this->redirect(array('action' => 'index'));
	
		
	}

		//-----------------------------------User Profile----------------------------------------
	public function user_profile(){
		//$this->checkAdmin();
		$user_id = $this->Session->read('User.id');
        $npUserType = $this->Session->read('User.type');
		$this->checkGeneric(array('rp_admin','rp_consultant','rp_reviewer'));
		$this->set('userTypeData',$this->userRoles);
		if(!$this->RequestHandler->isAjax()){
			$this->layout = 'user_profile';
		}
		
		if(empty($this->request->data)) {

		   	$this->request->data = $this->User->read(null, $user_id);
		   	$userTypeData = $this->UsersRole->find('list',array(
			'conditions'=>array(
				'UsersRole.user_id'=>$user_id,
			),
			'joins'=>array(
		        array(
					'alias' => 'Role',
		          	'table' => 'roles',
		          	'conditions' => 'UsersRole.role_id = Role.id'
		        ),
			),
			'fields'=>array('Role.id','Role.display_name'),
			'order'=>array('Role.id')
			));
		   	
		   	//$this->request->data['User']['type'] = $type['NpUserType']['type'];

		   	 //$position =$this->NpUserPostion->find('first',array('conditions'=>array('NpUserPostion.id'=> $this->request->data['NpUser']['np_user_position_id']) ,'fields'=>'position'));
		   	 
		   	//$this->request->data['User']['type'] = ucwords($type['NpUserType']['type']);
		   	//$this->request->data['User']['position'] = ucwords($position['NpUserPostion']['position']);
		   	 // print_r($this->request->data);
		   	 // exit;
		}
		$this->set('userTypeData',$userTypeData);
	    $this->set('user_id',$user_id);
	    
		$this->render('user_profile');
	}
	//--------------------------Upload------------------------------------------
	public function upload_photo($userId = null ){
		//$this->checkAdmin();
		$userId = $this->Session->read('User.id');
        $this->checkGeneric(array('rp_admin','rp_consultant','rp_reviewer'));
		if (!empty($this->request->data)) { //debug($this->request->data);

			$this->User->recursive = -1;
			$npUserData = $this->User->read(null,$userId);
			
			 $flag = true;
			 $msg = __('You have successfully updated your profile photo.');

			// Load S3 Component
			$this->S3 = $this->Components->load('S3', array('apiType' => AMAZON_S3_API_TYPE));

			//VALIDATION
			if(empty($this->request->data['upload_photo']["photo"]['name'])) { //debug($this->request->data);exit;
				$flag = false;
				$msg = __('Error while updating your profile photo. Empty Photo was Sent');
			}

			if($flag == true){ 
				$size = '5242880';// 5 MB
				$photoTempName = $this->request->data['upload_photo']["photo"]['tmp_name'];
				$extensionName = $this->request->data['upload_photo']["photo"]['name']; 
				$fileType = $this->request->data['upload_photo']["photo"]['type'];
				$fileSize = $this->request->data['upload_photo']["photo"]['size'];
				
	        	$pathInfoExtension = pathinfo($extensionName); //debug($pathInfoExtension);	
				$fileName = $pathInfoExtension['filename']; //debug($fileName);
				$extension = strtolower($pathInfoExtension['extension']);
				$imgExtensionList = array('jpg','jpeg','png','gif','tif','tiff');
				#@ 11/02/2016 by Vishal, Add File Extensions
				$fileExtensionList = array();
				$extensionList = array_merge($imgExtensionList,$fileExtensionList);

				$originalWidth = $originalHeight = 0;
				if (in_array($extension,$imgExtensionList)) {
					// Add Image Size Validation
					$image_params = getimagesize($photoTempName);
					$originalWidth = $image_params[0];
					$originalHeight = $image_params[1];
				}

				// Size Check
				if($fileSize > $size){
					$flag = false;
					$msg = __('Uploaded image size must be less than 5 MB.');
				}

				// Extension check
				if($flag == true && !in_array($extension,$extensionList)){
					$flag = false;
					$msg = __('Invalid File Extension.');//'Invalid File Extension.';
				}

				// Extension check
				if($flag == true && ($originalWidth > $originalHeight)){
					$flag = false;
					$msg = __('Please upload a portrait size image.');//'Please upload a portrait size image.';
				}
			}

			if($flag == true){
					
				// $newFileName = $this->generateRandomString(15);
					
				// example: Build the url that should be used for this file

				$remotePath = "files/NpUser/".$userId; 
				$localPath = $this->get_resized_image($photoTempName); //debug($photoTempName);
				
				if ($flag == true && !$this->S3->upload($localPath, $remotePath)) { //debug($this->request->data); exit;
					// Usually you don't need any message when everything is OK.
					$flag = false;
					$msg = __('Failed to upload your Profile photo');
				}else {

					// Update Photo Flag
					$this->User->set(array('photo_url' => 1));
					$this->User->save();
					$this->logEntry("profile photo uploaded.",1,0);

					// Delete temp file
					@unlink($localPath);
				}
			}

			if ($flag == true) {
				$this->companyMessage($msg,3,'profile');
			} else {
				$this->companyMessage($msg,0,'profile');
			}

			// Unload S3
			$this->Components->unload('S3');

			return $this->redirect(array('action' => 'user_profile', $userId));
		}

		$this->render('upload_photo');
	}
	//-----------------------------Display Profile Photo-------------------------------

	public function photo_url($userId = null ) {
		
		$this->autoRender = false;

		$url = Router::url('/', true).'img/default-user.png';

		$filePath = ''; //$this->Html->url('/img/default-user.png',true);


		// Load S3 Component
		$this->S3 = $this->Components->load('S3', array('apiType' => AMAZON_S3_API_TYPE));

		$this->User->recursive = -1;
		$npUserData = $this->User->read(array('User.id','User.photo_url'),$userId);
		 $this->set('npUserData',$npUserData);
		if (empty($npUserData) || (isset($npUserData['User']['photo_url']) && $npUserData['User']['photo_url'] == 0)) {
			return $url;
		} else {
			$npUser_id = $npUserData['User']['id'];

			$filePath = 'files/NpUser/'.$npUser_id;  // Preapare Paths for S3
		}

		$url = $this->S3->get_authenticated_url($filePath);
		return $url;
		// Unload S3
		$this->Components->unload('S3');
		
		
	}
		function send_logins($userId = null ){
		//$this->checkAdmin();
		$this->autoRender = false;
		$npUserId = $this->Session->read('User.id');
		$npUserType = $this->Session->read('User.type');

		
		//if (!empty($this->request->data)) { 
			$flag = true;
			$msg = __('Login details has been sent successfully.');

			//new

			$fields = array('NpUser.name','NpUser.email', 'NpUser.pword_txt AS password', 'NpUser.np_user_type_id', 'NpUser.phone','NpUser.study_participant_id','NpUser.active');
		    $userData = $this->NpUser->read($fields,$userId);
		    $user_type = $npUserType;
		    $type = $userData['NpUser']['np_user_type_id'];
		    if($userData['NpUser']['active'] != 1){
		    	$flag = false;
		    	$msg = __('%s is not an active user',$userData['NpUser']['name']);
		    }
		    //$templateId = 5;
		 	if($type == 1){
		    	$templateId = 3;
		    }
		    elseif($type == 4){
		    	$templateId = 9;
		    }
		    elseif($type == 3){
		    	$templateId = 10;
		    }
		    elseif($type == 2){
		    	$templateId = 2;
		    }
		    elseif($type == 5){
		    	$templateId = 7;
		    }
		    elseif($type == 6){
		    	$templateId = 8;
			}
			elseif($type == 12){
		    	$templateId = 7;
		    }
		    else{
		    	$templateId = null;
		    }
		    $studyParticipantId = $userData['NpUser']['study_participant_id'];
    		$userData['NpUser']['np_user_type_id'] = $userData['NpUser']['np_user_type_id'];
			//

			//commenting old
			/*$strippedData = $this->sanitize($this->request->data['NpEmailTemplate']['content']);
			$replacedData = str_replace(array("&nbsp;", "<p>", "</p>", "\r\n", "\r", "\n"),"",$strippedData);*/
			//

			// Initialization
			$subject = isset($this->request->data['NpUser']['subject'])?$this->request->data['NpUser']['subject']:'';
			$content = isset($this->request->data['NpUser']['content'])?$this->request->data['NpUser']['content']:''; 
			
			// Validation
			if ($flag == true && ($subject == '' || $content == '')) {
			$emailData= $this->NpEmailTemplate->find('first', array('conditions'=>array('NpEmailTemplate.id'=>$templateId )));			
			$subject = $emailData['NpEmailTemplate']['subject'];
			$content = $emailData['NpEmailTemplate']['content'];
			}

			$admin = 'GPTW Chile';
			// Data Save

			//commenting old
			/*$fields = array('NpUser.name','NpUser.email', 'NpUser.pword_txt AS password', 'NpUser.np_user_type_id');
			$userData = $this->NpUser->read($fields,$userId);
    		$template = 'Company Admin  Log-in Details';*/
    		//Adding new
    		$userData['NpUser']['subject']=$subject;
			$userData['NpUser']['content']=$content;
			$userData['NpUser']['user_id']=$npUserId;
			$userData['NpUser']['created'] = date('Y-m-d H:i:s');
    		//

    		$result = array();
		    $ReceiverArr['user_id'] = $npUserId;
		    $to[] = $userData['NpUser'];
		    //Added new
		    $ReceiverArr['tempName'] = 'New Password';
		    $ReceiverArr['user_name'] = $admin;
		    //
		    $ReceiverArr['to'] = json_encode($to);
		    //$ReceiverArr['content_data'] = $userData['User'];
		    //Commentiong old one and replaced with other
		    /*$ReceiverArr['content_data'] = $content;*/
		    $ReceiverArr['content_data'] = $userData['NpUser'];
		    App::import('Vendor', 'SendEmail', array('file' => 'classes/SendEmail.php'));
		    $sendingMail = new SendEmail();
		    $result = $sendingMail->send_email_process($ReceiverArr);
		    $emailToDetails = $result['details'];

		    $lastJobDetailId = $result['lastInsertId'];
		    if (!empty($lastJobDetailId)) {
		      App::import('Controller', 'SaveEmailDetails');
		      $EventController = new SaveEmailDetailsController;
		      $returnArr = $EventController->sendMailProcess($emailToDetails,$lastJobDetailId);

		     }
				// // Save method
		  //    	$dataArr = array();
				// $dataArr['NpEmailTemplate']['subject'] = $subject;
				// $dataArr['NpEmailTemplate']['content'] = $content;
				// $this->NpEmailTemplate->create();
				// if($flag == true && !$this->EmailTemplate->save($dataArr['EmailTemplate'],true/*,array('from','sender','reply_to','subject','name','content','level')*/)){
				// 	$errMsg = 'Template could not be create. Please, try again.';
				// 	$flag = false;
				// }
			

			// Redirection
			if ($flag == true) {
				$this->companyMessage($msg,3,'user');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->companyMessage($msg,0,'user');
			}
			
		//}
		
		//$this->render('send_logins');
	}


	/*Change status of user 
	*/
	function change_status($user_id = null){ 
		//echo $np_User_id ;
		$this->checkGeneric(array('rp_admin','rp_consultant','rp_reviewer'));
		$flag = true;
		$msg = "";
		$npUserData = $this->NpUser->read(null,$user_id);
		$userName = $npUserData['NpUser']['name'];
	
		if (empty($npUserData)) {
			$flag = false;
			$msg = __('Invalid User');
			
		}
		
		if(!empty($npUserData) && !($npUserData['NpUser']['active'] == 1 || $npUserData['NpUser']['active'] == 0 )){
			$flag = false;
			$msg = __('Invalid Status of User');
		}
		//-----------------Delete NpKpiDetail Table's Records-----------------------------	
		//$conditions = array('NpUserCompany.np_user_id' => $np_User_id );
			// if ($flag == true && !$this->NpUserCompany->deleteall($conditions)) { 
			// 	$flag = false;
			// 	$msg = __('Error while Emptying NpUserCompany Table!!');
				
			// }
			// if (empty($npUserData)) {
			// 	$flag = false;
			// 	$msg = __('Empty!!');			
			// }
		//-----------------Delete NpUser Table's Records-----------------------------
			//$this->NpUser->id = $np_User_id;
			//$delData = array();
			$delData = array(
					'id' => $user_id,
					
					'active' => ($npUserData['NpUser']['active'] == 1)?'0':'1',
				);
			//$this->NpUser->email = $np_email = '';
			if ($flag == true && !$this->NpUser->save($delData,false,array('id','active'))) {
				$flag = false;
				$msg = __('Error changing Status!!');				
			}
		//---------------------Check if Data is Deleted-----------------------------
			if($flag == true){
				$msg = __('Successfully changed status of user %s .',$userName);
			}
		//--------------------Display Flash Message----------------------------------
		if($flag == true && !$this->logEntry("Changed status of user ".$userName,1,0) ){

      		$flag = false;
      		$msg = __('Unable to change status of user.');
      		$flag = false;
      	}
      	
		if ($flag == true) {
			$this->companyMessage($msg,3,'user');
		}else {
			$this->companyMessage($msg,0,'user');
		}
			
		return $this->redirect(array('action' => 'index'));
	}

	public function getToken(){
	$this->checkGeneric(array('rp_admin','rp_consultant','rp_reviewer','rp_company_admin','rp_group_company_admin','rp_segment_leader'));
	$configurationData = $this->Configuration->find('first',
			array('conditions'=>
				array(
					'Configuration.constant' => 'COUNTRY')
				));
		
		$privateKey = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAoIeRzmXp+Njke61JD3vWgwsHAXQzpl/EfzhiDwgl95+HobxJ
aM8PAQHASex2e2Q0Qg5xe5cdUGxFAPpsQfY/+GaCtUElUJ0iwtZfdPmVcGawc/4H
SZyf1AvVU5AsQYwZhgrEbXM2oyUcdH5qFpj0HdIfoiYvGPB23KmiSEIO1OrBd1Bi
ao+ynYIq9GrsZhuzC9zYsAn70znHIwtAqEVBD/tIDIhvyRY/2tR4YFwE0+JNVof9
wD8wFFwJ/LuHgyS36iZk47xj9Kwu7o61bPugHqVl0mrlCw+6ncwVfU3UJbahPRBu
org4EIBsECGQ5XJIBoHefF9TvGYHEHDNWJfIGwIDAQABAoIBAQCSy6Y/0d5lWyGF
H29CI4KEDt93KuXYbJbpp4u0J0Vg7ZdABUgz+bTEvO80KnImX/mRtld8JzH9SyTG
wjmhEChrZIJ+cXZIA4m4FgTwmRNY1+7gAxpy7DET3UZUxfBSeGUWuF3roIIEKnmc
5cTpqTEC3BVlV/mVmr93BgCKhy288QEIY0kZem9NFR7dRQLjwyJN3l4jbFulob7e
jm5ji9taC0m0ZmURybane8LG0CGleJ/tjAmi6gCZQVMmINFQC1icdGbadiFBG1V9
WqmurFKsurXb18oDrDkz7MVi8O0UBGcQKchSU61jFsZ/V8zO4sKIs/QoqXtalmh3
07t9ebCBAoGBAOqL7H8aPAURN9QLcot0I3AsNwdTJBtCSfiV9dtso6vebBMZnZLG
y9wEnTafZDWP8VzKFAiEztXh0pEdVzIjvxOMSGjx3SMadEfKlYKMX28N3xLzEASW
zqT+LPOl5iUlZnD4TbmCGwZALB/bDf45gWmGs5dlh6rHb1PhOArhT00hAoGBAK82
fAQzxMMjlPcnshefj50yma2B7lj76PYl5FyE1EH/1axM1dLMhjVSaqTJB2zBGW/9
Atbu/rYgYgAZK6AdQrJ49k9VEGvWRwNxCIm/nan1EBe1L8+D2nEHFMJhO5U/aGDp
Z0iZ9Fz0RS+01Ops7WctYL+3zuuNNwA36M5I41G7AoGBAIah6RAQjBFQj95c79RS
xyDVkITY2an4BCP4WJcqpky6sQjJtGSTTmOuFlxLZCdGyAI+UP+O1Hd7V/ZKhEnY
sQ7UgKAU7Z3/ym2HQQkd8I37xWfINBKeSmH1MPJu8UuzSzlfnqX0o/STk4B5qm+a
rMlZM++crSJ/tkzMw/Gi4XVhAoGAUGA+I+9bo+j+vSKIoC2iRAqiVOX14PwusjxP
teF5PY2PB6t3q2wHZQ6ZvV46+bjbYnQ+iTq5vfK9Ai6JxLmnjxfOZjYvgkiZ6wo/
UHGGciDpcPa9KATkgFUvQLw6CQ09ZLetmbCGWN31nxzlT2UIwvweFdTMJ2JwiLkd
IwRsw2ECgYACHE5MyY/uOHiRRB1XlF1J8KVII+W17laeufQcKfY4c8htiNtcLCCN
qB+v9gAER/T5kHB/IbwwEmNbHgAa7OAOehyyro7efQaBQuamx9hKxUccaCyp0I8T
Z57CvByNFNQ5oQOL5E5WnBLlo5LPI28bPunJddKtX+nzTlYSFTensQ==
-----END RSA PRIVATE KEY-----
EOD;

		$d = new DateTime();
		$timestamp = $d->getTimestamp();
		
		$token = array(
			"iss" => "https://".$_SERVER[HTTP_HOST]."/",
			"iat" => $timestamp,
			"nbf" => $timestamp,
			"exp"  => $timestamp + (20 * 60),
			"data" => [                  
				'userId'   => $this->Session->read('User.id'),
				'country' => $configurationData['Configuration']['value'],
			]
		);
		
		$jwt = JWT::encode($token, $privateKey, 'RS256');
		
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $jwt
		);

		$this->set(array('response' => $response, '_serialize' => array('response')));
	}
}