<?php
App::uses('AppController', 'Controller');
/**
 * ListOfSegmentsAndSurveys Controller
 */
class ListOfSegmentsAndSurveysController extends AppController {
	var $uses = array('SurveySegmentation','SurveySegmentationDetail','Survey','StudyParticipant','Language','SurveyLanguage','Configuration');

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('test','process','checkUserDetail');

		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');

		$this->url = Router::url('/',true);
	}
	
/* List of all surveys and segments which have not thier word cloud. */
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
		
		$surveyLanguageArr = $arr = $final = array();

		$languageData = $this->Language->find('list',array('fields'=>
				array(
					'Language.id','Language.language_display')
				));
		
		$configurationData = $this->Configuration->find('first',
			array('conditions'=>
				array(
					'Configuration.constant' => 'COUNTRY')
				));

		$preferredLanguageData = $this->Language->find('first',
			array('conditions'=>
				array(
					'Language.for_TI' => 'PREFERRED'),
				'fields'=>
				array(
					'Language.id','Language.language_display')
				));

		$surveyData = $this->Survey->find('all', 
			array('conditions'=>
				array('StudyParticipant.is_report_portal_allowed' => 1, 'Survey.wordcloud_status' => 0, 'Survey.active' => 1, 'Survey.end_date <' => date('Y-m-d h:i:s')),
				'joins'=>array(
					array(
						'alias' => 'StudyParticipant',
			          	'table' => 'study_participants',
			          	'conditions' => 'StudyParticipant.survey_id = Survey.id'
			        )),
				'fields'=>
				array('Survey.id','Survey.name')
				)
			);

		$segmentData = $this->SurveySegmentation->find('all', 
			array('conditions'=>
				array('StudyParticipant.is_report_portal_allowed' => 1, 'SurveySegmentation.wordcloud_status' => 0, 'Survey.active' => 1, 'Survey.end_date <' => date('Y-m-d h:i:s')),
				'joins'=>array(
					array(
						'alias' => 'Survey',
			          	'table' => 'surveys',
			          	'conditions' => 'Survey.id = SurveySegmentation.survey_id'
			        ),
					array(
						'alias' => 'StudyParticipant',
			          	'table' => 'study_participants',
			          	'conditions' => 'StudyParticipant.survey_id = Survey.id'
			        )
			        ),
				'fields'=>
				array('SurveySegmentation.id','SurveySegmentation.name')
				)
			);

		$surveyLanguageData = $this->SurveyLanguage->find('all', 
				array('conditions'=>
					array('SurveyLanguage.active' => 1),
					'joins'=>array(
						array(
							'alias' => 'Language',
				          	'table' => 'languages',
				          	'conditions' => 'Language.id = SurveyLanguage.language_id'
				        )),
					'fields'=>
					array('Language.id','Language.language_display','SurveyLanguage.survey_id')
					)
				);	

		foreach ($surveyLanguageData as $slkey => $slvalue) {
			$surveyLanguageArr[$slvalue['SurveyLanguage']['survey_id']][] = $slvalue['Language']['language_display'];
		}

		foreach ($surveyData as $surveykey => $surveyValue) {
			$arr = array();
			$arr['id'] = $surveyValue['Survey']['id'];
			$arr['type'] = 'survey';
			$arr['language'] = $preferredLanguageData['Language']['language_display'];
			$arr['country'] = $configurationData['Configuration']['value'];
			$final[] = $arr;
		}

		
		foreach ($segmentData as $segmentKey => $segmentValue) {
			$arr = array();
			$arr['id'] = $segmentValue['SurveySegmentation']['id'];
			$arr['type'] = 'segment';
			$arr['language'] = $preferredLanguageData['Language']['language_display'];
			$arr['country'] = $configurationData['Configuration']['value'];
			$final[] = $arr;
		}
		

		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $final,
		);

		return $response;
	}

	function checkUserDetail($data){
		
		$redirectClass = 'Authentication';
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		$redirectClassController = new $newClass;		
		$response = $redirectClassController->checkUser($data);
		return $response;
	}

	public function test(){

		$arr = $final = array();
		$arr['type'] = 'segment';
		$arr['id'] = '101';
		$final[] = $arr;

		$arr = array();
		$arr['type'] = 'segment';
		$arr['id'] = '105';
		$final[] = $arr;

		$arr = array();
		$arr['type'] = 'survey';
		$arr['id'] = '50';
		$final[] = $arr;

		$arr = array();
		$arr['type'] = 'survey';
		$arr['id'] = '76';
		$final[] = $arr;

		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $final,
		);

		return $response;
	}
}