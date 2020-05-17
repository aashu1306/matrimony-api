<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $components = array('Security','Session','RequestHandler'/*,'Cookie'*/);
	var $sanitizationOptions = array();
	var $langKey='';
	var $langId='';
	var $langKeyDefault='';
	var $langIdDefault='';
	var $nonAuthorizedActions = array();
	var $userRoles = array();
	var $companyId = '';

	public $uses = array('Configuration','SurveyDemographic','SurveyDemographicOption','SurveyBackendDemographic','SurveyBackendDemographicOption','Role');

	public function beforeFilter() {
		$this->companyId = $this->Session->read('User.company_id');
		$this->Security->csrfCheck = true;
		$this->Security->csrfExpires = '+200 minute';
		$this->Security->csrfUseOnce = true;
		$this->Security->blackHoleCallback = 'blackhole';
		$this->_addHeaderForCors();
		$this->request->data = $this->sanitize($this->request->data, $this->sanitizationOptions);
		// If user is logged in then set all allowed roles in an array
		if ($this->Session->check('User.id') and empty($this->userRoles)) {
			$this->loadRoles();
		}
		// If session is not valid then redirect to login page 
		if(!in_array($this->params['action'], $this->nonAuthorizedActions) and (!$this->Session->valid())){
			
			// If it is API call with extension then set the error action
			if (isset($this->params->ext) and strtolower($this->params->ext) == 'json') {
				/*
				setting additional parameter to controller so that when the actual action get called these parameters can be used to set the proper respose
				*/
				$this->pass = array('msg' => __('Session expired'), 'options' => array('errorType' => 'logout'));
				/*
				Action is changed to error action
				this will set the error in response
				 */
				$this->setAction('setError');
				
			} else {
				/*
				When a normal method is called then redirect to login page
				 */
				$this->redirect('/admin',null,true);
			}
		}


	}

	//This function sends header to allows cross domain calls
	//This is a temparary function should be removed once Maximess finish the work
	protected function _addHeaderForCors()
	{
		// Get the $_SERVER['HTTP_ORIGIN'] value
		// http://book.cakephp.org/2.0/en/core-libraries/global-constants-and-functions.html#env
		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		// Need the host name to be dynamic for CORS
		// Can't set to '*' for credential transfer header to work
		$this->response->header('Access-Control-Allow-Origin', $host);
		// Has to be set for credential transfer Cross-Domain
		$this->response->header('Access-Control-Allow-Credentials', 'true');
	}

	#@16/10/2015 by Vishal, echo function 
	protected function db($var = array()) {
		if (!is_string($var)) {
			echo '<pre>';
			print_r($var);
			echo '</pre>';
		}
		else {
			echo $var;
		}


	}
	protected function strip_pnoid($string,$allowed=array(),$allowOrDeny="allow" ){
		// Now we don't want the affect of this function as we are doing sanitization using another function.
		return $string;
		// $defalutAllowed = array("\\"," ","'","?","+","*","@","`",".","&","(",")","\"",",","$","%","#",":",";","|","[","]","{","}","_			","=","!","~","<",">","/");
		$defalutAllowed = array("~","`","!","@","#","$","%","^","&","*","(",")","_","-","+","=","{","}","[","]","|","\\",":",";",'"',"'","<",">",",",".","?","/"," ");

		$allowed =(array)$allowed;


		if (!is_array($string)) {

			if ($allowOrDeny=="allow") {
				if (empty($allowed)) {
					$allowed = $defalutAllowed;

				}
			}elseif ($allowOrDeny=="deny") {
				$allowed = array_diff($defalutAllowed,$allowed);
			}	
			// $string = trim(preg_replace('/\s+/', ' ', strip_tags($string)));
			$string = strip_tags($string);

			$allowStr = null;
			$allowed[] = " ";
	        if (!empty($allowed)) {
	            foreach ($allowed as $value) {
	                 $allowStr .= "\\$value";
	            }
	        }
	        $string = preg_replace("/[^\n\r{$allowStr}a-zA-Z0-9]/", '', $string);
	        $string = trim(preg_replace('/ +/', ' ', $string));
			//$string = preg_replace("/\n+/","\n",$string);

        }
 
		return $string;
	}

	//below function is used to display the message
	function companyMessage($message, $value='3', $placeHolder='flash'){
		switch ($value) {
			case '0':
				$message = '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';		
				break;
			case '1':
				$message = '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">&times;</button>'.$message . '</div>';
				break;
			case '2':
				$message = '<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';
				break;
			default:
				$message = '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';
				break;
		}
		$this->Session->setFlash($message,'default',array(),$placeHolder);
	}
	function npKpiMessage($message, $value='3', $placeHolder='flash'){
		switch ($value) {
			case '0':
				$message = '<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';		
				break;
			case '1':
				$message = '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">&times;</button>'.$message . '</div>';
				break;
			case '2':
				$message = '<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';
				break;
			default:
				$message = '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button>' . $message . '</div>';
				break;
		}
		$this->Session->setFlash($message,'default',array(),$placeHolder);
	}
	
	// to check the Admin
	public function checkAdmin(){
		if(!$this->Session->valid()){
			$this->redirect('/admin',null,true);
		}
		if($this->Session->read('User.type')!= 'admin'){
			//$this->redirect('/Pages/refresh/Not Allowed',null,true);
			$this->redirect('/admin',null,true);
		}
	}
	public function checkReviewer(){
		if(!$this->Session->valid()){
			$this->redirect('/admin',null,true);

		}
		if($this->Session->read('User.type')!= 'reviewer'){
			//$this->redirect('/Pages/refresh/Not Allowed',null,true);
			$this->redirect('/admin',null,true);
		}
	}
	public function checkConsultant(){
		if(!$this->Session->valid()){
			$this->redirect('/admin',null,true);
		}
		if($this->Session->read('User.type')!= 'consultant'){
			//$this->redirect('/Pages/refresh/Not Allowed',null,true);
			$this->redirect('/admin',null,true);
		}
	}

	//TO CHECK THE TYPE OF USER
	function checkGeneric($types=array()){
		if(!$this->Session->valid()){
			return $this->redirect('/Pages/refresh/Please Login/admin/',null,true);
		}elseif(empty($types)){
			return $this->redirect('/Pages/refresh/Please Login/admin/',null,true);
		}elseif(!$this->isRole($types)){
			return $this->redirect('/Pages/refresh/Not Allowed',null,true);
		}

		return true;
	}

	// Generates Random String for given length
	public static function randomPassword($length, $available_chars = 'ABDEFHKMNPRTWXYABDEFHKMNPRTWXY23456789') {
		$chars = preg_split('//', $available_chars, -1, PREG_SPLIT_NO_EMPTY);
		$char_count = count($chars);
		$out = '';
		for($ii = 0; $ii < $length; $ii++) {
			$out .= $chars[rand(1, $char_count)-1];
		}
		return $out;
	}

	//removes the multispaces from the string	
	function stripTagsAndSpace($string){
		// Now we don't want the affect of this function as we are doing sanitization using another function.
		return $string;
		if (!is_array($string)) {

			$string = trim(preg_replace('/\s+/', ' ', strip_tags($string)));
		}

		return $string;
	}

	//for log entry
	//For the action_id we have the following array
	/*1 = 'Add/Created/Insert';
	2 = 'Edit/Update';
	3 = 'Delete';
	4 = 'Assigned';
	5 = 'Active/In-Active';
	6 = 'Upload';
	7 = 'Download';
	8 = 'Un Assigned';
	9 = 'Email Sent';
	10 = '';
	11 = 'Login Fail';
	12 = 'Confirm';*/
	
	function logEntry($description=null, $actionId=null, $companyId=null){
		$userId = $this->Session->read('User.id');

		$clientIpAddress = $this->getRealIpAddr(false);
		$completeDate = date("Y-m-d H:i:s", strtotime('now'));

		if(!isset($userId) || $userId==0 || empty($userId) || $userId==null){
			$userId = 0;
		}
		
		if(!isset($companyId) || $companyId == null){
			$companyId = 0;
		}
		$description = addslashes($description);
		$this->{$this->uses[0]}->query("INSERT INTO logs(description,action,date,user_id,company_id,ip,ip_address) VALUES ('{$description}',{$actionId},'{$completeDate}',{$userId},{$companyId},INET_ATON('".$_SERVER['REMOTE_ADDR']."'),'{$clientIpAddress}')");
		return true;      
	}

	// Pooja - This function is used to get the preferred langauge form language table for getting the languages to set the language of the platform 
	public function getDefaultLang(){ 
		$langDatas = $this->{$this->uses[0]}->query("SELECT id, language, language_short_code, for_TI FROM languages where for_TI in ('DEFAULT','PREFERRED')");
		foreach ($langDatas as $langData) {
			$langArr[$langData['languages']['for_TI']]['id'] = $langData['languages']['id'];
			$langArr[$langData['languages']['for_TI']]['language_short_code'] = $langData['languages']['language_short_code'];
		}
		return $langArr;	
	}

	# This funcation Sanitize the input ($this-request-data)
	# By default it stripout the unwanted tags
	public function sanitize($input, $options = array())
	{
		$defaultOptions = array();
		$options = array_merge($defaultOptions, $options);

		if (is_array($input)) {
			foreach ($input as $key => $value) {
				
				if (is_array($value)) {
					foreach ($value as $key1 => $value1) {
						if(isset($this->sanitizationOptions["$key.$key1"]) and strtolower($this->sanitizationOptions["$key.$key1"]) == 'skip'){
							$input[$key][$key1] = $value1;
							continue;
						} elseif (isset($this->sanitizationOptions["$key.$key1"]) and strtolower($this->sanitizationOptions["$key.$key1"]) == 'html') {
							$input[$key][$key1] = $this->htmlPurifier($value1);
							continue;
						}

						if (is_array($value1)) {
							$input[$key][$key1] = $this->sanitize($value1, $options);
							continue;
						}

						$input[$key][$key1] = filter_var ($value1, FILTER_SANITIZE_STRING, array('flags' =>FILTER_FLAG_NO_ENCODE_QUOTES));
					}					

				} else {

					$input[$key] = filter_var ($value, FILTER_SANITIZE_STRING, array('flags' =>FILTER_FLAG_NO_ENCODE_QUOTES));
				}
				
			}
		
		
			return $input;
		}

		return filter_var ($input, FILTER_SANITIZE_STRING, array('flags' =>FILTER_FLAG_NO_ENCODE_QUOTES));
	}

	//$value = $this->htmlPurifier($value, array('html','head', 'body', 'div', 'p', 'a'));

	# Furify html with custom options
	function htmlPurifier($string = null){
		$string = trim($string);
		if ($string==null || empty($string) || $string=="") {
			return $string;
		}else{
			//include file from vandor
	        App::import('Vendor', 'HTMLPurifier', array('file' => 'HTMLPurifier/library' . DS . 'HTMLPurifier.auto.php'));
	        //assign
	        $config = HTMLPurifier_Config::createDefault();
	        //$config->set('HTML.Allowed', 1);
	        //object
	        $purifier = new HTMLPurifier($config);
	        return $purifier->purify($string);
	    }
    }

    protected function get_resized_image($photoTempName){
		
		App::import('Vendor', 'SimpleImage', array('file' => 'UploadHandler/SimpleImage.php'));

		// Images resize
		$image = new SimpleImage();

		$image_params = getimagesize($photoTempName); //debug($photoTempName);
		$originalWidth = $image_params[0]; 
		
		//@29/06/2015 by Vishal, Resizing uploaded image in ckEditor/Simple Uploads
		$originalHeight = $image_params[1]; 
		
		$image->load($photoTempName);
		//$fileName = ""

		//When Width > Height
		if($originalWidth > $originalHeight) {
			$newWidth = 960; 
			$newHeight = 480;
			
			if($originalWidth > $newWidth || $originalHeight > $newHeight){							  
				$image->resize($newWidth,$newHeight);
			}
		}

		//When Width < Height
		if($originalWidth < $originalHeight) {
			$newWidth = 480; 
			$newHeight = 960;
			
			if($originalWidth > $newWidth || $originalHeight > $newHeight){
				$image->resize($newWidth,$newHeight);
			}
		}

		//When Width = Height
		if($originalWidth == $originalHeight) {
			$newWidth = $newHeight = 480;
			if($originalWidth > $newWidth || $originalHeight > $newHeight) {
				$image->resize($newWidth,$newHeight);
			}
		}
		
		$photoTempName_New = TMP."resized_".rand(1,8);
		
		$image->save($photoTempName_New);
		
		return $photoTempName_New;
	}

/* function for client name use any where in project */
	public function getClientName($key = null){
		$clientNameArr = $this->Configuration->find('first', array(
       		'fields' => array('Configuration.value'),
       		'conditions' => array(
       			'Configuration.constant' => $key
       			)
       	));
       	return $clientNameArr['Configuration']['value'];
	}

	function getLastQuery($model) {
		
	    $dbo = $model->getDatasource();
	    $logData = $dbo->getLog();
	    $getLog = end($logData['log']);
	    echo $getLog['query'];
	}

	function getUsersRoles($userId){
		$roleData = $this->UsersRole->find('list',array(
			'conditions'=>array('UsersRole.user_id'=> $userId),
			'joins' => array(
		       		array(
		       			'alias' => 'Role',
		          		'table' => 'roles',
		          		'type' => 'Inner',
		          		'conditions' => 'UsersRole.role_id =Role.id'
		        	)
		 		),
			'fields'=>array('UsersRole.role_id','Role.role')));
		if (!empty($roleData)) {
			return false;
		}
		else{
			return $roleData;
		}
	}

	/**
	 * Check whether atleast one role exists in user roles
	 * @param  array  $roles A single role in string or multiple roles in an array
	 * @return [type]        Bool
	 */
	public function isRole($roles = array())
	{
		if (is_string($roles)) {
			return in_array($roles, $this->userRoles);
		}

		foreach ($roles as $key => $role) {
			if (in_array($role, $this->userRoles)) {
				return true;
			}
		}
		return false;
	}

	public function loadRoles()
	{
		if ($this->Session->check('User.id') and empty($this->userRoles)) {
			$this->userId = $this->Session->read('User.id');
			$result = $this->{$this->uses[0]}->query("select role from users u join users_roles ur on (u.id = ur.user_id) join roles r on (ur.role_id = r.id) where user_id = {$this->userId}");
			
			foreach ($result as $key => $role) {
				$this->userRoles[] = $role['r']['role'];
			}

			$this->set('userRoles', $this->userRoles);
		}
	}

	public function updateCompanyId($id){

		$this->companyId = $id;
		$this->set('companyId', $this->companyId);
		return $id;
	}

	public function filterText($text){
		// trim the text;
		$trimText = trim($text);
		// Make the text lower case strtolower.
		$lowerText = strtolower($trimText);
		// Remove specail charector.
		$finalText = preg_replace("/[^A-Za-z0-9\-]/", "", html_entity_decode($lowerText, ENT_QUOTES));
		return $finalText;
	}

	public function getFrontDemoData($demoId)
    {
      $surveyDemoData = $this->SurveyDemographic->find('first', array('conditions' => array('SurveyDemographic.id' => $demoId), 'fields'=>array('SurveyDemographic.id','SurveyDemographic.statement')));
      return $demoKey = $this->filterText($surveyDemoData['SurveyDemographic']['statement']);
    }
    
    public function getFrontDemoOptData($demooptId)
    {
      $surveyDemoData = $this->SurveyDemographicOption->find('first', array('conditions' => array('SurveyDemographicOption.id' => $demooptId), 'fields'=>array('SurveyDemographicOption.id','SurveyDemographicOption.option')));
      return $demoKey = $this->filterText($surveyDemoData['SurveyDemographicOption']['option']);
    }

	public function getBackDemoData($demoId)
    {
      $surveyDemoData = $this->SurveyBackendDemographic->find('first', array('conditions' => array('SurveyBackendDemographic.id' => $demoId), 'fields'=>array('SurveyBackendDemographic.id','SurveyBackendDemographic.name')));

      return $demoKey = $this->filterText($surveyDemoData['SurveyBackendDemographic']['name']);
    }
    
    public function getBackDemoOptData($demooptId)
    {
      $surveyDemoData = $this->SurveyBackendDemographicOption->find('first', array('conditions' => array('SurveyBackendDemographicOption.id' => $demooptId), 'fields'=>array('SurveyBackendDemographicOption.id','SurveyBackendDemographicOption.name')));
      
      return $demoKey = $this->filterText($surveyDemoData['SurveyBackendDemographicOption']['name']);
    }

    function headerStatus($statusCodeValue){
	  	static $statusCodes = null;
	  	if($statusCodes === null){
	   		$statusCodes = array(100 => 'Continue',101 => 'Switching Protocols',102 => 'Processing',200 => 'OK',201 => 'Created',202 => 'Accepted',203 => 'Non-Authoritative Information',204 => 'No Content',205 => 'Reset Content',206 => 'Partial Content',207 => 'Multi-Status',300 => 'Multiple Choices',301 => 'Moved Permanently',302 => 'Found',303 => 'See Other',304 => 'Not Modified',305 => 'Use Proxy',307 => 'Temporary Redirect',400 => 'Bad Request',401 => 'Unauthorized',402 => 'Payment Required',403 => 'Forbidden',404 => 'Not Found',405 => 'Method Not Allowed',406 => 'Not Acceptable',407 => 'Proxy Authentication Required',408 => 'Request Timeout',409 => 'Conflict',410 => 'Gone',411 => 'Length Required',412 => 'Precondition Failed',413 => 'Request Entity Too Large',414 => 'Request-URI Too Long',415 => 'Unsupported Media Type',416 => 'Requested Range Not Satisfiable',417 => 'Expectation Failed',422 => 'Unprocessable Entity',423 => 'Locked',424 => 'Failed Dependency',426 => 'Upgrade Required',500 => 'Internal Server Error',501 => 'Not Implemented',502 => 'Bad Gateway',503 => 'Service Unavailable',504 => 'Gateway Timeout',505 => 'HTTP Version Not Supported',506 => 'Variant Also Negotiates',507 => 'Insufficient Storage',509 => 'Bandwidth Limit Exceeded',510 => 'Not Extended');
	  	}
	  	if ($statusCodes[$statusCodeValue] !== null){
	   		$statusString = $statusCodeValue . ' ' . $statusCodes[$statusCodeValue];
	   		$this->response->header($_SERVER['SERVER_PROTOCOL'] . ' ' . $statusString, true, $statusCodeValue);
	  	}
	}

	function getRoleId($role)
	{
		$roleData = $this->Role->find('first', array('conditions' => array('Role.role' => $role)));
      	return $roleID = $roleData['Role']['id'];
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


/* return Role Id by Role */
    public function getRoleIdByRole($role)
    {
    	$roleData = $this->Role->find('first', array('conditions' => array('Role.role' => $role)));
      	return $roleId = $roleData['Role']['id'];
    }

/* return Role Display Name by Role */
    public function getRoleDisplayNameByRole($role)
    {
    	$roleData = $this->Role->find('first', array('conditions' => array('Role.role' => $role)));
      	return $roleDisplayName = $roleData['Role']['display_name'];
    }

}
