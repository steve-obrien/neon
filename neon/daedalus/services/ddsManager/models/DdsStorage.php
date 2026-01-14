<?php

namespace neon\daedalus\services\ddsManager\models;

/**
 * This is the model class for table "dds_storage".
 *
 * @property string $id
 * @property string $storage_ref
 * @property string $label
 * @property string $type
 * @property string $description
 *
 * @property DdsDataType[] $ddsDataTypes
 */
class DdsStorage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dds_storage}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['storage_ref', 'label', 'type', 'description'], 'required'],
            [['type'], 'string'],
            [['storage_ref'], 'string', 'max' => 50],
            [['label'], 'string', 'max' => 300],
            [['description'], 'string', 'max' => 8000],
            [['storage_ref'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'storage_ref' => 'Storage Ref',
            'label' => 'Label',
            'type' => 'Type',
            'description' => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDdsDataTypes()
    {
        return $this->hasMany(DdsDataType::className(), ['storage_ref' => 'storage_ref']);
    }
}
