<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "size_profile".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property integer $user_id User ID
 * @property string $session_id Session ID
 * @property string $type Type
 * @property string $description Description
 * @property string $tree_ids Tree Ids
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 *
     * @property SizeProfileParams[] $sizeProfileParams
    */
class SizeProfile extends \common\ActiveRecord
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
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'size_profile';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['tree_ids'], 'string'],
            [['name', 'session_id', 'type', 'description'], 'string', 'max' => 255],
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
            'user_id' => 'User ID',
            'session_id' => 'Session ID',
            'type' => 'Type',
            'description' => 'Description',
            'tree_ids' => 'Tree Ids',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSizeProfileParams()
    {
        return $this->hasMany($this->called_class_namespace . '\SizeProfileParams', ['size_profile_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SizeProfileQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SizeProfileQuery(get_called_class());
    }
}
