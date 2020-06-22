<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_badges_products".
 *
 * @property integer $badge_id Badge ID
 * @property integer $bitrix_id Bitrix ID
 * @property integer $product_id Product ID
*/
class SsBadgesProducts extends \common\ActiveRecord
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
        return 'ss_badges_products';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['badge_id', 'bitrix_id', 'product_id'], 'integer'],
            [['badge_id', 'bitrix_id'], 'unique', 'targetAttribute' => ['badge_id', 'bitrix_id'], 'message' => 'The combination of Badge ID and Bitrix ID has already been taken.'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'badge_id' => 'Badge ID',
            'bitrix_id' => 'Bitrix ID',
            'product_id' => 'Product ID',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsBadgesProductsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsBadgesProductsQuery(get_called_class());
    }
}
