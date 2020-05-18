<?php
App::uses('AppController', 'Controller');

/**
 * Apis Controller
 */
class ApisController extends AppController {

/**
 * Scaffold
 *
 * @var mixed
 */
	public $settings;
	public $uses = array();
	public function beforeFilter() {
		//$this->Security->unlockedActions = array('label');
		//$this->nonAuthorizedActions = array('login', 'logOff');
		parent::beforeFilter();
		$this->url = Router::url('/',true);
		ini_set('max_execution_time',36000);
		ini_set('memory_limit',-1);
	}

	//To render left menu independent of survey and company
	/* public function label()
	{
		$this->companyId = isset($this->request->query['company_id'])?$this->request->query['company_id']:0;
		$this->studyId = isset($this->request->data['study_id'])?$this->request->data['study_id']:0;
		$this->companyData = array(); */
		/* $this->companyData = $this->Company->find('first',
			array(
				'conditions' => array('Company.id' => $this->companyId),
				'fields' => array('Company.bp_access')
				)); */
		
		/* $class = 'app\\Label';
		$objMyWidget = new $class($this);
		$data = $objMyWidget->getWidget();
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);

		$this->set(array('response' => $response, '_serialize' => array('response')));
	} */
}