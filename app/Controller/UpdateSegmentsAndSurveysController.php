<?php
App::uses('AppController', 'Controller');
/**
 * UpdateSegmentsAndSurveys Controller
 */
class UpdateSegmentsAndSurveysController extends AppController {
	var $uses = array('SurveySegmentation','SurveySegmentationDetail','Survey');

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('test','process','checkValidData','updateSurvey','updateSegment','checkUserDetail','checkValidData');

		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');

		$this->url = Router::url('/',true);
	}
	
	/* Update wordcloud status done or pending. */
	public function process(){

		$access = $this->checkUserDetail($_POST);
		if ($access['IsError']) {
			$response = array(
				'is_error' => true,
				'error_message' => '',
				'data' => $access['error_message'],
			);

			return $response;
		}
		$valid = $this->checkValidData($_POST);
		if (!$valid['IsError']) {
			if ($_POST['type'] == 'survey') {
				$a = $this->updateSurvey($_POST['id']);
			}
			
			if ($_POST['type'] == 'segment') {
				$a = $this->updateSegment($_POST['id']);
			}

			$is_error = false;
			if ($a['flag'] == false) {
				$is_error = true;
			}

			$response = array(
				'is_error' => $is_error,
				'error_message' => '',
				'data' => $a['msg'],
			);

			return $response;
		}
	}

	function updateSurvey($surveyId){
		
		$surveyData = $segmentData = $returnResponse = array();

		$existSurveyData = $this->Survey->find('first', 
			array('conditions'=>
				array('Survey.id' => $_POST['id']),
				'fields'=>
				array('Survey.id','Survey.wordcloud_status','Survey.is_comments_available')
				)
			);

		if ($existSurveyData['Survey']['wordcloud_status'] == 1) {
			$returnResponse['flag'] = true;
			$returnResponse['msg'] = __('Wordcloud already exist.');
			return $returnResponse;
		}

		$returnResponse['flag'] = true;
		$returnResponse['msg'] = __('Wordcloud status updated.');

		if ($_POST['is_comments_available'] != 1) {
			$_POST['is_comments_available'] = 0;
		}

		$surveyData = array(
			'Survey'=>array(
				'id' => $surveyId,
				'wordcloud_status' => 1,
				'is_comments_available' => $_POST['is_comments_available'],
				'wordcloud_date' => date('Y-m-d h:i:s')
			)
		);

		if ($returnResponse['flag'] == true and !$this->Survey->save($surveyData['Survey'],false,array('id','wordcloud_status','wordcloud_date','is_comments_available'))) {
			$returnResponse['flag'] = false;
			$returnResponse['msg'] = __('Unable to update wordcloud status.');
		}

		return $returnResponse;
	}	

	function updateSegment($segmentId){
		
		$surveyData = $segmentData = $returnResponse = array();

		$existSegmentData = $this->SurveySegmentation->find('first', 
			array('conditions'=>
				array('SurveySegmentation.id' => $_POST['id']),
				'fields'=>
				array('SurveySegmentation.id','SurveySegmentation.wordcloud_status','SurveySegmentation.is_comments_available')
				)
			);

		if ($existSegmentData['SurveySegmentation']['wordcloud_status'] == 1) {
			$returnResponse['flag'] = true;
			$returnResponse['msg'] = __('Wordcloud already exist.');
			return $returnResponse;
		}

		$returnResponse['flag'] = true;
		$returnResponse['msg'] = __('Wordcloud status updated.');

		if ($_POST['is_comments_available'] != 1) {
			$_POST['is_comments_available'] = 0;
		}
		
		$segmentData = array(
			'SurveySegmentation'=>array(
				'id' => $segmentId,
				'wordcloud_status' => 1,
				'is_comments_available' => $_POST['is_comments_available'],
				'wordcloud_date' => date('Y-m-d h:i:s')
			)
		);

		if ($returnResponse['flag'] == true and !$this->SurveySegmentation->save($segmentData['SurveySegmentation'],false,array('id','wordcloud_status','wordcloud_date','is_comments_available'))) {
			$returnResponse['flag'] = false;
			$returnResponse['msg'] = __('Unable to update wordcloud status.');
		}

		return $returnResponse;
	}	

	function checkUserDetail($data){
		
		$redirectClass = 'Authentication';
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		$redirectClassController = new $newClass;		
		$response = $redirectClassController->checkUser($data);
		return $response;
	}

	function checkValidData($data){
		$redirectClass = 'Authentication';
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		$redirectClassController = new $newClass;		
		$response = $redirectClassController->checkValidId($data);
		return $response;
	}

	public function test(){
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => '',
		);

		return $response;
	}
}