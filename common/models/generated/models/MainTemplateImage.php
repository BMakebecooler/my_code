<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "main_template_image".
 *
 * @property integer $id ID
 * @property integer $block_id Block ID
 * @property string $image Image
*/
class MainTemplateImage extends \common\ActiveRecord
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
        return 'main_template_image';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['block_id'], 'required'],
            [['block_id'], 'integer'],
            [['image'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'block_id' => 'Block ID',
            'image' => 'Image',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MainTemplateImageQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MainTemplateImageQuery(get_called_class());
    }
}
