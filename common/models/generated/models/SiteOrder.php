<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "site_order".
 *
 * @property integer $id ID
 * @property integer $order_id Order ID
 * @property integer $order_kfss Order Kfss
 * @property integer $order_created_at Order Created At
 * @property string $order_date Order Date
*/
class SiteOrder extends \common\ActiveRecord
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
        return 'site_order';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['order_id', 'order_date'], 'required'],
            [['order_id', 'order_kfss', 'order_created_at'], 'integer'],
            [['order_date'], 'safe'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'order_kfss' => 'Order Kfss',
            'order_created_at' => 'Order Created At',
            'order_date' => 'Order Date',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SiteOrderQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SiteOrderQuery(get_called_class());
    }
}
