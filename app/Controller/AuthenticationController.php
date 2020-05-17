<?php
App::uses('AppController', 'Controller');
/**
 * Authentication Controller
 */
class AuthenticationController extends AppController {
	var $uses = array('User','Survey','SurveySegmentation','UserCompany','StudyParticipant','NpCompanyUserSegment');

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('checkUser','headerStatus');

		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');

		$this->url = Router::url('/',true);
	}
	
	public function checkUser($details){
		$returnArr =array();
		if (!empty($details['Username']) && !empty($details['Password'])) {

			$userData = $this->User->find('first', array('conditions'=>array('User.email' => $_POST['Username'], 'User.pword' => md5($_POST['Password']), 'User.active' => 1),'fields'=>array('User.id','User.email')));

			if (count($userData) > 0) {
				$returnArr['IsError'] = false;
	       		$returnArr['error_message'] = "";
			}else {
				$this->headerStatus(401);
				$returnArr['IsError'] = true;
	       		$returnArr['error_message'] = "Data not found.";
			}	
		}else {
				$this->headerStatus(401);
				$returnArr['IsError'] = true;
	       		$returnArr['error_message'] = "Data not found.";
			}
	
		return $returnArr;
	}

	public function checkValidId($details){
		$validData = $returnArr =array();
		if (!empty($details['Username']) && !empty($details['Password'])&& !empty($details['id'])&& !empty($details['type'])) {

			if ($details['type'] == 'survey') {
				$validData = $this->Survey->find('first', array('conditions'=>array('Survey.id' => $_POST['id']),'fields'=>array('Survey.id','Survey.name')));
			}
			if ($details['type'] == 'segment') {
				$validData = $this->SurveySegmentation->find('first', array('conditions'=>array('SurveySegmentation.id' => $_POST['id']),'fields'=>array('SurveySegmentation.id','SurveySegmentation.name')));
			}			

			if (count($validData) > 0) {
				$returnArr['IsError'] = false;
	       		$returnArr['error_message'] = "";
			}else {
				$this->headerStatus(404);
				$returnArr['IsError'] = true;
	       		$returnArr['error_message'] = "Not Found.";
			}	
		}else {
				$this->headerStatus(401);
				$returnArr['IsError'] = true;
	       		$returnArr['error_message'] = "Data not found.";
			}
	
		return $returnArr;
	}

	public function checkValidIdWithUser($details){
		$returnArr =array();

			if ($details['type'] == 'survey') {

				$surveyData = $this->Survey->find('first', array('conditions'=>array('Survey.id' => $details['id']),'fields'=>array('Survey.id','Survey.name','Survey.company_id')));

				$userCompanyData = $this->UserCompany->find('first', array('conditions'=>array('UserCompany.company_id' => $surveyData['Survey']['company_id'],'UserCompany.user_id' => $details['decoded']->data->userId),'fields'=>array('UserCompany.id')));

				if (count($userCompanyData) > 0) {
					$returnArr['IsError'] = false;
					$returnArr['error_message'] = "";
				}else {
					$returnArr['IsError'] = true;
					$returnArr['error_message'] = "";
				}

			}

			if ($details['type'] == 'segment') {
				$segmentData = $this->SurveySegmentation->find('first', array('conditions'=>array('SurveySegmentation.id' => $details['id']),'fields'=>array('SurveySegmentation.id','SurveySegmentation.name','SurveySegmentation.survey_id')));

				$studyParticipantsData = $this->StudyParticipant->find('first', array('conditions'=>array('StudyParticipant.survey_id' => $segmentData['SurveySegmentation']['survey_id']),'fields'=>array('StudyParticipant.id','StudyParticipant.company_id')));

				$userCompanyData = $this->UserCompany->find('first', array('conditions'=>array('UserCompany.company_id' => $studyParticipantsData['StudyParticipant']['company_id'],'UserCompany.user_id' => $details['decoded']->data->userId),'fields'=>array('UserCompany.id')));

				$npCompanyUserSegmentData = $this->NpCompanyUserSegment->find('first', array('conditions'=>array('NpCompanyUserSegment.study_participant_id' => $studyParticipantsData['StudyParticipant']['id'],'NpCompanyUserSegment.segment_id' => $details['id']),'fields'=>array('NpCompanyUserSegment.id')));
				
				if (count($npCompanyUserSegmentData) > 0) {
					$returnArr['IsError'] = false;
					$returnArr['error_message'] = "";
				}else {
					$returnArr['IsError'] = true;
					$returnArr['error_message'] = "";
				}

			}			
	
		return $returnArr;
	}
		
}