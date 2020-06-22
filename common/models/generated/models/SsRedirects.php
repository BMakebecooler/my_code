<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_redirects".
 *
 * @property integer $id ID
 * @property string $from From
 * @property string $to To
*/
class SsRedirects extends \common\ActiveRecord
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
        return 'ss_redirects';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['from', 'to'], 'required'],
            [['from', 'to'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from' => 'From',
            'to' => 'To',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsRedirectsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsRedirectsQuery(get_called_class());
    }
}
