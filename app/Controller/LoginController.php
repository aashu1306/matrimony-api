<?php
App::uses('AppController', 'Controller');
/**
 * Login Controller
 */
class LoginController extends AppController {
	var $uses = array();

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('login');
		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');
		$this->url = Router::url('/',true);
	}
	
	public function login(){
		$redirectClass = ucfirst($redirectClass);
		App::import('Controller', 'Authentication');
		$newClass = 'AuthenticationController';		
		$redirectClassController = new $newClass;
		$response = $redirectClassController->process();
		$this->set(array('response' => $response, '_serialize' => array('response')));
	}			
}