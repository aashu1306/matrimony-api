<?php
App::uses('AppModel', 'Model');
/**
 * NpTemplateRatingMaster Model
 *
 */
class NpTemplateRatingMaster extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'np_template_rating_masters';

/**
 * hasMany associations
 *
 * @var array
 */
	public function getTemplateStatment($value='')
	{
		return $this->find('all', 
			array(
       			'fields' => 
       				array(
       					'NpTemplateRatingMaster.statement',
       					'NpTemplateRatingMaster.id'
       					),
       			'conditions' => 
       				array(
       					'NpTemplateRatingMaster.template_id' => $value
       					)
       				)
			);
	}

}
