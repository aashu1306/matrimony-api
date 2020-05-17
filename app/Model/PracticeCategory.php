<?php
App::uses('AppModel', 'Model');
/**
 * PracticeCategory Model
 *
 */
class PracticeCategory extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'practice_category';

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'PracticeCategoryStatementMapping' => array(
			'className' => 'PracticeCategoryStatementMapping',
			'foreignKey' => 'practice_category_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);	

}
