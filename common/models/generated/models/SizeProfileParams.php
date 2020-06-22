<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "size_profile_params".
 *
 * @property integer $id ID
 * @property integer $size_profile_id Size Profile ID
 * @property string $type Type
 * @property integer $param_id Param ID
 *
     * @property ProductParam $param
     * @property SizeProfile $sizeProfile
    */
class SizeProfileParams extends \common\ActiveRecord
{
    private $called_class_namespace;

    public function __construct()
    {
        $this->called_class_namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
        parent::__construct();
    }

                
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'size_profile_params';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['size_profile_id', 'param_id'], 'integer'],
            [['type'], 'required'],
            [['type'], 'string', 'max' => 255],
            [['size_profile_id', 'param_id', 'type'], 'unique', 'targetAttribute' => ['size_profile_id', 'param_id', 'type'], 'message' => 'The combination of Size Profile ID, Type and Param ID has already been taken.'],
            [['param_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductParam::className(), 'targetAttribute' => ['param_id' => 'id']],
            [['size_profile_id'], 'exist', 'skipOnError' => true, 'targetClass' => SizeProfile::className(), 'targetAttribute' => ['size_profile_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'size_profile_id' => 'Size Profile ID',
            'type' => 'Type',
            'param_id' => 'Param ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getParam()
    {
        return $this->hasOne($this->called_class_namespace . '\ProductParam', ['id' => 'param_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSizeProfile()
    {
        return $this->hasOne($this->called_class_namespace . '\SizeProfile', ['id' => 'size_profile_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SizeProfileParamsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SizeProfileParamsQuery(get_called_class());
    }
}
