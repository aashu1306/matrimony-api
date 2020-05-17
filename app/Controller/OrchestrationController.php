<?php
App::uses('AppController', 'Controller');
/**
 * Orchestration Controller
 */
class OrchestrationController extends AppController {
	var $uses = array();

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('wordcloud');

		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');

		$this->url = Router::url('/',true);
	}
	
	public function wordcloud($redirectClass){
		
		$redirectClass = ucfirst($redirectClass);
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		if (!in_array($newClass, array('ListOfSegmentsAndSurveysController', 'GetCommentsController', 'UpdateSegmentsAndSurveysController', 'AuthenticateTokenController'))) {
			$this->headerStatus(400);
			$returnArr['IsError'] = true;
	       	$returnArr['error_message'] = "Bad Request.";
		}
		$redirectClassController = new $newClass;
		$response = $redirectClassController->process();
		$this->set(array('response' => $response, '_serialize' => array('response')));
	}			
}