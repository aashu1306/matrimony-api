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
	public $components = array('Security','Session','RequestHandler');
	var $uses = array();
	public function beforeFilter() {
		$this->Security->csrfCheck = true;
		$this->Security->csrfExpires = '+200 minute';
		$this->Security->csrfUseOnce = true;
		$this->Security->blackHoleCallback = 'blackhole';
	}

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
	
	public function logEntry($description=null, $actionId=null, $companyId=null){
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

	public function getLastQuery($model) {
		
	    $dbo = $model->getDatasource();
	    $logData = $dbo->getLog();
	    $getLog = end($logData['log']);
	    echo $getLog['query'];
	}

    public function headerStatus($statusCodeValue){
	  	static $statusCodes = null;
	  	if($statusCodes === null){
	   		$statusCodes = array(100 => 'Continue',101 => 'Switching Protocols',102 => 'Processing',200 => 'OK',201 => 'Created',202 => 'Accepted',203 => 'Non-Authoritative Information',204 => 'No Content',205 => 'Reset Content',206 => 'Partial Content',207 => 'Multi-Status',300 => 'Multiple Choices',301 => 'Moved Permanently',302 => 'Found',303 => 'See Other',304 => 'Not Modified',305 => 'Use Proxy',307 => 'Temporary Redirect',400 => 'Bad Request',401 => 'Unauthorized',402 => 'Payment Required',403 => 'Forbidden',404 => 'Not Found',405 => 'Method Not Allowed',406 => 'Not Acceptable',407 => 'Proxy Authentication Required',408 => 'Request Timeout',409 => 'Conflict',410 => 'Gone',411 => 'Length Required',412 => 'Precondition Failed',413 => 'Request Entity Too Large',414 => 'Request-URI Too Long',415 => 'Unsupported Media Type',416 => 'Requested Range Not Satisfiable',417 => 'Expectation Failed',422 => 'Unprocessable Entity',423 => 'Locked',424 => 'Failed Dependency',426 => 'Upgrade Required',500 => 'Internal Server Error',501 => 'Not Implemented',502 => 'Bad Gateway',503 => 'Service Unavailable',504 => 'Gateway Timeout',505 => 'HTTP Version Not Supported',506 => 'Variant Also Negotiates',507 => 'Insufficient Storage',509 => 'Bandwidth Limit Exceeded',510 => 'Not Extended');
	  	}
	  	if ($statusCodes[$statusCodeValue] !== null){
	   		$statusString = $statusCodeValue . ' ' . $statusCodes[$statusCodeValue];
	   		$this->response->header($_SERVER['SERVER_PROTOCOL'] . ' ' . $statusString, true, $statusCodeValue);
	  	}
	}
}
