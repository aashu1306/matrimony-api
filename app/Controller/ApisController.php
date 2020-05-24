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
	public $uses = array('Religion', 'Education', 'Country');
	public function beforeFilter() {
		$this->Security->unlockedActions = array('getEducations', 'getReligions', 'getCommonData');
		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');
		$this->url = Router::url('/',true);
		ini_set('max_execution_time',36000);
		ini_set('memory_limit',-1);
	}

	public function getCommonData(){
		
		$educationData = $this->Education->find('all', array('conditions'=>array('Education.status' => 1),'fields'=>array('Education.id', 'Education.title')));
		$educationDataArr = array();
		foreach ($educationData as $key => $value) {
			$educationArr = array();
			$educationArr['id'] = $value['Education']['id'];
			$educationArr['name'] = $value['Education']['title'];
			$educationDataArr[] = $educationArr;
		}

		$religionData = $this->Religion->find('all', array('conditions'=>array('Religion.status' => 1),'fields'=>array('Religion.id', 'Religion.name')));
		$religionDataArr = array();
		foreach ($religionData as $key => $value) {
			$religionArr = array();
			$religionArr['id'] = $value['Religion']['id'];
			$religionArr['name'] = $value['Religion']['name'];
			$religionDataArr[] = $religionArr;
		}

		$countryData = $this->Country->find('all', array('conditions'=>array('Country.status' => 1),'fields'=>array('Country.id', 'Country.name')));
		$countryDataArr = array();
		foreach ($countryData as $key => $value) {
			$countryArr = array();
			$countryArr['id'] = $value['Country']['id'];
			$countryArr['name'] = $value['Country']['name'];
			$countryDataArr[] = $countryArr;
		}

		$response = array(
				'code' => '200',
				'message' => '',
				'data' => array('education' => $educationDataArr, 'religion' => $religionDataArr, 'country' => $countryDataArr)
			);
		$this->set(array('response' => $response, '_serialize' => 'response'));
	}
	public function getEducations(){
		$educationData = $this->Education->find('all', array('conditions'=>array('Education.status' => 1),'fields'=>array('Education.id', 'Education.title')));
		$educationDataArr = array();
		foreach ($educationData as $key => $value) {
			$educationArr = array();
			$educationArr['id'] = $value['Education']['id'];
			$educationArr['title'] = $value['Education']['title'];
			$educationDataArr[] = $educationArr;
		}
		$response = array(
				'code' => '200',
				'message' => '',
				'data' => $educationDataArr
			);
		$this->set(array('response' => $response, '_serialize' => 'response'));
	}

	public function getReligions(){
		$religionData = $this->Religion->find('all', array('conditions'=>array('Religion.status' => 1),'fields'=>array('Religion.id', 'Religion.name')));
		$religionDataArr = array();
		foreach ($religionData as $key => $value) {
			$religionArr = array();
			$religionArr['id'] = $value['Education']['id'];
			$religionArr['title'] = $value['Education']['title'];
			$religionDataArr[] = $religionArr;
		}
		$response = array(
				'code' => '200',
				'message' => '',
				'data' => $religionDataArr
			);
		$this->set(array('response' => $response, '_serialize' => 'response'));
	}
}