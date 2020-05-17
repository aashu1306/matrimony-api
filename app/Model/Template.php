<?php
App::uses('AppModel', 'Model');
/**
 * PracticeCategory Model
 *
 */
class Template extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'templates';

/**
 * hasMany associations
 *
 * @var array
 */
	public function getTemplate()
	{
		return $this->find('list',array('conditions'=>array(), 'fields'=>array('name')));
	}

}
