<?php
App::uses('AppController', 'Controller');
/**
 * ActionPlans Controller
 */
//@ 26/02/2018 by Pooja Ganvir, REQ-4737
class OperationsController extends AppController {

/**
 * Scaffold
 *
 * @var mixed
 */
	// public $scaffold, $company_data=array();

	// var $name = 'CopyDescInExistingProjects'; 

	public $uses = array('PracticeArea','ProjectPracticeArea','SelfAssessmentQuestion','ProjectSelfAssessmentQuestion','BatchEmail','ProjectEmailTemplate','ActionPlanFeedback');


	public function beforeFilter() {
		// $this->layout = 'participant';
		$this->Security->unlockedActions = array('copy_description');
		$this->set('refrer', $this->refrer);

		parent::beforeFilter();
	}
	/**
	 * [copy_description description]
	 * @return [type] [description]
	 */
	public function copy_description() {
		$flag = true;
		$this->autoRender = false;
		$projectPracticeArea = $this->ProjectPracticeArea->find('all',array('conditions' => array('ProjectPracticeArea.description'=>null),'fields'=>array('id','project_id','name','description')));
		$practiceArea = $this->PracticeArea->find('list',array('fields'=>array('name','description')));



		$pAreaArr = $dataToSave = $projectIdArr = array();
		
		foreach ($projectPracticeArea as $key => $value) {
			$pAreaArr[$value['ProjectPracticeArea']['project_id']][$value['ProjectPracticeArea']['id']] = array(
					'name' => $value['ProjectPracticeArea']['name'],
				'	description' => $value['ProjectPracticeArea']['description']
				);
		}
			
		foreach ($pAreaArr as $projectId => $data) {
			foreach ($data as $id => $value) {
				foreach ($practiceArea as $name => $description) {

					if ($name == $value['name'] and is_null($value['description'])) {
						if (!is_null($description) and $description != '' ) {
							$dataToSave[] = array(
								'id' => $id,
								'description' => $description
							);
						}

					}
				}
			}
		}

		if ($flag == true && !$this->ProjectPracticeArea->saveMany($dataToSave)) {
			$flag = false;
		}

		if ($flag == true) {
			echo __('operation performed successfully');
		}
		else{
			echo __('operation failed');
		}
	} 

	//@ 07/03/2018 by Pooja Ganvir, REQ-4737
	public function copy_question() {
		$flag = true;
		$this->autoRender = false;

		$ids = $this->Project->find('list',array('fields'=>array('id')));
		$projectSelfAssIds = $this->ProjectSelfAssessmentQuestion->find('list',array('fields'=>array('project_id')));
		$projectIds = array_diff($ids, $projectSelfAssIds);
		$questionsArr = $this->SelfAssessmentQuestion->find('all',array('conditions' => array('active' => 1),'fields'=>array('question','active')));

		$dataToSave =array();
		foreach ($projectIds as $id) {
			foreach ($questionsArr as $key => $value) {
				foreach ($value as $key => $questionData) {
					$dataToSave[] = array(
						'project_id' => $id,
						'question' => $questionData['question'],
						'active' => $questionData['active']
					);
				}
			}
		}
		if ($flag == true && !$this->ProjectSelfAssessmentQuestion->saveMany($dataToSave)) {
			$flag = false;
		}
		
		if ($flag == true) {
			echo __('operation performed successfully');
		}
		else{
			echo __('operation failed');
		}
	}

	//@ 28/03/2018 by Pooja Ganvir, REQ-4730
	public function copy_batch_email() {
		$flag = true;
		$this->autoRender = false;
		$ids = $this->Project->find('list',array('fields'=>array('id')));
		$projectEmailIds = $this->ProjectEmailTemplate->find('list',array('conditions'=> array('name'=>'level 1 self-assessment'),'fields'=>array('project_id')));
		$projectIds = array_diff($ids, $projectEmailIds);
		$batchEmailData = $this->BatchEmail->find('all',array('conditions'=>array('name'=>'level 1 self-assessment')));
		$dataToSave = array();
		foreach ($projectIds as $id) {
			foreach ($batchEmailData as $key => $value) {
				foreach ($value as $key => $data) {
					$dataToSave[] = array(
						'project_id' => $id,
						'from' => $data['from'],
						'sender' => $data['sender'],
						'reply_to' => $data['reply_to'],
						'subject' => $data['subject'],
						'name' => $data['name'],
						'content' => $data['content'],
						'attachment' => $data['attachment'],
						'level' => $data['level'],
						'active' => $data['active'],
					);
				}
			}
		}
		if ($flag == true && !$this->ProjectEmailTemplate->saveMany($dataToSave)) {
			$flag = false;
		}

		if ($flag == true) {
			echo __('operation performed successfully');
		}
		else{
			echo __('operation failed');
		}
	}


	public function move_verified(){
		$this->autoRender = false;
		$flag = true;
		$msg = "Operation Performed successfully.";
		$count = $this->ActionPlanFeedback->find('count',array('conditions'=>array('ActionPlanFeedback.is_verified'=>0)));
		if ($count == 0) {
			$flag = false;
			$msg = "No need to perform this operation";
		}
		if($flag == true and !$this->ActionPlanFeedback->updateAll(
    		array('ActionPlanFeedback.is_verified' => 1),
    		array('ActionPlanFeedback.is_verified' => 0 )
		)){
			$flag = false;
			$msg = "Operation Failed";
		}
		else{
			$countNew = $this->ActionPlanFeedback->find('count',array('conditions'=>array('ActionPlanFeedback.is_verified'=>0)));
		}
		if ($flag == true) {
			$rows = $count - $countNew;
			$msg = "Total ".$rows." rows are updated";
		}

		echo $msg;
	}

	
	//@ 28/03/2018 by Pooja Ganvir, REQ-4730
	public function copy_batch_email_level2() {
		$flag = true;
		$this->autoRender = false;
		$ids = $this->Project->find('list',array('fields'=>array('id')));
		$projectEmailIds = $this->ProjectEmailTemplate->find('list',array('conditions'=> array('name'=>'level 2 self-assessment'),'fields'=>array('project_id')));
		$projectIds = array_diff($ids, $projectEmailIds);
		$batchEmailData = $this->BatchEmail->find('all',array('conditions'=>array('name'=>'level 2 self-assessment')));
		$dataToSave = array();
		foreach ($projectIds as $id) {
			foreach ($batchEmailData as $key => $value) {
				foreach ($value as $key => $data) {
					$dataToSave[] = array(
						'project_id' => $id,
						'from' => $data['from'],
						'sender' => $data['sender'],
						'reply_to' => $data['reply_to'],
						'subject' => $data['subject'],
						'name' => $data['name'],
						'content' => $data['content'],
						'attachment' => $data['attachment'],
						'level' => $data['level'],
						'active' => $data['active'],
					);
				}
			}
		}
		if ($flag == true && !$this->ProjectEmailTemplate->saveMany($dataToSave)) {
			$flag = false;
		}

		if ($flag == true) {
			echo __('operation performed successfully');
		}
		else{
			echo __('operation failed');
		}
	}

}