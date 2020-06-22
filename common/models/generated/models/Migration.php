<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "migration".
 *
 * @property string $version Version
 * @property integer $apply_time Apply Time
*/
class Migration extends \common\ActiveRecord
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
        return 'migration';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['version'], 'required'],
            [['apply_time'], 'integer'],
            [['version'], 'string', 'max' => 180],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'version' => 'Version',
            'apply_time' => 'Apply Time',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MigrationQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MigrationQuery(get_called_class());
    }
}
