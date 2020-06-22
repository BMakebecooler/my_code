<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "main_banner".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property integer $block_id Block ID
 * @property integer $sort Sort
 * @property string $link Link
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 *
     * @property MainBlock $block
    */
class MainBanner extends \common\ActiveRecord
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
            'author' => \yii\behaviors\BlameableBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'main_banner';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'block_id', 'link'], 'required'],
            [['block_id', 'sort', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name', 'link'], 'string', 'max' => 255],
            [['block_id'], 'exist', 'skipOnError' => true, 'targetClass' => MainBlock::className(), 'targetAttribute' => ['block_id' => 'id']],
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
            'block_id' => 'Block ID',
            'sort' => 'Sort',
            'link' => 'Link',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
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
     * @inheritdoc
     * @return \common\models\query\MainBannerQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MainBannerQuery(get_called_class());
    }
}
