<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "callback".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property string $phone Phone
 * @property string $time Time
 * @property string $theme Theme
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
*/
class Callback extends \common\ActiveRecord
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
    public function behaviors()
    {
        return [
            'author' => \yii\behaviors\BlameableBehavior::class,
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'callback';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'phone'], 'required'],
            [['theme'], 'string'],
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 512],
            [['phone', 'time'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'phone' => 'Phone',
            'time' => 'Time',
            'theme' => 'Theme',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CallbackQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CallbackQuery(get_called_class());
    }
}
