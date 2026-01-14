<?php

namespace neon\daedalus\services\ddsManager\models;

/**
 * This is the model class for table "dds_data_type".
 *
 * @property integer $id
 * @property string $data_type_ref
 * @property string $label
 * @property string $description
 * @property string $definition
 * @property string $storage_ref
 * @property integer $deleted
 *
 * @property DdsStorage $storageRef
 * @property DdsMember[] $ddsMembers
 */
class DdsDataType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dds_data_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data_type_ref', 'storage_ref'], 'required'],
            [['deleted'], 'integer'],
            [['data_type_ref', 'storage_ref'], 'string', 'max' => 50],
            [['label'], 'string', 'max' => 300],
            [['description'], 'string', 'max' => 1000],
            [['definition'], 'string', 'max' => 2000],
            [['data_type_ref'], 'unique'],
            [['storage_ref'], 'exist', 'skipOnError' => true, 'targetClass' => DdsStorage::className(), 'targetAttribute' => ['storage_ref' => 'storage_ref']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'data_type_ref' => 'Data Type Ref',
            'label' => 'Label',
            'description' => 'Description',
            'definition' => 'Definition',
            'storage_ref' => 'Storage Ref',
            'deleted' => 'Deleted',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStorageRef()
    {
        return $this->hasOne(DdsStorage::className(), ['storage_ref' => 'storage_ref']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDdsMembers()
    {
        return $this->hasMany(DdsMember::className(), ['data_type_ref' => 'data_type_ref']);
    }
}
