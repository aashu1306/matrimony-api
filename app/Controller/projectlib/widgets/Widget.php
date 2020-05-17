<?php

namespace app;

/**
* Parent widget class
*/
class Widget {

	var $controller;
	var $objTIScore;
	var $objFrontDemoScores;
	protected $historicalDataArr = array();
	protected $studyIdArr = array();
	public $primaryBenchmarkId;
	protected $benchmarkArr = array();
	public $wholeCompanyScoreArr = array();
	
	var $options = array();
	var $languageId = 2;
	var $naText = '-';
	var $dbPerspective = array();
	var $options2 = array();
	var $objEachPracScorePrimaryBench;
	var $surveyList;
	
	function __construct(&$controller)
	{
		$this->controller = $controller;
		include_once dirname(dirname(dirname(dirname(__FILE__)))).'/Vendor/ScoresEngine/DatabaseConnection.php';
		$this->mysqli = \DatabaseConnection::getConnection();

		if (!empty($this->controller->languageId)) {
			$this->languageId = $this->controller->languageId;
		}
		$this->surveyList = "(".implode(',',$this->controller->surveyId).")";
		$surveyId = $this->controller->surveyId;
		$this->dimColors = array('credibility' => '#F38A00', 'respect' => '#AA272F', 'camaraderie' => '#612141', 'fairness' => '#015075', 'pride' => '#6B8D00', 'common' => '#4A4A4A');

		// Colors Array For Company Score Filter By Ashutosh
		$this->dimColorsforCmp = array('credibility' => array('1'=> 'rgb(255, 212, 81)', '2'=> 'rgb(255, 234, 170)'), 'respect' => array('1'=> 'rgb(255, 100, 100)', '2'=> 'rgb(255, 151, 151)'), 'camaraderie' => array('1'=> 'rgb(208, 61, 135)', '2'=> 'rgb(236, 118, 178)'), 'fairness' => array('1'=> 'rgb(0, 161, 236)', '2'=> 'rgb(96, 205, 255)'), 'pride' => array('1'=> '#73BC8E', '2'=> '#73BC8A'), 'common' => array('1'=> '#82A9B7', '2'=> '#82A9B8'));

		$this->dimColorsInBenchMarkSection = array('credibility' => array('1'=> 'rgb(255, 166, 0)', '2'=> 'rgb(243, 138, 0)', '3'=> 'rgb(255, 199, 4)'), 'respect' => array('1'=> 'rgb(204, 47, 56)', '2'=> 'rgb(170, 39, 47)', '3'=> 'rgb(245, 56, 67)'), 'camaraderie' => array('1'=> 'rgb(116, 40, 78)', '2'=> 'rgb(97, 33, 65)', '3'=> 'rgb(139, 48, 94)'), 'fairness' => array('1'=> 'rgb(1, 96, 140)', '2'=> 'rgb(1, 80, 117)', '3'=> 'rgb(1, 115, 168)'), 'pride' => array('1'=> '#6B8D00', '2'=> '#6B8D00', '3'=> '#6B8D00'), 'common' => array('1'=> '#4A4A4A', '2'=> '#4A4A4A', '3'=> '#4A4A4A'));

		
		// Check Filter 
		if (count($this->controller->filter) > 0) {
			$companyDataArr = $this->controller->Company->find('first',array('conditions' => array('Company.id' => $this->controller->companyId),'fields' => array('Company.company_name', 'Company.company_name_jp')));
				if ($this->languageId == 3) {
					$companyName = $companyDataArr['Company']['company_name'];
				}else{
					$companyName = $companyDataArr['Company']['company_name_jp'];
				}
				$this->wholeCompanyScoreNameArr[1] = __d('front_end', 'Organization Score of ').$companyName;
				$this->wholeCompanyScoreNameArr[2] = __d('front_end', 'Workgroup Score of ').$companyName;

			$this->studyIdArr = isset($this->controller->filter['study_id'])?$this->controller->filter['study_id']:$this->studyIdArr;
			$this->benchmarkArr = isset($this->controller->filter['benchmark_id'])?$this->controller->filter['benchmark_id']:$this->benchmarkArr;

			$this->wholeCompanyScoreArr = isset($this->controller->filter['company_score_filter'])?$this->controller->filter['company_score_filter']:$this->wholeCompanyScoreArr;

			$this->primaryBenchmarkId = isset($this->controller->filter['primary_benchmark_id'])?$this->controller->filter['primary_benchmark_id']:$this->controller->primaryBenchmarkId;

			if (isset($this->controller->filter['demographic_id_hist'])) {
				foreach ($this->controller->filter['demographic_id_hist'] as $demograph => $demographOpt) {
					$this->controller->filter['demographic_id'][$demograph][] = $demographOpt;
				}
			}

			$demographic_ids = isset($this->controller->filter['demographic_id'])?$this->controller->filter['demographic_id']:array();

			foreach ($demographic_ids as $demoKey => $demoVal) {
				foreach ($demoVal as $did => $demo_opt_id) {
					if ($demo_opt_id != '') {
						$this->options['filter']['SurveyDemographic'][$demoKey][] = $demo_opt_id;
						$this->options['Survey']['front_check'] =1;
					}
				}
			}

			$backdemographic_ids = isset($this->controller->filter['backdemographic_id'])?$this->controller->filter['backdemographic_id']:array();
			foreach ($backdemographic_ids as $key => $backDemographic) {
				foreach ($backDemographic as $id => $opt_id) {
					if ($opt_id != '') {
						$this->options['filter']['SurveyBackendDemographic'][$key][] = $opt_id;
						$this->options['Survey']['back_check'] =1;
					}
				}
			}
		}


		$this->options['decimal'] = 10;
		$this->decimal = 10;

	}

	public function loadCompanyTIData()
	{
		if (!isset($this->controller->objTIScore)) {
			// New TI Score Object For Each Practices Score with primary Benchmark Filter By Ashutosh
			$userId = $this->controller->userId;
			$studyParticipantId = $this->controller->studyParticipantId;

			$segmentLeaderRoleId = $this->controller->getRoleId('rp_segment_leader');

			if (isset($this->controller->segmentId)) {
				$this->options['Survey']['segmentId'] = $this->controller->segmentId;
			}

			$this->objTIScore = new TIScores($this->mysqli, $this->controller->surveyId, $this->languageId, $this->options);
			$this->controller->objTIScore = $this->objTIScore;
			
			$benchmarkArr = $this->mysqli->query("SELECT b.bench_type, ncub.primary, ncub.benchmark_id FROM benchmarks b join np_company_user_benchmarks ncub on (ncub.benchmark_id = b.id) WHERE ncub.study_participant_id={$this->controller->studyParticipantId} and ncub.role_id != {$segmentLeaderRoleId}");

			/* Primary benchmarks changes for segment leader REQ-4083 */
			if (in_array('rp_segment_leader',$this->controller->userRoles)) {
				$benchmarkArr = $this->mysqli->query("SELECT b.bench_type, ncub.primary, ncub.benchmark_id FROM benchmarks b join np_company_user_benchmarks ncub on (ncub.benchmark_id = b.id) WHERE ncub.user_id={$this->controller->Session->read('User.id')}");
				if($benchmarkArr->num_rows == 0){
				$benchmarkArr = $this->mysqli->query("SELECT b.bench_type, ncub.primary, ncub.benchmark_id FROM benchmarks b join np_company_user_benchmarks ncub on (ncub.benchmark_id = b.id) WHERE ncub.study_participant_id={$this->controller->studyParticipantId} and ncub.role_id != {$segmentLeaderRoleId}");
				}
			}

			if($benchmarkArr->num_rows == 0){
				$benchmarkArr = $this->mysqli->query("SELECT b.bench_type, ncub.primary, ncub.benchmark_id FROM benchmarks b join np_company_user_benchmarks ncub on (ncub.benchmark_id = b.id) WHERE ncub.company_id={$this->controller->groupCompanyId} and ncub.primary = 1 limit 2");
			}

			while($busers = $benchmarkArr->fetch_assoc()){
				/* 
				   if primary benchmark then set primaryBenchmark_ with perspective in controller, there can be only one primaryBenchmark_ for one perspective
				*/
				if ($busers['primary'] == 1) {
					$perspective = $busers['bench_type'];
					// When bench_type is zero then by default perspective is organization
					if ($perspective == 0) {
						$perspective = 1;
					}

					$this->controller->{"primaryBenchmark_{$perspective}"} = $busers['benchmark_id'];
					$this->objTIScore->loadBenchmarkData($busers['benchmark_id']);
				}				
			}
		} else {
			$this->objTIScore = $this->controller->objTIScore;
		}
	}

	public function loadFDData($value='')
	{
		if (!isset($this->controller->objFrontDemoScores)) {
			$this->objFrontDemoScores = new FrontDemoScores($this->objTIScore);
			$this->controller->objFrontDemoScores = $this->objFrontDemoScores;
		} else {
			$this->objFrontDemoScores = $this->controller->objFrontDemoScores;
		}
	}

	protected function setHistoricalSurvey()
	{
		// Get Company_id 
		$companyId = $this->objTIScore->surveyData['company_id'];

		$historicalData = $this->controller->StudyParticipant->find('list', array(
		'conditions' => array(
			'StudyParticipant.company_id' => $companyId, 
			'StudyParticipant.study_id' => $this->studyIdArr, 
			'StudyParticipant.survey_id !=' => 0, 
			'StudyParticipant.survey_id !=' => '',
			'StudyParticipant.study_id !=' => 0,
			'StudyParticipant.study_id !=' => '',
			'StudyParticipant.is_report_portal_allowed' => 1),
		'fields' => array('StudyParticipant.study_id', 'StudyParticipant.survey_id')
		));
		$histOptions = $this->options;
		// Don't use summarize data
		$histOptions['summarizedData'] = false;
		$histOptions['Survey']['front_check'] =1;
		$histOptions['Survey']['back_check'] =1;
		foreach ($historicalData as $studyId => $surveyId) {
			if ($this->objTIScore->surveyData['study_id'] == $studyId) {
				continue;
			}

			$histOptions['filter']['SurveyBackendDemographic'] = $this->getHistoricalMappedBDOptions($surveyId);
			if ($histOptions['filter']['SurveyBackendDemographic'] === false) {
				$this->historicalDataArr[$studyId] = null;
				continue;
			}
			
			$histOptions['filter']['SurveyDemographic'] = $this->getHistoricalMappedFDOptions($surveyId);
			if ($histOptions['filter']['SurveyDemographic'] === false) {
				$this->historicalDataArr[$studyId] = null;
				continue;
			}
			$this->historicalDataArr[$studyId] = SurveyData::getObject($surveyId, $this->languageId, $histOptions);
		}
	}

	protected function setBenchmarkData()
	{
		// Initialize benhcmark array

		foreach ($this->benchmarkArr as $benchmarkId) {
			$this->objTIScore->loadBenchmarkData($benchmarkId);
		}
	}

	protected function getColor($score, $statementId, $perspective = 1, $argb=false)
	{
		if (!isset($this->colorObjs['perspective_'.$perspective])) {
			$benchmarkId = $this->controller->{"primaryBenchmark_{$perspective}"};
			
			$this->colorObjs['perspective_'.$perspective] = ColorBank::getObject($this->objTIScore, $benchmarkId);
		}
		return $this->colorObjs['perspective_'.$perspective]->getCode($score, $statementId, $perspective, $argb);
	}

	// Returns array of optionIds that has map with current surveys frontend demographic
	// filter. If exact number of option don't map this function returns false
	protected function getHistoricalMappedFDOptions($surveyId = array())
	{
		if (empty($this->options['filter']['SurveyDemographic'])) {
			return array();
		}
		$optIds = array();

		$frontDemoOptions = $this->getFrontDemoArr();
		
		foreach ($this->options['filter']['SurveyDemographic'] as $demoId => $options) {
			foreach ($options as $key => $optionId) {
				$optIds[] = "'".$frontDemoOptions[$demoId]["demographicOpt"][$optionId]."'";
			}
		}

		$historicalMapData = $this->controller->NpSurveyDemographicOptionMapping->query("select nsdom.survey_demographic_option_id, nsdom.historical_survey_id, nsdom.historical_survey_demographic_option_id, nsdom.historical_survey_demographic_id from np_survey_demographic_option_mappings nsdom join survey_demographic_options sdo on (sdo.id = nsdom.survey_demographic_option_id) where nsdom.historical_survey_id in (".implode(',', $surveyId).") and sdo.`option` in (".implode(',', $optIds).") group by sdo.`option`");
		
		$histCount = count($historicalMapData);
		// Current survey option is not not available then return false
		if ($histCount > 0 and $histCount != count($optIds)) {
			return false;
		}

		if (!empty($historicalMapData)) {
			$historicalMappedFDOptions = array();
			foreach ($historicalMapData as $key => $value) {

				$historicalMappedFDOptions[$this->controller->filterText($this->controller->getFrontDemoData($value['nsdom']['historical_survey_demographic_id']))][] =  $this->controller->filterText($this->controller->getFrontDemoOptData($value['nsdom']['historical_survey_demographic_option_id']));
			}
			return $historicalMappedFDOptions;
		}


		$historicalDemoOptions = array();
		if (empty($historicalMapData)) {
			
			foreach ($this->options['filter']['SurveyDemographic'] as $demoId => $options) {
				foreach ($options as $key => $optionId) {
					$optIdsArr[] = $frontDemoOptions[$demoId]["demographicOpt"][$optionId];
				}
			}

			$historicalDemoData = $this->controller->SurveyDemographicOption->find('all', array('conditions' => array('SurveyDemographicOption.`option`' => $optIdsArr, 'SurveyDemographicOption.survey_id'=>$surveyId), 'fields' => array('SurveyDemographicOption.id', 'SurveyDemographicOption.survey_demographic_id'), 'group' => 'SurveyDemographicOption.`option`'));
			
			// Current survey option is not not available then return false
			if (count($historicalDemoData) != count($optIds)) {
				return false;
			}
			$historicalMappedFDOptions = array();
			foreach ($historicalDemoData as $key => $value) {
				$historicalMappedFDOptions[$this->controller->filterText($this->controller->getFrontDemoData($value['SurveyDemographicOption']['survey_demographic_id']))][] = $this->controller->filterText($this->controller->getFrontDemoOptData($value['SurveyDemographicOption']['id']));
			}
			return $historicalMappedFDOptions;
		}


	}

	// Returns array of optionIds that has map with current surveys Backend demographic
	// filter. If exact number of option don't map this function returns false
	protected function getHistoricalMappedBDOptions($surveyId='')
	{
	
		if (empty($this->options['filter']['SurveyBackendDemographic'])) {
			return array();
		}
		$optIds = array();
		foreach ($this->options['filter']['SurveyBackendDemographic'] as $backDemoId => $options) {
			foreach ($options as $key => $optionId) {
				$optIds[] = $optionId;
			}
		}

		//Find current surveys backdemooption info
		$demographicOption = $this->controller->SurveyBackendDemographic->find('all',array('conditions'
                        =>array('SurveyBackendDemographicOption.id' => $optIds),
	    'joins'=> array(
	     array('alias'=>'SurveyBackendDemographicOption',
	    'table'=>'survey_backend_demographic_options',
	    'type' => 'inner',
	    'conditions'=>'SurveyBackendDemographic.id=SurveyBackendDemographicOption.survey_backend_demographic_id')),
	    'fields'=> array('SurveyBackendDemographic.id','SurveyBackendDemographic.name','SurveyBackendDemographicOption.name')));
		$backDemoOpiton = $backDemo = array();
		foreach ($demographicOption as $key => $value) {
			$backDemo[] = $value['SurveyBackendDemographic']['name'];
			$backDemoOpiton[] = $value['SurveyBackendDemographicOption']['name'];
		}

		//Based on current survey's backdemographic get the historical backdemographic using text comparison
		$demographicOption = $this->controller->SurveyBackendDemographic->find('all',array('conditions'
                        =>array('SurveyBackendDemographic.survey_id' => $surveyId, 'SurveyBackendDemographicOption.name' => $backDemoOpiton,  'SurveyBackendDemographic.name' => $backDemo),
	    'joins'=> array(
	     array('alias'=>'SurveyBackendDemographicOption',
	    'table'=>'survey_backend_demographic_options',
	    'type' => 'inner',
	    'conditions'=>'SurveyBackendDemographic.id=SurveyBackendDemographicOption.survey_backend_demographic_id')),
	    'fields'=> array('SurveyBackendDemographic.id','SurveyBackendDemographicOption.id')));

	    if (count($optIds) != count($demographicOption)) {
	    	return false;
	    }
		$histDemo = array();
		foreach ($demographicOption as $key => $value) {
			$histDemo[$value['SurveyBackendDemographic']['id']][] = $value['SurveyBackendDemographicOption']['id'];
		}
		return $histDemo;
	}

	/*
		Load the survey score without segment filter
	 */
	public function loadNonSegmentCompanyTIData()
	{
		if (!isset($this->controller->objTIScoreNonSegment)) {
			// Set the options not load FD TIscore.
			$this->options['loadFDData'] = false;
			$options = $this->options;

			/*
				Unset filter options to load whole survey data
			 */
			unset($options['filter']);
			unset($options['Survey']['segmentId']);
			$options['summarized'] = true;
			$options['Survey']['front_check'] = 1;
			$options['Survey']['back_check'] = 1;
			$this->objTIScoreNonSegment = new TIScores($this->mysqli, $this->controller->surveyId, $this->languageId, $options);

			$this->controller->objTIScoreNonSegment = $this->objTIScoreNonSegment;
		} else {
			$this->objTIScoreNonSegment = $this->controller->objTIScoreNonSegment;
		}
	}

	public function getPrespectiveFromDB()
	{

		if (isset($this->controller->dbPerspective) && !empty($this->controller->dbPerspective)) {
			return;
		}
		$surveyList = implode(',',$this->controller->surveyId);
		 $surveyDataResult =  $this->mysqli->query("select instruction from surveys where id in {$this->surveyList}");
  		$surveyData = $surveyDataResult->fetch_assoc();
  		$instruction = $surveyData['instruction'];
  		
  		if($instruction == 1) {
   			$surveyLanguageResult = $this->mysqli->query("select work_group,organisation from survey_languages where survey_id in {$this->surveyList} and language_id = {$this->languageId}");
   			$surveyLanguage = $surveyLanguageResult->fetch_assoc();
   			$workgroup = $surveyLanguage['work_group'];
   			$organization = $surveyLanguage['organisation'];
   			$average = __d('front_end', "Average");
  		}
  		else{
   			$masterLanguageResult =  $this->mysqli->query("select work_group,organisation,average from languages where id = {$this->languageId}");
   			$masterLanguage = $masterLanguageResult->fetch_assoc();
   			$workgroup = $masterLanguage['work_group'];
   			$organization = $masterLanguage['organisation'];
   			$average = $masterLanguage['average'];
  		}
  		$this->controller->dbPerspective = array();
  		$this->controller->dbPerspective['workgroup'] = $workgroup;
  		$this->controller->dbPerspective['organization'] = $organization;
  		$this->controller->dbPerspective['average'] = $average;
  		
	}	
}