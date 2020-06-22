<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_preorders_logs".
 *
 * @property integer $id ID
 * @property string $created_at Created At
 * @property string $phone Phone
 * @property string $products Products
 * @property string $products_ids Products Ids
*/
class SsPreordersLogs extends \common\ActiveRecord
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
        return 'ss_preorders_logs';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['products'], 'string'],
            [['phone'], 'string', 'max' => 64],
            [['products_ids'], 'string', 'max' => 256],
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
            'phone' => 'Phone',
            'products' => 'Products',
            'products_ids' => 'Products Ids',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsPreordersLogsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsPreordersLogsQuery(get_called_class());
    }
}
