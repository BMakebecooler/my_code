<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "main_template_block".
 *
 * @property integer $id ID
 * @property integer $template_id Template ID
 * @property integer $block_id Block ID
 * @property integer $sort Sort
 *
     * @property MainBlock $block
     * @property MainTemplate $template
    */
class MainTemplateBlock extends \common\ActiveRecord
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
        return 'main_template_block';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['template_id', 'block_id'], 'required'],
            [['template_id', 'block_id', 'sort'], 'integer'],
            [['block_id'], 'exist', 'skipOnError' => true, 'targetClass' => MainBlock::className(), 'targetAttribute' => ['block_id' => 'id']],
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => MainTemplate::className(), 'targetAttribute' => ['template_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => 'Template ID',
            'block_id' => 'Block ID',
            'sort' => 'Sort',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getBlock()
    {
        return $this->hasOne($this->called_class_namespace . '\MainBlock', ['id' => 'block_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTemplate()
    {
        return $this->hasOne($this->called_class_namespace . '\MainTemplate', ['id' => 'template_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MainTemplateBlockQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MainTemplateBlockQuery(get_called_class());
    }
}
