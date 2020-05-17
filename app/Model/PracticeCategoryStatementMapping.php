<?php
App::uses('AppModel', 'Model');
/**
 * PracticeCategoryStatementMapping Model
 *
 * @property PracticeCategory $PracticeCategory
 * @property CommonRatingMaster $CommonRatingMaster
 */
class PracticeCategoryStatementMapping extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'practice_category_statement_mapping';


	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'PracticeCategory' => array(
			'className' => 'PracticeCategory',
			'foreignKey' => 'practice_category_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
