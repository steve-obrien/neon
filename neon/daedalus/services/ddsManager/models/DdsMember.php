<?php

namespace neon\daedalus\services\ddsManager\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "dds_member".
 *
 * @property string $id
 * @property string $class_type
 * @property string $member_ref
 * @property string $data_type_ref
 * @property string $label
 * @property string $description
 * @property string $definition
 * @property string $choices
 * @property string $link_class
 * @property integer $deleted
 *
 * @property DdsClass $classType
 * @property DdsDataType $dataTypeRef
 */
class DdsMember extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'dds_member';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['class_type', 'member_ref', 'data_type_ref'], 'required'],
			[['deleted'], 'integer'],
			[['class_type', 'member_ref', 'data_type_ref', 'link_class'], 'string', 'max' => 50],
			[['label'], 'string', 'max' => 300],
			[['description'], 'string', 'max' => 1000],
			[['choices'], 'safe'],
			[['class_type', 'member_ref'], 'unique', 'targetAttribute' => ['class_type', 'member_ref'], 'message' => 'The combination of Class Type and Member Ref has already been taken.'],
			[['class_type'], 'exist', 'skipOnError' => true, 'targetClass' => DdsClass::className(), 'targetAttribute' => ['class_type' => 'class_type']],
			[['data_type_ref'], 'exist', 'skipOnError' => true, 'targetClass' => DdsDataType::className(), 'targetAttribute' => ['data_type_ref' => 'data_type_ref']],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'class_type' => 'Class Type',
			'member_ref' => 'Member Ref',
			'data_type_ref' => 'Data Type Ref',
			'label' => 'Label',
			'description' => 'Description',
			'choices' => 'Choices',
			'link_class' => 'Link Class',
			'deleted' => 'Deleted',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		// choices can only be an array and are stored as JSON
		if (is_array($this->choices))
			$this->choices = json_encode($this->choices);
		else
			$this->choices = null;
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind()
	{
		// return choices as decoded json or null
		if (!empty($this->choices))
			$this->choices = json_decode($this->choices,true);
		parent::afterFind();
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getClassType()
	{
		return $this->hasOne(DdsClass::className(), ['class_type' => 'class_type']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getDataTypeRef()
	{
		return $this->hasOne(DdsDataType::className(), ['data_type_ref' => 'data_type_ref']);
	}

}
