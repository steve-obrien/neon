<?php

namespace neon\daedalus\services\ddsManager\models;

/**
 * This is the model class for table "dds_link".
 *
 * @property string $from_id
 * @property string $from_member
 * @property string $to_id
 *
 * @property DdsClass $classType
 */
class DdsLink extends \yii\db\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%dds_link}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['from_id', 'from_member', 'to_id'], 'required'],
			[['from_id', 'to_id'], 'string', 'size' => 22],
			[['from_member'], 'string', 'max' => 50],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'from_id' => 'From Class Id',
			'from_member' => 'From Class Member',
			'to_id' => 'To Class Id',
		];
	}
}
