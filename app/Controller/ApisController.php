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


	public $uses = array('BenchmarkSurvey','NpUser','NpLoginLog','NpCompanyUserBenchmark','StudyParticipant','Company','NpKpi','PracticeCategory','NpCompanyUserIdentifier','SurveyBackendDemographic','Study','LoginLog','NpUserCompany','NpLanguage','SurveyBackendDemographicOption', 'EmployeeComment', 'SurveyOpenEndedQuestion', 'Dimension','NpEmailTemplate','NpEmailJobDetail','NpEmailJob', 'BestPeoplePractice', 'Practice', 'NpSurveyDemographicOptionMapping', 'SurveyDemographicOption', 'SurveyBackendDemographicOption','Language', 'NpPortalConfig', 'NpPortalConfigDetail', 'NpPortalConfigDetailsOtherLanguage', 'NpPortalConfigsOtherLanguage', 'SurveyDemographic', 'Survey', 'SurveyLanguage','SurveySegmentation','SurveySegmentationDetail','PracticeGroup','NpCompanyUserSegment','Benchmark','Configuration','SegmentMapping','SegmentTarget','OrganizationalTarget','User','UserCompany','Role','UsersRolesEmailTemplate');


	public function beforeFilter() {
		$this->Security->unlockedActions = array('getSummary','login','changeLanguage','logOff','segmentos','viewLanguage','forgotPassword','randomPassword', 'leftMenu','getLoginView','getForgetPwdView', 'FunctionName', 'setError','updateCompany','updateSurvey');
		$this->nonAuthorizedActions = array('login', 'logOff');
		parent::beforeFilter();

		// Set a single header
		//$this->response->header('Access-Control-Allow-Origin', '*');
		$this->url = Router::url('/',true);
		ini_set('max_execution_time',36000);
		ini_set('memory_limit',-1);
	}

	public function getSummary() {

		$data = array();
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		$clientName = $this->getClientName('CLIENT_NAME');
		// Initialization of Data
		$this->userId = $this->Session->read('User.id');
		$this->userType = $this->Session->read('User.type');
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		
		if (!in_array('rp_group_company_admin', $this->userRoles) and !in_array('rp_company_admin', $this->userRoles)) {
			$this->defualtSegment = $this->Session->read('UserSegment.segment_id');
		}
		$this->defualtStudyId = $this->Session->read('User.study_id');
		$this->segmentId = isset($this->request->data['segment_id'])?$this->request->data['segment_id']:0;
		if (empty($this->segmentId) and (!in_array('rp_group_company_admin', $this->userRoles) and !in_array('rp_company_admin', $this->userRoles)) and !empty($this->defualtSegment)) {
			$this->segmentId = $this->defualtSegment;
		}
		//If userType is not company admin or admin then segmentId is compulsory
		if (empty($this->segmentId) and in_array('rp_segment_leader', $this->userRoles)) {
			return $this->setError('Invalid User');
		}

		$this->companyData = $this->Company->find('first',
			array(
				'conditions' => array('Company.id' => $this->companyId)
				));
		$this->studyId = isset($this->request->data['study_id'])?$this->request->data['study_id']:0;
		if (in_array('rp_segment_leader', $this->userRoles)) {
			if ($this->studyId != $this->defualtStudyId) {
					$this->segmentId = $this->request->data['segment_id'];
					}
			if (isset($this->request->data['study_id'])) {
				$this->segmentId = $this->request->data['segment_id'];
			}
		}
		/*historical tab hide if data is not present*/
		$studyParticipantData = $this->StudyParticipant->find('first',array('conditions'=>array('StudyParticipant.company_id' => $this->companyId,'StudyParticipant.study_id' => $this->studyId),'fields'=>array('StudyParticipant.id', 'StudyParticipant.survey_id', 'StudyParticipant.historic_access')));
		// Check filter
		$this->filter = isset($this->request->data['filter'])?$this->request->data['filter']:array();

		$userData = $this->User->find('first',array('conditions'=>array('User.id' => $this->userId),'fields'=>array('User.id', 'User.group_company_id')));
		//check if user belogs to any study or not
		if (empty($userData)) {
			return $this->setError('Invalid User Id.');
		}
		
		if ($this->companyId != 0) {
			$studyParticipantData = $this->StudyParticipant->find('first',array('conditions'=>array('StudyParticipant.study_id'=>$this->studyId, 'StudyParticipant.company_id'=>$this->companyId)));

			$this->studyParticipantData = $studyParticipantData;
			$this->studyParticipantId = $studyParticipantData['StudyParticipant']['id'];
			$surveyArr[] = isset($studyParticipantData['StudyParticipant']['survey_id'])?$studyParticipantData['StudyParticipant']['survey_id']:0;
		}
		if (in_array('rp_group_company_admin',$this->userRoles) and $this->companyId == 0){

			$companyList = $this->UserCompany->find('list',array('conditions'=>array('user_id'=>$this->userId),'fields'=>array('company_id','company_id')));

			$studyParticipantData = $this->StudyParticipant->find('all',array('conditions'=>array('StudyParticipant.study_id'=>$this->studyId,'is_report_portal_allowed'=>1,'StudyParticipant.company_id'=>$companyList)));
			$this->studyParticipantData = $studyParticipantData;

			foreach ($studyParticipantData as $dataKey => $dataValue) {
				$studyParticipantIdArr[$dataValue['StudyParticipant']['id']] = $dataValue['StudyParticipant']['id'];
			}

			$this->studyParticipantIdArr = $studyParticipantIdArr;

			foreach ($studyParticipantData as $key => $data1) {
				$surveyArr[$data1['StudyParticipant']['survey_id']] = isset($data1['StudyParticipant']['survey_id'])?$data1['StudyParticipant']['survey_id']:0;
			}

			$groupCompanyIdArr = $this->User->find('first',array('conditions'=>array('User.id'=>$this->userId)));
			$this->groupCompanyId = $groupCompanyId = $groupCompanyIdArr['User']['group_company_id'];

			$groupStudyParticipantsId = $this->StudyParticipant->find('first',array('conditions'=>array('StudyParticipant.study_id'=>$this->studyId,'StudyParticipant.company_id'=>$groupCompanyId)));
			if (empty($groupStudyParticipantsId)) {
				$groupStudyParticipantsId = $this->StudyParticipant->find('first',array('conditions'=>array('StudyParticipant.company_id'=>$groupCompanyId)));
			}

			$this->studyParticipantId = $groupStudyParticipantsId['StudyParticipant']['id'];

		}

		$this->surveyId = !empty($surveyArr)?$surveyArr:array();

		if (!empty($this->segmentId) && !is_null($this->segmentId)) {
			/*$segmentationData = $this->SurveySegmentation->find('first',array('conditions'=>array('SurveySegmentation.id'=>$this->segmentId)));*/
				$npSegmentBackendData = $this->NpCompanyUserSegment->find('all',array('conditions' => array('NpCompanyUserSegment.segment_id' => $this->segmentId),'joins' => array(array('alias' => 'SurveySegmentationDetail','table' => 'survey_segmentation_details','type' => 'inner','conditions' => 'NpCompanyUserSegment.segment_id = SurveySegmentationDetail.survey_segmentation_id and SurveySegmentationDetail.type = 0')),'fields' => array('NpCompanyUserSegment.id','SurveySegmentationDetail.survey_backend_demographic_id','SurveySegmentationDetail.survey_backend_demographic_option_id','NpCompanyUserSegment.segment_id','SurveySegmentationDetail.type')));
				
				foreach ($npSegmentBackendData as $npSegmentDataKey => $npSegmentDataValue) {
					foreach ($npSegmentDataValue as $npSegmentDataValueKey => $npSegmentDataValues) {
						$this->filter['backdemographic_id'][$this->getBackDemoData($npSegmentDataValues['survey_backend_demographic_id'])][] = $this->getBackDemoOptData($npSegmentDataValues['survey_backend_demographic_option_id']);
					}
				}

				$npSegmentFrontendData = $this->NpCompanyUserSegment->find('all',array('conditions' => array('NpCompanyUserSegment.segment_id' => $this->segmentId),'joins' => array(array('alias' => 'SurveySegmentationDetail','table' => 'survey_segmentation_details','type' => 'inner','conditions' => 'NpCompanyUserSegment.segment_id = SurveySegmentationDetail.survey_segmentation_id and SurveySegmentationDetail.type = 1')),'fields' => array('NpCompanyUserSegment.id','SurveySegmentationDetail.survey_backend_demographic_id','SurveySegmentationDetail.survey_backend_demographic_option_id','NpCompanyUserSegment.segment_id','SurveySegmentationDetail.type')));
				foreach ($npSegmentFrontendData as $npSegmentDataKey => $npSegmentDataValue) {
					foreach ($npSegmentDataValue as $npSegmentDataValueKey => $npSegmentDataValues) {
						$this->filter['demographic_id'][$this->getFrontDemoData($npSegmentDataValues['survey_backend_demographic_id'])][] = $this->getFrontDemoOptData($npSegmentDataValues['survey_backend_demographic_option_id']);
					}
				}
			}

		//$resp = $this->StudyParticipant->find('first',array('conditions'=>array('id'=>$this->studyParticipantId),'fields'=>array('ec_evaluation_status')));
		$ec_evaluation_status = $studyParticipantData['StudyParticipant']['ec_evaluation_status'];
	
		// Requested Widget key
		$widget = isset($this->request->data['widget'])?$this->request->data['widget']:array();
		$this->seg_page = isset($this->request->data['seg_page'])?$this->request->data['seg_page']:'';
		$this->limitSize = isset($this->request->data['limitSize'])?$this->request->data['limitSize']:'';
		// Validation
		if (empty($this->surveyId)) {
			return $this->setError('Survey Id is empty.');
		}
		/*
			Get the language id from session
		 */
		if ($this->Session->check('Config.languageId')) {
			$this->languageId = $this->Session->read('Config.languageId');
		}
		/* Load setting from data base per clinet's requrement 
		in the class variable settings*/
		$this->loadSettings();
		
		$allWidgetArr = array(
			'study_data' => 'app\\StudyData',
			'segment_data' => 'app\\SegmentData',
			'benchmark_info' => 'app\\BenchmarkInfo',
			'demographic_info' => 'app\\DemographicInfo',
			'gptw_model' => 'app\\GPTWModel',
			'open_ended_model' => 'app\\OpenEndedData',
			'giftwork_model' => 'app\\GiftworkModel',
			'responses_distribution' => 'app\\DistributionResponsesData',
			'general_indices' => 'app\\GeneralIndices',
			'demographic_data' => 'app\\DemographicData', 
			'survey_data' => 'app\\SurveyDetail',
			'dimension_data' => 'app\\DimensionData',
			'practices_data' => 'app\\PracticesData',
			'ca_data' => 'app\\CaData',
			'gaps_data' => 'app\\GapDimensionData',
			'hist_demo' => 'app\\HistoricalData',
			'benchmark_data' => 'app\\BenchmarkData',
			'indices_data' => 'app\\IndicesData',
			'good_practices_data' => 'app\\GoodPracticeData',
			'static_general_data' => 'app\\StaticGeneralData',
			'static_model_data' => 'app\\StaticModelData',
			'static_faq_data' => 'app\\StaticFAQData',
			'static_ti_summary' => 'app\\StaticTISummary',
			'open_comment' => 'app\\OpenCommentData',
			'segment_visualization' => 'app\\SegmentVisualization',
			'company_data' => 'app\\CompanyData'
		);

		$widgetArr = array();
		
		if (count($widget) > 0) {

			//we are hiding segment visualization and reports. comment code to show these
			unset($allWidgetArr['open_ended_model']);			
			unset($allWidgetArr['ca_data']);	
			unset($allWidgetArr['demographic_data']);		
			if ($ec_evaluation_status == 3 || $ec_evaluation_status == 5) {
				$allWidgetArr['ca_data'] = 'app\\CaData';
			}
			
			if (!in_array('rp_group_company_admin', $this->userRoles)) {
				unset($allWidgetArr['company_data']);
			}
			else{
				$grpCompanyData = $this->Company->find('first',array('conditions'=>array('id'=>$userData['User']['group_company_id']),'fields'=>array('company_name')));
				$data['group_company_admin_name'] =  $grpCompanyData['Company']['company_name'].' - Consolidated';
				$this->groupCompanyId = $userData['User']['group_company_id'];
			}
				$data['unset'] = 1;
			if (in_array('rp_group_company_admin', $this->userRoles) and $this->companyId == 0) {
					$data['unset'] = 0;

				unset($allWidgetArr['segment_visualization']);
				unset($allWidgetArr['general_indices']);
				unset($allWidgetArr['indices_data']);
			}

			foreach ($widget as $wKey) {
				// Configure widgets as, data_key => class
				if ($wKey == 'demographic_data') {
					unset($allWidgetArr['demographic_data']);
				}
				if (isset($allWidgetArr[$wKey])) {
					$widgetArr[$wKey] = $allWidgetArr[$wKey];
				}
			}
		}

		// Load widget classes
		foreach ($widgetArr as $key => $class) {
			$objMyWidget = new $class($this);
			$data[$key] = $objMyWidget->getWidget();
		}
		if ($this->companyId == 0) {
			$data['historic_access'] = 1;
		}
		if ($this->companyId != 0) {
			$data['historic_access'] = $studyParticipantData['StudyParticipant']['historic_access'];
		}
		// return response
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);

		$this->set(array('response' => $response, '_serialize' => array('response')));
	}

	//To render left menu independent of survey and company
	public function label()
	{

		$this->companyId = isset($this->request->query['company_id'])?$this->request->query['company_id']:0;
		$this->userType = $this->Session->read('User.type');
		$this->studyId = isset($this->request->data['study_id'])?$this->request->data['study_id']:0;

		/*$studyParticipantData = $this->StudyParticipant->find('first',array('conditions'=>array('StudyParticipant.study_id'=>$this->studyId, 'StudyParticipant.company_id'=>$this->companyId)));
		$this->studyParticipantData = $studyParticipantData;*/

		$this->companyData = $this->Company->find('first',
			array(
				'conditions' => array('Company.id' => $this->companyId),
				'fields' => array('Company.bp_access')
				));
		
		$class = 'app\\Label';
		$objMyWidget = new $class($this);
		$data = $objMyWidget->getWidget();
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);

		$this->set(array('response' => $response, '_serialize' => array('response')));
	}

	public function login()
	{
		$data = array();
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		// Get Username and password
		$username = isset($this->request->data['username'])?$this->request->data['username']:'';
		$password = isset($this->request->data['password'])?$this->request->data['password']:'';
		
		// Check if username, password empty
		if (empty($username) || empty($password)) {
			return $this->setError('Invalid Username/Password.');
		}
		// Check if username is existed in sytem
		$cmUserData = $this->User->find('all',array(
			'conditions'=>array(
					'User.email'=> $username,
			),
			'joins'=>array(
				array(
					'alias' => 'UserRole',
		          	'table' => 'users_roles',
		          	'conditions' => 'User.id = UserRole.user_id'
		        ),
	        	array(
					'alias' => 'Role',
		          	'table' => 'roles',
		          	'conditions' => 'UserRole.role_id = Role.id'
	        	),
	        	array(
					'alias' => 'UserCompany',
		          	'table' => 'user_companies',
		          	'conditions' => 'User.id = UserCompany.user_id'
	        	),
			),
			'fields'=>array('User.*','Role.role','Role.id','UserCompany.company_id'),
			'order'=>array('User.id'),
		));
		
		$login = array();
		foreach ($cmUserData as $key => $value) {
			$login['User'] = $value['User'];
			$login['Role'][$value['Role']['role']] = $value['Role']['role'];
			$login['UserCompany'][$value['UserCompany']['company_id']] = $value['UserCompany']['company_id'];
		}
		//study Id
		$studyPData = $this->StudyParticipant->find('first',array(
			'conditions'=>array('company_id'=>$login['UserCompany'], 'is_report_portal_allowed'=>1),
			'joins'=>array(
				array(
					'alias' => 'Study',
		          	'table' => 'studies',
		          	'conditions' => 'Study.id = StudyParticipant.study_id'
		        ),
		    ),
		    'fields'=> array('study_id'),
		    'order'=>array('list_year desc')
		));
		$userName = $login['User']['name'];
		if ( in_array('rp_company_admin', $login['Role']) || in_array('rp_group_company_admin', $login['Role']) || in_array('rp_segment_leader', $login['Role']) ) {
					$roleFlag = true;
		}
		
		if(empty($login)){
			return $this->setError('Username does not exist.');
		}
		elseif ( $roleFlag == false ) {
				return $this->setError('You have not given access of report portal, kindly contact administration.');
		}elseif ($login['User']['active'] != 1) {
			return $this->setError('This user is blocked, Please contact administrator.');
		}elseif (md5($login['User']['pword']) != trim($password)) {
			$this->request->data['password'] = '';
			//to check the Session
			if($this->Session->check('invalid'))
				$a = $this->Session->read('invalid');
			else
				$a = array();
			$a[] = $username;
			$this->Session->write('invalid', $a);
			
			$total = array_count_values($this->Session->read('invalid'));
			if(array_key_exists($username, $total)){
				if($total[$username] >= 10){
					$primData = array();
					$primData['User']['active']=2;
					$primData['User']['id'] = $login['User']['id'];
					$this->User->save($primData['User'],false,array('id','active'));
					$this->log_entry('10 Times Login Fail',11);
					$a = $this->Session->read('invalid');
					foreach($a as $key=>$value){
						if($value == $username)
							unset($a[$key]);
					}
					$this->Session->write('invalid', $a);
				}
			}
			return $this->setError('Invalid Password.');
		}elseif (!empty($username) && !empty($password)) {
			if (!in_array('rp_group_company_admin',$login['Role'])) {
				$participantData = $this->StudyParticipant->find('first', array('conditions' => array('StudyParticipant.company_id' => $login['UserCompany'], 'StudyParticipant.is_report_portal_allowed' => 1), 'joins' =>array(array('alias'=>'Study','table'=>'studies','type' => 'inner','conditions'=>'StudyParticipant.study_id = Study.id')),
				    'fields' => array('StudyParticipant.id', 'StudyParticipant.study_id'),'order' => array('Study.list_year DESC'),));
				$latestStudyYear = $participantData['StudyParticipant']['study_id'];
			}
			else{
				$participantData = $this->StudyParticipant->find('all', array('conditions' => array('StudyParticipant.company_id' => $login['UserCompany'], 'StudyParticipant.is_report_portal_allowed' => 1), 'joins' =>array(array('alias'=>'Study','table'=>'studies','type' => 'inner','conditions'=>'StudyParticipant.study_id = Study.id')),
				    'fields' => array('StudyParticipant.id', 'StudyParticipant.study_id'),'order' => array('Study.list_year DESC'),));
				$latestStudyYear = $participantData[0]['StudyParticipant']['study_id'];
			}
			$resetArray = $login['UserCompany'];
			reset($resetArray);
			$companyId =  (!empty($login['Role']) and (in_array('rp_company_admin', $login['Role']) || (in_array('rp_segment_leader', $login['Role']) ))) ? key($resetArray) : 0 ;
			$segmentId =0;
			if (in_array('rp_segment_leader',$login['Role']) ) {
				$segments= $this->NpCompanyUserSegment->find('first',array('conditions'=> 
					array('NpCompanyUserSegment.user_id' => $login['User']['id']
					),'fields' => array('NpCompanyUserSegment.id', 'NpCompanyUserSegment.segment_id', 'StudyParticipant.id', 'StudyParticipant.study_id'),
					  'order' => array('NpCompanyUserSegment.id' => 'asc'),
					'joins' =>
						array(
							array('alias'=>'StudyParticipant',
							    'table'=>'study_participants',
							    'type' => 'inner',
							    'conditions'=>'StudyParticipant.id = NpCompanyUserSegment.study_participant_id')),
					    )
				);
				//If a user is not a company_admin he is not allowed to without segement
				if (empty($segments)) {
					return $this->setError(__d('front_end', 'Invalid user, segment permissions not given'));
				}
				// For non company_admin user 
				$latestStudyYear = $segments['StudyParticipant']['study_id'];
				$segmentId  = (isset($segments['NpCompanyUserSegment']['segment_id'])) ? $segments['NpCompanyUserSegment']['segment_id'] : 0 ;
			}
			unset($_SESSION['salt']);
			$this->Session->destroy();
			$this->Session->renew();

			$this->Session->write('User.id', $login['User']['id']);
			$this->Session->write('User.study_id', $latestStudyYear);
			$this->Session->write('User.name', $userName);
			$loginLogUser = md5($this->randomPassword(12));
			$this->Session->write('loginLogUser', $loginLogUser);
			$userId = $login['User']['id'];
			$clientIpAddress = $this->getRealIpAddr(false);
			$completeDate = date("Y-m-d H:i:s", strtotime('now'));
			//lang setting
			$langPref = $this->getDefaultLang();
			$this->langKeyDefault = isset($langPref['PREFERRED']) ? $langPref['PREFERRED']['language_short_code'] : $langPref['Default']['language_short_code'];
			$this->langIdDefault = isset($langPref['PREFERRED']) ? $langPref['PREFERRED']['id'] : $langPref['Default']['id'];

			Configure::write('Config.language', $this->langKeyDefault);
			$this->Session->write('Config.language', $this->langKeyDefault);
			$this->Session->write('Config.languageId', $this->langIdDefault);

			// Login log
			$this->NpLoginLog->query("INSERT INTO np_login_logs(user_id,ip,ip_address,np_user_agent,start_time) VALUES ({$userId},INET_ATON('".$_SERVER['REMOTE_ADDR']."'),'{$clientIpAddress}','{$loginLogUser}','{$completeDate}')");

			// Send Data in response
			$this->Session->renew();$this->Session->write('User.id', $login['User']['id']);
			$this->Session->write('User.study_id', $latestStudyYear);
			$this->Session->write('User.name', $userName);
			$loginLogUser = md5($this->randomPassword(12));
			$this->Session->write('loginLogUser', $loginLogUser);

			$forSurveyData = $this->StudyParticipant->find('first', array('conditions' => array('StudyParticipant.company_id' => $companyId, 'StudyParticipant.study_id' => $latestStudyYear),'fields' => array('StudyParticipant.id', 'StudyParticipant.survey_id')));

			$data['survey_id'] = $forSurveyData['StudyParticipant']['survey_id'];
			$data['id'] = $login['User']['id'];
			$data['study_id'] = $latestStudyYear;
			if (in_array('rp_segment_leader',$login['Role'])) {
				$data['segmentId'] = $segmentId;
			} else {
				$data['segmentId'] = '0';
			}
			$this->Session->write('UserSegment.segment_id', $data['segmentId']);
		}
		if (in_array('rp_group_company_admin',$login['Role'])){
			$data['group_company_admin_flag'] = 1;
		}
		else{
			$data['group_company_admin_flag'] = 0;
		}
		$data['company_id'] = $companyId;
		// return response
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);

		$this->set(array('response' => $response, '_serialize' => array('response')));
	}

	public function viewLanguage() {

		$data = array();
		$this->companyId = isset($this->request->query['company_id'])?$this->request->query['company_id']:0;
		$this->studyId = isset($this->request->query['studyId'])?$this->request->query['studyId']:0;
		// Initialization of Data
		$this->userId = $this->Session->read('User.id');
		
		if ($this->companyId == 0) {
			$surveyId = $this->User->find('list',
				array(
					'conditions'=>array('User.id'=> $this->userId),
					'joins'=>array(
						array(
							'table'=>'user_companies',
							'alias'=>'UserCompany',
							'conditions'=> 'User.id = UserCompany.user_id',
						),
						array(
							'table'=>'study_participants',
							'alias'=>'StudyParticipant',
							'conditions'=> 'UserCompany.company_id = StudyParticipant.company_id and StudyParticipant.is_report_portal_allowed = 1',
						)
					),
					'fields'=>array('StudyParticipant.survey_id','StudyParticipant.survey_id')
				)
			);
		}
		else{
			$surveyId = $this->StudyParticipant->find('list',
				array(
					'conditions'=>array('StudyParticipant.company_id'=> $this->companyId, 'StudyParticipant.study_id'=> $this->studyId),
					'fields'=>array('StudyParticipant.survey_id','StudyParticipant.survey_id')
				)
			);
		}
		
		if (empty($surveyId)) {
			return $this->setError('Invalid User Id.');
		}

		//language option auto-populate
		 $languageData = $this->SurveyLanguage->find('all',array(
		 	'fields' => array(
		 		'Language.language_short_code',
		 		'Language.language_display'),
		 	'conditions' => array(
		 		'SurveyLanguage.survey_id' => $surveyId,
		 		'Language.for_report_portal' => 1,
		 		'SurveyLanguage.active' => 1
		 		),'joins'=>array(array('table' =>'languages', 'alias'=>'Language', 'conditions'=>'SurveyLanguage.language_id = Language.id','type'=>'inner')))); 
	
			foreach ($languageData as $opt) {
				$data['page_info']['options'][$opt['Language']['language_short_code']] = $opt['Language']['language_display'];
			}
		$data['label']['label_select']= __d('front_end', 'Select Language');
		$data['label']['label_btn_submit']= __d('front_end', 'Submit');
		$data['label']['label_btn_cancel']= __d('front_end', 'Cancel');
		$data['page_info']['selected_language'] = $this->Session->read('Config.language');
		// return response
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);

		$this->set(array('response' => $response, '_serialize' => array('response')));
	}
	public function changeLanguage() {

		$data = array();
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		// Initialization of Data
		$this->userId = $this->Session->read('User.id');
		$this->studyId = 3;

		$userData = $this->User->findById($this->userId);

		if (empty($userData)) {
			return $this->setError('Invalid User Id.');
		}
		$default = $this->getDefaultLang();
		$defaultLanuage = $default['DEFAULT']['language_short_code'];
		$language = isset($this->request->data['language'])?$this->request->data['language']:$defaultLanuage;
		
		$lang = $this->Language->find('list', array(
       		'fields' => array('Language.language_short_code'),
       		'order'=>array('Language.id desc')
       	));
       	$data['title'] = __d('front_end', 'Language');
       
       	if (!array_search($language, $lang)) {
   			return $this->setError('Enter valid language.');
		}
		// Check if valid language
		if (empty($language)) {
			return $this->setError('Enter language.');
		}

		// Set language
		Configure::write('Config.language', $language);
		$this->Session->write('Config.language', $language);
		$this->Session->write('Config.languageId', array_search($language, $lang));
		
		
		$data['language'] = $language;

		// return response
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);

		$this->set(array('response' => $response, '_serialize' => array('response')));
	}
	
	public function setError($msg = 'Some error occured.', $options = array()) {
		$errorType = 'display';
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		if (isset($options['errorType'])) {
			$errorType = $options['errorType'];
		}

		if (isset($this->pass)) {
			$msg = $this->pass['msg'];
			if (isset($this->pass['options']['errorType'])) {
				$errorType = $this->pass['options']['errorType'];
			}			

		}

		$response = array(
			'is_error' => true,
			'error_message' => $msg,
			'error_type' => $errorType,
			'data' => $this->request->data,
		);
		return $this->set(array('response' => $response, '_serialize' => array('response')));
	}

	public function logOff() {

		$data = array();
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;

		// Initialization of Data
		if($this->Session->check('User')){
			$userType = $this->Session->read('User.type');
			$userId = $this->Session->read('User.id');
			$loginLogUser = $this->Session->read('loginLogUser');
			$this->Session->destroy();
			if (in_array('rp_admin', $this->userRoles) || in_array('rp_reviewer', $this->userRoles) || in_array('rp_consultant', $this->userRoles)){
				$this->Session->destroy();
				$this->NpLoginLog->query("INSERT INTO np_login_logs(user_id,end_time,np_user_agent) VALUES ({$userId},now(),'{$loginLogUser}') on duplicate key update `end_time`= values(`end_time`)");
				$this->redirect('/');		
			}
			if (in_array('evaluation_admin', $this->userRoles) || in_array('evaluation_user', $this->userRoles)) {
				$this->Session->destroy();
				$this->NpLoginLog->query("INSERT INTO np_login_logs(user_id,end_time,np_user_agent) VALUES ({$userId},now(),'{$loginLogUser}') on duplicate key update `end_time`= values(`end_time`)");
				$this->redirect('/evaluation');		
			}
			if (in_array('rp_group_company_admin', $this->userRoles) || in_array('rp_segment_leader', $this->userRoles)) {
				$this->Session->destroy();
				$this->NpLoginLog->query("INSERT INTO np_login_logs(user_id,end_time,np_user_agent) VALUES ({$userId},now(),'{$loginLogUser}') on duplicate key update `end_time`= values(`end_time`)");
			}
		}		
		$this->Session->destroy();
		// return response
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);

		$this->set(array('response' => $response, '_serialize' => array('response')));
	}
	public static function randomPassword($length, $available_chars = 'ABDEFHKMNPRTWXYABDEFHKMNPRTWXY23456789') {
    	$chars = preg_split('//', $available_chars, -1, PREG_SPLIT_NO_EMPTY);
    	$char_count = count($chars);
    	$out = '';
    	for($ii = 0; $ii < $length; $ii++) {
    		$out .= $chars[rand(1, $char_count)-1];
    	}
    	return $out;
  	}
	function forgotPassword() {
		$data = array();
		//$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		$clientName = $this->getClientName('CLIENT_NAME');
		$submittedEmail = isset($this->request->data['email'])?$this->request->data['email']:'';
		if (empty($submittedEmail)) {
			return $this->setError(__d('front_end', 'Please enter Email.'));
		}

		$existingData = $this->User->find('first',array('conditions'=>array('User.email'=>$submittedEmail), 'fields'=>array('id','email','name')));
		$userCompanyData = $this->UserCompany->find('all',
			array('conditions'=>
				array('UserCompany.user_id'=>$existingData['User']['id']
					),
				'joins'=>array(
						array(
							'alias' => 'Company',
				          	'table' => 'companies',
				          	'conditions' => 'Company.id = UserCompany.company_id'
			        	)
					),
				'fields'=>array('Company.id', 'Company.company_name')
				)
			);
		$assignCompanyArr = array();
		foreach ($userCompanyData as $key => $value) {
			$assignCompanyArr[$value['Company']['id']] = $value['Company']['id'];
		}	
		$assignCompanyStr = implode(",", $assignCompanyArr);
		$existingName = $existingData['User']['name'];

		$flag=true;
		if(empty($existingData)){
			$flag = false;
			return $this->setError(__d('front_end', 'Entered Email not found in the system.'));	
		}
		$user = $this->User->getDataSource();
		$user->begin($this);			
		if($flag==true && !empty($existingData)){
			$existingEmail = $existingData['User']['email'];
			$newPassword = 'Av#';
			$newPassword .= $this->randomPassword(8);
			$newPassword .= '$';
			//TO UPDATE User
			$this->User->create();
			$this->request->data['User']['id'] = $existingData['User']['id'];
			$this->request->data['User']['pword'] = md5($newPassword);
			$this->request->data['User']['pword_txt'] = $newPassword;
			if(!$this->User->save($this->request->data['User'],true,array('id','pword'))){
				$flag = false;
				return $this->setError(__d('front_end', 'Password could not be changed.', true));
			}
			if($flag==true){
				if (!empty($existingData['User']['id'])) {

				$templateData = $this->UsersRolesEmailTemplate->find('first',array('conditions'=>
						array('Role.role'=>array('rp_segment_leader')),
					'joins'=>array(
						array(
							'alias' => 'Role',
				          	'table' => 'roles',
				          	'conditions' => 'UsersRolesEmailTemplate.role_id = Role.id'
			        	)
					),
					'fields'=>'UsersRolesEmailTemplate.template_id'
					)
				);
				if (empty($templateData)) {
					$templateId = null;
				}
				else{
					$templateId = $templateData['UsersRolesEmailTemplate']['template_id'];
				}
		       	$returnArray = $this->sendLoginMail($existingData['User']['id'],$clientName,$templateId,$newPassword);
		       	$data['message'] = __d('front_end', "New Password has been sent on your mail id.");
				$user->commit($this);
				$this->logEntry("Forget Password of User $existingName.", 2, $assignCompanyStr);
				//Redirection
				}				
					
			}
			else{

				$user->rollback($this);
			}
		}
			$response = array(
				'is_error' => false,
				'error_message' => '',
				'data' => $data,
			);
			$this->set(array('response' => $response, '_serialize' => array('response')));
		}	
		
	public function sendLoginMail($npUserLastId = null,$admin ,$templateId = null) {
	
		$data = array('id' => $npUserLastId);
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		// Initialization of Data
		$this->userId =  $npUserLastId;
		$id = $this->userId;
		$this->npUserType = isset($this->request->data['type'])?$this->request->data['type']:'';
		$options = array(
			'fields' => array(
			'User.id'
			),
			'conditions' => array(
				'id' => $id,
			),
		);
		$npUserData = $this->User->find('all', $options);

		if (empty($npUserData)) {
			return $this->setError('Invalid User Id.');
		}
				
		// Validation
			$emailData= $this->NpEmailTemplate->find('first', array('conditions'=>array('NpEmailTemplate.id'=>$templateId )));			
			$subject = $emailData['NpEmailTemplate']['subject'];
			$content = $emailData['NpEmailTemplate']['content'];

			$flag = true;
			$msg = __d('front_end', 'Login details has been sent successfully.');
			$fields = array('User.name','User.email', 'User.phone');
		    $userData = $this->User->read($fields,$id);		
			$userData['User']['subject']=$subject;
			$userData['User']['content']=$content;
			$userData['User']['user_id']=$id;
			$userData['User']['password']= $random;
			$userData['User']['created'] = date('Y-m-d H:i:s');
			$result = array();
		    $ReceiverArr['user_id'] = $id;
		    $ReceiverArr['tempName'] = $emailData['NpEmailTemplate']['name'];
		    $ReceiverArr['user_name'] = $admin;
		    $to[] = $userData['User'];
		    $ReceiverArr['to'] = json_encode($to);
		    $ReceiverArr['content_data'] = $userData['User'];
			App::import('Vendor', 'SendEmail', array('file' => 'classes/SendEmail.php'));
		    $sendingMail = new SendEmail();
		    $result = $sendingMail->send_email_process($ReceiverArr);
		    $emailToDetails = $result['details'];
		    $lastJobDetailId = $result['lastInsertId'];
		    if (!empty($lastJobDetailId)) {
		      App::import('Controller', 'SaveEmailDetails');
		      $EventController = new SaveEmailDetailsController;
		      $returnArr = $EventController->sendMailProcess($emailToDetails,$lastJobDetailId);
				// Redirection
				if ($flag == true) {
					$message= __d('front_end', 'Login Details Sent to newly created User');
				} 
				else {
					$message= __d('front_end', 'Failed to send Login Details');
				}
			}
		}
	public function getLoginView() { 

		$data = array();
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		// Initialization of Data
			$data['label']['title'] = __d('front_end', 'Trust Index Results');
			$data['label']['email'] = __d('front_end', 'Email');
			$data['label']['password'] = __d('front_end', 'Password');
			$data['label']['button_text'] = __d('front_end', 'Enter');
			$data['label']['label_remember'] = __d('front_end', 'Remember me');
			$data['label']['link_forgot'] = __d('front_end', 'Forgot Password');
			$data['label']['validate_email'] = __d('front_end', 'Enter a valid email');
			$data['label']['validate_password'] = __d('front_end', 'Please provide password');
				
		// return response
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);
		$this->set(array('response' => $response, '_serialize' => array('response')));
	}
	public function getForgetPwdView() { 

		$data = array();
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		// Initialization of Data
			$data['label']['title'] = __d('front_end', 'Trust Index Results');
			$data['label']['email'] = __d('front_end', 'Email');
			$data['label']['button_text'] = __d('front_end', 'Enter');
			$data['label']['link_back'] = __d('front_end', 'Back to login');
			$data['label']['validate_email'] = __d('front_end', 'Enter a valid email.');
		// return response
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);
		$this->set(array('response' => $response, '_serialize' => array('response')));
	}

	public function footer_data() { 

		$data = array();
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		// Initialization of Data	
			$data['footer_data']['copyright_title'] = $this->getClientName('FOOTER_NOTE');
			$data['footer_data']['logo_img_url'] = Router::url('/', true).'img/grt.png';
			$data['footer_data']['logo_img_link'] =Router::url('/', true).'files/ui-component/dist/#/resumen#/list-da-Empresas.html';
			$data['footer_data']['content'] = __d('front_end', 'Result <br>Delivery System <span>Trust Index</span>');
			
		// return response
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $data,
		);
		$this->set(array('response' => $response, '_serialize' => array('response')));
	}

	/**
	 * Loading settings from database
	 * Eg. Perspectives, Order of perspective, etc.
	 * 
	 */
	public function loadSettings($value='')
	{
		$newCond = array();
		$this->companyId = isset($this->request->data['company_id'])?$this->request->data['company_id']:0;
		$surveyData = $this->Survey->find('first', array(
			'conditions' => array(
				'Survey.id' => $this->surveyId
				),'fields' => array('Survey.instruction','Survey.perspective')
			)
		);
		if ($surveyData['Survey']['perspective'] == 1) {
			$this->NpPortalConfig->bindModel(
				array('hasMany' => array(
					'NpPortalConfigDetail' => array(
						'className' => 'NpPortalConfigDetail',
						'order' => 'NpPortalConfigDetail.order ASC',
						'conditions' => array('NpPortalConfigDetail.active' => 1,'NpPortalConfigDetail.perspective' => 1)
						)
					)
				)
			);
		}
		if ($surveyData['Survey']['perspective'] == 2) {
			$this->NpPortalConfig->bindModel(
				array('hasMany' => array(
					'NpPortalConfigDetail' => array(
						'className' => 'NpPortalConfigDetail',
						'order' => 'NpPortalConfigDetail.order ASC',
						'conditions' => array('NpPortalConfigDetail.active' => 1)
						)
					)
				)
			);
		}
		$allSettings = $this->NpPortalConfig->find('all', array(
			'order' => array(
				'NpPortalConfig.widget ASC',
				'NpPortalConfig.order ASC'),
			'conditions' => array(
				'NpPortalConfig.active' => 1
				)
			)
		);
		$configDetailOtherLang = $this->NpPortalConfigDetailsOtherLanguage->find('list', array(
       		'fields' => array('NpPortalConfigDetailsOtherLanguage.np_portal_config_detail_id', 'NpPortalConfigDetailsOtherLanguage.display_name'),
       		'conditions' => array(
       			'NpPortalConfigDetailsOtherLanguage.language_id' => $this->languageId
       			)
       	));
       	$configOtherLang = $this->NpPortalConfigsOtherLanguage->find('list', array(
       		'fields' => array('NpPortalConfigsOtherLanguage.np_portal_config_id', 'NpPortalConfigsOtherLanguage.title'),
       		'conditions' => array(
       			'NpPortalConfigsOtherLanguage.language_id' => $this->languageId
       			)
       	));		
       	/*
       		Find the survey perspective informaiton put in array
       	 */
       	$perspectiveDetails = array();
		if ($surveyData['Survey']['instruction'] == 1) {
				$surveyLanguage = $this->SurveyLanguage->find('first', array(
				'conditions' => array(
					'SurveyLanguage.survey_id' => $this->surveyId,
					'SurveyLanguage.language_id' => $this->languageId
					),
				'fields' => array('SurveyLanguage.organisation', 'SurveyLanguage.work_group')
				)
			);
			if (!empty($surveyLanguage['SurveyLanguage']['organisation'])) {
				$perspectiveDetails[1] = $surveyLanguage['SurveyLanguage']['organisation'];
			}
			if ($surveyData['Survey']['perspective'] == 2) {
				if (!empty($surveyLanguage['SurveyLanguage']['work_group'])) {
					$perspectiveDetails[2] = $surveyLanguage['SurveyLanguage']['work_group'];
					$perspectiveDetails[3] = __d('front_end', "Average");
				}
			}
		}else{
			$surveyLanguage = $this->Language->find('first', array(
				'conditions' => array(
					'Language.id' => $this->languageId
					),'fields' => array('Language.work_group','Language.organisation','Language.average')
				)
			);
			if (!empty($surveyLanguage['Language']['organisation'])) {
				$perspectiveDetails[1] = $surveyLanguage['Language']['organisation'];
			}
			if ($surveyData['Survey']['perspective'] == 2) {			
				if (!empty($surveyLanguage['Language']['work_group'])) {
					$perspectiveDetails[2] = $surveyLanguage['Language']['work_group'];
					$perspectiveDetails[3] = $surveyLanguage['Language']['average'];
				}
			}
		}

		/* If perspective information is available then add it to settings */
		if (!empty($perspectiveDetails)) {
			$this->settings['perspective_details'] = $perspectiveDetails;
		}
		
		foreach ($allSettings as $key => $config) {
			
			$items = $arr1 = array();
			foreach ($config['NpPortalConfigDetail'] as $key => $item) {		
				if (isset($configDetailOtherLang[$item['id']])) {
					if (strtolower($item['display_name']) == 'organization' and isset($perspectiveDetails[1])) {
						$item['display_name'] = $perspectiveDetails[1];
					} elseif (strtolower($item['display_name']) == 'workgroup' and isset($perspectiveDetails[2])) {
						$item['display_name'] = $perspectiveDetails[2];
					} else {
						$item['display_name'] = $configDetailOtherLang[$item['id']];
					}					
				}
				$items[] = $item;
			}
			$arr = array(
				'title' => isset($configOtherLang[$config['NpPortalConfig']['id']]) ? $configOtherLang[$config['NpPortalConfig']['id']] :  $config['NpPortalConfig']['title'],
				'type' => $config['NpPortalConfig']['type'],
				'details' => $items
				);
			$this->settings[$config['NpPortalConfig']['widget']][] = $arr;

		}
	}

	public function updateCompany() {

		$companydata = $this->request->data['company_id'];
		$companyId = $this->updateCompanyId($companydata);

		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $companyId,
		);
		$this->set(array('response' => $response, '_serialize' => array('response')));
	}
	
	public function updateSurvey() {
		//$this->autoRender = false;
		$companyId = $this->request->data['company_id'];
		$studyId = $this->request->data['study_id'];
		$userId = $this->request->data['user_id'];
		
		$surveyData = $this->StudyParticipant->find('first', array(
				'conditions' => array(
					'StudyParticipant.company_id' => $companyId,'StudyParticipant.study_id' => $studyId
					),'fields' => array('StudyParticipant.survey_id')
				)
			);
		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => $surveyData['StudyParticipant']['survey_id'],
		);
		$this->set(array('response' => $response, '_serialize' => array('response')));
	}
}