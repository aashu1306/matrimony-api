<?php
App::uses('AppController', 'Controller');
/**
 * GetComments Controller
 */
class GetCommentsController extends AppController {
	var $uses = array('Candidate','Survey','SurveyOpenEndedAnswer','SurveyOpenEndedQuestion','SurveyOtherLanguageOpenEndedQuestion','SurveySegmentation','SurveySegmentationDetail','DemographicCandidate','SurveyDemographicAnswer','Language');

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('test', 'process','segmentWiseComments','surveyWiseComments','getOpenEndedQuestion','updateStatus','checkUserDetail','checkValidData');

		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');

		$this->url = Router::url('/',true);
	}
	
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

		$valid = $x = $a = array();

		$valid = $this->checkValidData($_POST);
		if (!$valid['IsError']) {
			if ($_POST['type'] == 'survey') {

				$a = $this->surveyWiseComments($_POST['id']);
			}


			if ($_POST['type'] == 'segment') {
				
				$a = $this->segmentWiseComments($_POST['id']);
			}

			if (empty($a)) {			
				$res = $this->updateStatus($_POST);
				return $res;
			}

			$response = array(
				'is_error' => false,
				'error_message' => '',
				'data' => $a,
			);

			return $response;
		}

	}

	function surveyWiseComments($surveyId){
		
		$candidateArr = $candidateIds = $x = $a = array();

		$languageData = $this->Language->find('list',array('fields'=>
				array(
					'Language.id','Language.language_display')
				));
		$preferredLanguageData = $this->Language->find('first',
			array('conditions'=>
				array(
					'Language.for_TI' => 'PREFERRED'),
				'fields'=>
				array(
					'Language.id','Language.language_display')
				));

		$surveyOpenEndedAnswerData = $this->SurveyOpenEndedAnswer->find('all', 
			array('conditions'=>
				array('SurveyOpenEndedQuestion.survey_id' => $surveyId, 'SurveyOpenEndedAnswer.answer !=' => '', 'SurveyOpenEndedQuestion.statement !=' => ''),
					'joins'=>array(
					array(
						'alias' => 'SurveyOpenEndedQuestion',
			          	'table' => 'survey_open_ended_questions',
			          	'conditions' => 'SurveyOpenEndedQuestion.id = SurveyOpenEndedAnswer.survey_open_ended_question_id'
			        ),
			        array(
						'alias' => 'SurveyOtherLanguageOpenEndedQuestion',
			          	'table' => 'survey_other_language_open_ended_questions',
			          	'conditions' => 'SurveyOtherLanguageOpenEndedQuestion.survey_open_ended_question_id = SurveyOpenEndedQuestion.id'
			        )
				),
					'fields'=>
					array('SurveyOpenEndedAnswer.survey_open_ended_question_id','SurveyOpenEndedAnswer.answer','SurveyOpenEndedAnswer.candidate_id','SurveyOpenEndedAnswer.language_id','SurveyOtherLanguageOpenEndedQuestion.statement','SurveyOpenEndedQuestion.template_open_ended_master_id'
					), 
				'group' => 
					array(
						'SurveyOpenEndedAnswer.survey_open_ended_question_id','SurveyOpenEndedAnswer.candidate_id'
					)
				)
			);

		foreach ($surveyOpenEndedAnswerData as $surveyOpenEndedAnswerKey => $surveyOpenEndedAnswerValue) {
			
			$language = $preferredLanguageData['Language']['language_display'];
			
			if ($surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['language_id'] != 0) {
				$language = $languageData[$surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['language_id']];
			}
			
			$x[$surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['survey_open_ended_question_id']][$surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['candidate_id']][$language] = $surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['answer'];

		}

		foreach ($x as $y => $z) {
			
			$stmnt = $this->getOpenEndedQuestion($y);

			$a[] =  array('question' => $stmnt['statement'], 'id' => $y, 'type' => $stmnt['type'], 'comments' => $z);

		}

		return $a;

	}

	public function segmentWiseComments($segmentId){

		$candidateArr = $candidateIds = $x = $a = array();

		$languageData = $this->Language->find('list',array('fields'=>
				array(
					'Language.id','Language.language_display')
				));
		$preferredLanguageData = $this->Language->find('first',
			array('conditions'=>
				array(
					'Language.for_TI' => 'PREFERRED'),
				'fields'=>
				array(
					'Language.id','Language.language_display')
				));

		$segmentData = $this->SurveySegmentation->find('all', 
			array('conditions'=>
				array(
					'SurveySegmentation.id' => $segmentId),
				'joins'=>
					array(
						array(
							'type' => 'left',
							'alias' => 'SurveySegmentationDetail',
				          	'table' => 'survey_segmentation_details',
				          	'conditions' => 'SurveySegmentationDetail.survey_segmentation_id = SurveySegmentation.id'
				        )
					),
				'fields'=>
				array(
					'SurveySegmentation.survey_id','SurveySegmentation.name','SurveySegmentationDetail.survey_backend_demographic_id','SurveySegmentationDetail.survey_backend_demographic_option_id','SurveySegmentationDetail.type')
				)
			);

		$frontOptArr = $backOptArr = array();

		foreach ($segmentData as $segmentOptKey => $segmentOptValue) {

			if ($segmentOptValue['SurveySegmentationDetail']['type'] == 0) {
				$backOptArr[] = "'".$segmentOptValue['SurveySegmentationDetail']['survey_backend_demographic_option_id']."'";
			}
			if ($segmentOptValue['SurveySegmentationDetail']['type'] == 1) {
				$frontOptArr[] = "'".$segmentOptValue['SurveySegmentationDetail']['survey_backend_demographic_option_id']."'";
			}

		}
		
		$surveyId = $segmentData[0]['SurveySegmentation']['survey_id'];

		$condition = "SurveyOpenEndedQuestion.survey_id = ".$surveyId." and SurveyOpenEndedAnswer.answer != '' and SurveyOpenEndedQuestion.statement != ''";
		if (count($frontOptArr) > 0) {
			$condition .= " and SurveyDemographicAnswer.answer in (".implode(',',$frontOptArr).")";
		}

		if (count($backOptArr) > 0) {
			$condition .= " and DemographicCandidate.survey_backend_demographic_option_id in (".implode(',',$backOptArr).")";
		}

		$surveyOpenEndedAnswerData = $this->SurveyOpenEndedAnswer->find('all', 
			array('conditions'=>
				array($condition),
					'joins'=>
					array(
						array(

							'alias' => 'SurveyOpenEndedQuestion',
				          	'table' => 'survey_open_ended_questions',
				          	'conditions' => 'SurveyOpenEndedQuestion.id = SurveyOpenEndedAnswer.survey_open_ended_question_id'
				        ),
				        array(

							'alias' => 'SurveyOtherLanguageOpenEndedQuestion',
				          	'table' => 'survey_other_language_open_ended_questions',
				          	'conditions' => 'SurveyOtherLanguageOpenEndedQuestion.survey_open_ended_question_id = SurveyOpenEndedQuestion.id'
				        ),
				        array(
				        	
							'alias' => 'SurveyDemographicAnswer',
				          	'table' => 'survey_demographic_answers',
				          	'conditions' => 'SurveyDemographicAnswer.candidate_id = SurveyOpenEndedAnswer.candidate_id'
				        ),
				        array(
				        	
							'alias' => 'DemographicCandidate',
				          	'table' => 'demographic_candidates',
				          	'conditions' => 'DemographicCandidate.candidate_id = SurveyOpenEndedAnswer.candidate_id'
				        )
					),
					'fields'=>
						array(
							'SurveyOpenEndedAnswer.survey_open_ended_question_id','SurveyOpenEndedAnswer.answer','SurveyOpenEndedAnswer.candidate_id','SurveyOpenEndedAnswer.language_id','SurveyOtherLanguageOpenEndedQuestion.statement','SurveyOpenEndedQuestion.template_open_ended_master_id'
						), 
						'group' => 
						array(
							'SurveyOpenEndedAnswer.survey_open_ended_question_id','SurveyOpenEndedAnswer.candidate_id'
						)
					)
				);
		
		foreach ($surveyOpenEndedAnswerData as $surveyOpenEndedAnswerKey => $surveyOpenEndedAnswerValue) {
			
			$language = $preferredLanguageData['Language']['language_display'];
			
			if ($surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['language_id'] != 0) {
				$language = $languageData[$surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['language_id']];
			}
			
			$x[$surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['survey_open_ended_question_id']][$surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['candidate_id']][$language] = $surveyOpenEndedAnswerValue['SurveyOpenEndedAnswer']['answer'];

		}

		foreach ($x as $y => $z) {
			
			$stmnt = $this->getOpenEndedQuestion($y);

			$a[] =  array('question' => $stmnt['statement'], 'id' => $y, 'type' => $stmnt['type'], 'comments' => $z);

		}

		return $a;
	}

	function getOpenEndedQuestion($openEndedId){
		$returnArr = array();
		$surveyOpenEndedQuestionData = $this->SurveyOpenEndedQuestion->find('first', 
			array('conditions'=>
				array('SurveyOpenEndedQuestion.id' => $openEndedId),
					'joins'=>array(
					array(
						'alias' => 'SurveyOtherLanguageOpenEndedQuestion',
			          	'table' => 'survey_other_language_open_ended_questions',
			          	'conditions' => 'SurveyOtherLanguageOpenEndedQuestion.survey_open_ended_question_id = SurveyOpenEndedQuestion.id'
			        )
				),
					'fields'=>
					array('SurveyOpenEndedQuestion.id','SurveyOpenEndedQuestion.statement','SurveyOtherLanguageOpenEndedQuestion.statement','SurveyOpenEndedQuestion.sort')
				)
			);

		if ($surveyOpenEndedQuestionData['SurveyOpenEndedQuestion']['sort'] == 1) {
			$returnArr['type'] = 'Strength';
			$returnArr['statement'] = $surveyOpenEndedQuestionData['SurveyOpenEndedQuestion']['statement'];
		}
		if ($surveyOpenEndedQuestionData['SurveyOpenEndedQuestion']['sort'] == 2) {
			$returnArr['type'] = 'Improvement';
			$returnArr['statement'] = $surveyOpenEndedQuestionData['SurveyOpenEndedQuestion']['statement'];
		}
		if ($surveyOpenEndedQuestionData['SurveyOpenEndedQuestion']['sort'] > 2) {
			$returnArr['type'] = 'Custom'.$surveyOpenEndedQuestionData['SurveyOpenEndedQuestion']['sort'];
			$returnArr['statement'] = $surveyOpenEndedQuestionData['SurveyOpenEndedQuestion']['statement'];
		}

		return $returnArr;
	}

	function checkUserDetail($data){
		
		$redirectClass = 'Authentication';
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		$redirectClassController = new $newClass;		
		$response = $redirectClassController->checkUser($data);
		return $response;
	}

	function updateStatus($data){
		
		$redirectClass = 'UpdateSegmentsAndSurveys';
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		$redirectClassController = new $newClass;	
		$_POST['is_comments_available'] = 1;	
		$response = $redirectClassController->process();
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

		$x = $a = array();
		$x[2356] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a orci nunc. Sed porttitor nisi sed arcu posuere condimentum. Aliquam at sapien massa. Sed id est at magna cursus pretium in ac ex. Vivamus nisl arcu';
		$x[2389] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a orci nunc. Sed porttitor nisi sed arcu posuere condimentum. Aliquam at sapien';
		$x[2410] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a orci nunc. Sed porttitor nisi sed arcu posuere condimentum.';

		$a[] =  array('question' => 'I am strength question', 'id' => 34, 'type' => 'Strength', 'comments' => $x);
		$x[2412] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a orci nunc. Sed porttitor nisi sed arcu posuere condimentum.';
		$a[] =  array('question' => 'I am improvement question', 'id' => 35, 'type' => 'Improvement', 'comments' => $x);
		unset($x[2389]);
		$a[] =  array('question' => 'I am custom 1 question', 'id' => 36, 'type' => 'Custom1', 'comments' => $x);

		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $a,
		);

		return $response;
	}	

}