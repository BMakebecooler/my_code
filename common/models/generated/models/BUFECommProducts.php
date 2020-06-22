<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_ECommProducts".
 *
 * @property integer $id ID
 * @property string $created_at Created At
 * @property string $type Type
 * @property integer $product_id Product ID
 * @property integer $OFFCNT_ID Offcnt  ID
*/
class BUFECommProducts extends \common\ActiveRecord
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
        return 'BUF_ECommProducts';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['type', 'product_id', 'OFFCNT_ID'], 'required'],
            [['product_id', 'OFFCNT_ID'], 'integer'],
            [['type'], 'string', 'max' => 20],
            [['OFFCNT_ID'], 'unique'],
            [['product_id'], 'unique'],
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
            'type' => 'Type',
            'product_id' => 'Product ID',
            'OFFCNT_ID' => 'Offcnt  ID',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\BUFECommProductsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BUFECommProductsQuery(get_called_class());
    }
}
