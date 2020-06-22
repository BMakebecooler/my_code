<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "custom_menu".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property string $url Url
 * @property integer $is_active Is Active
 * @property integer $type_id Type ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
*/
class CustomMenu extends \common\ActiveRecord
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
        return 'custom_menu';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'url', 'type_id'], 'required'],
            [['is_active', 'type_id', 'created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['name', 'url'], 'string', 'max' => 255],
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
            'url' => 'Url',
            'is_active' => 'Is Active',
            'type_id' => 'Type ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CustomMenuQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CustomMenuQuery(get_called_class());
    }
}
