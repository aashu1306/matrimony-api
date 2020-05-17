<?php
App::uses('AppController', 'Controller');
/**
 * TestOrchestration Controller
 */
class TestOrchestrationController extends AppController {
	var $uses = array();

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('testwordcloud');

		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');

		$this->url = Router::url('/',true);
	}

	public function testwordcloud($redirectClass){
		
		$redirectClass = ucfirst($redirectClass);

		App::import('Controller', $redirectClass);

		$newClass = $redirectClass.'Controller';
		
		$redirectClassController = new $newClass;
		
		$response = $redirectClassController->test();

		$this->set(array('response' => $response, '_serialize' => array('response')));
	}		
}