<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "image_manager".
 *
 * @property integer $id ID
 * @property integer $owner_id Owner ID
 * @property string $path Path
 * @property string $base_url Base Url
 * @property string $type Type
 * @property integer $size Size
 * @property string $name Name
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $tag Tag
*/
class ImageManager extends \common\ActiveRecord
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
        return 'image_manager';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['owner_id', 'path'], 'required'],
            [['owner_id', 'size', 'created_at', 'updated_at'], 'integer'],
            [['path', 'base_url', 'type', 'name', 'tag'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'path' => 'Path',
            'base_url' => 'Base Url',
            'type' => 'Type',
            'size' => 'Size',
            'name' => 'Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'tag' => 'Tag',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ImageManagerQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ImageManagerQuery(get_called_class());
    }
}
