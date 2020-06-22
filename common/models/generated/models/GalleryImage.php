<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "gallery_image".
 *
 * @property integer $id ID
 * @property string $type Type
 * @property string $ownerId Owner ID
 * @property integer $rank Rank
 * @property string $name Name
 * @property string $description Description
*/
class GalleryImage extends \common\ActiveRecord
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
        return 'gallery_image';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['ownerId'], 'required'],
            [['rank'], 'integer'],
            [['description'], 'string'],
            [['type', 'ownerId', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'ownerId' => 'Owner ID',
            'rank' => 'Rank',
            'name' => 'Name',
            'description' => 'Description',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\GalleryImageQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\GalleryImageQuery(get_called_class());
    }
}
