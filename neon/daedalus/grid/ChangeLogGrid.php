<?php
/**
 * Created by PhpStorm.
 * User: steve
 * Date: 12/12/2015
 * Time: 11:28
 */

namespace neon\daedalus\grid;

/**
 * Change log grid for Daedalus
 */
class ChangeLogGrid extends \neon\core\grid\Grid
{
	public $classType = '';
	public $objectUuid = '';

	/**
	 * @inheritdoc
	 */
	public function configure()
	{
		$this->query = (new \yii\db\Query())
			->from('{{dds_change_log}}');
		if ($this->classType)
			$this->query->where(['class_type'=>$this->classType]);
		if ($this->objectUuid)
			$this->query->andWhere(['object_uuid'=>$this->objectUuid]);
		$this->query->orderBy(['when'=>SORT_DESC, 'log_id'=>SORT_DESC]);

		if (!$this->classType)
			$this->title = 'Full Change Log';
		else
			$this->title = 'Change Log for class '.$this->classType;

		if ($this->objectUuid)
			$this->title .= " and object '$this->objectUuid'";

		$this->addTextColumn('log_uuid')->setWidth("10em");
		$this->addTextColumn('object_uuid')->setWidth("10em");
		$this->addTextColumn('class_type')->setWidth("10em");
		$this->addTextColumn('change_key')->setWidth("10em");
		$this->addTextColumn('description');
		$this->addDateColumn('when')->setWidth("10em");

		$this->addButtonColumn('View')
			->addButton('view', ['/daedalus/change-log/view-object' , 'uuid' => '{{object_uuid}}', 'logUuid' => '{{log_uuid}}'], '', 'fa fa-eye', '', ['data-toggle' => 'tooltip', 'title'=>'View Object']);

		$this->setPageSize(100);
	}

}

