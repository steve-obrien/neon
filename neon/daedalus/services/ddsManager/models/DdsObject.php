<?php

namespace neon\daedalus\services\ddsManager\models;

/**
 * This is the model class for table "dds_object".
 *
 * @property string $_uuid
 * @property string $_class_type
 * @property integer $_deleted
 * @property string $_created
 * @property string $_updated
 *
 * @property DdsClass $classType
 */
class DdsObject extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%dds_object}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['_class_type'], 'required'],
			[['_deleted'], 'integer'],
			[['_created', '_updated'], 'safe'],
			[['_uuid'], 'string', 'max' => 36],
			[['_class_type'], 'string', 'max' => 50],
			[['_class_type'], 'exist', 'skipOnError' => true, 'targetClass' => DdsClass::class, 'targetAttribute' => ['_class_type' => 'class_type']],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'_uuid' => 'Object Identifier',
			'_class_type' => 'Class Type',
			'_deleted' => 'Deleted',
			'_created' => 'Created',
			'_updated' => 'Updated',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getClassType()
	{
		return $this->hasOne(DdsClass::class, ['class_type' => '_class_type']);
	}
}
