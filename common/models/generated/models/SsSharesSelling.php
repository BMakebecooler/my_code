<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shares_selling".
 *
 * @property integer $id ID
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $fuser_id Fuser ID
 * @property integer $user_id User ID
 * @property integer $share_id Share ID
 * @property integer $status Status
 * @property integer $product_id Product ID
 * @property integer $order_id Order ID
 *
     * @property SsShares $share
    */
class SsSharesSelling extends \common\ActiveRecord
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
        return 'ss_shares_selling';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at', 'fuser_id', 'share_id'], 'required'],
            [['created_at', 'updated_at', 'fuser_id', 'user_id', 'share_id', 'status', 'product_id', 'order_id'], 'integer'],
            [['share_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsShares::className(), 'targetAttribute' => ['share_id' => 'id']],
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
            'fuser_id' => 'Fuser ID',
            'user_id' => 'User ID',
            'share_id' => 'Share ID',
            'status' => 'Status',
            'product_id' => 'Product ID',
            'order_id' => 'Order ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShare()
    {
        return $this->hasOne($this->called_class_namespace . '\SsShares', ['id' => 'share_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsSharesSellingQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsSharesSellingQuery(get_called_class());
    }
}
