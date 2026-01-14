<?php

namespace neon\daedalus\services\ddsManager\models;

/**
 * This is the model class for table "dds_class".
 *
 * @property string $id
 * @property string $class_type
 * @property string $label
 * @property string $description
 * @property string $module
 * @property integer $deleted
 * @property boolean $change_log
 *
 * @property DdsMember[] $ddsMembers
 * @property DdsObject[] $ddsObjects
 */
class DdsClass extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%dds_class}}';
	}

	/**
	 * Check if a class has a change log.
	 * @return boolean
	 */
	public function hasChangeLog()
	{
		if (isset($this->attributes['change_log']))
			return $this->attributes['change_log'];
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['class_type'], 'required'],
			[['deleted', 'count_total', 'count_deleted'], 'integer'],
			[['class_type', 'module'], 'string', 'max' => 50],
			[['change_log'], 'safe'],
			[['label'], 'string', 'max' => 300],
			[['description'], 'string', 'max' => 2000],
			[['class_type'], 'unique'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'class_type' => 'Class Type',
			'label' => 'Label',
			'description' => 'Description',
			'module' => 'Module',
			'deleted' => 'Deleted',
			'count_total' => 'Total Object Count',
			'count_deleted' => 'Deleted Object Count',
			'change_log' => 'Has Change Log'
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getDdsMembers() {
		return $this->hasMany(DdsMember::className(), ['class_type' => 'class_type']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getDdsObjects() {
		return $this->hasMany(DdsObject::className(), ['class_type' => 'class_type']);
	}

}
