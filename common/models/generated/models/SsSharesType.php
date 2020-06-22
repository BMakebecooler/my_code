<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shares_type".
 *
 * @property integer $id ID
 * @property string $code Code
 * @property string $description Description
*/
class SsSharesType extends \common\ActiveRecord
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
        return 'ss_shares_type';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['code', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'description' => 'Description',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsSharesTypeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsSharesTypeQuery(get_called_class());
    }
}
