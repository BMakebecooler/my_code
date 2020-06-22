<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_badges".
 *
 * @property integer $id ID
 * @property string $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $begin_datetime Begin Datetime
 * @property integer $end_datetime End Datetime
 * @property integer $image_id Image ID
 * @property integer $image_id_product_card Image Id Product Card
 * @property string $name Name
 * @property string $code Code
 * @property string $url Url
 * @property string $active Active
 * @property string $description Description
*/
class SsBadges extends \common\ActiveRecord
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
        return 'ss_badges';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['updated_at', 'begin_datetime', 'end_datetime', 'image_id', 'image_id_product_card'], 'integer'],
            [['name', 'code', 'description'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 1056],
            [['active'], 'string', 'max' => 1],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'image_id' => 'Image ID',
            'image_id_product_card' => 'Image Id Product Card',
            'name' => 'Name',
            'code' => 'Code',
            'url' => 'Url',
            'active' => 'Active',
            'description' => 'Description',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsBadgesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsBadgesQuery(get_called_class());
    }
}
