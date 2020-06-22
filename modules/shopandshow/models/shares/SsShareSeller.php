<?php

namespace modules\shopandshow\models\shares;

use common\helpers\Msg;
use common\helpers\User;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shop\ShopFuser;
use modules\shopandshow\models\shop\ShopOrder;

/**
 * This is the model class for table "ss_shares_selling".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $fuser_id
 * @property integer $user_id
 * @property integer $share_id
 * @property integer $status
 * @property integer $product_id
 * @property integer $updated_at
 * @property integer $order_id
 *
 * @property SsShare $share
 */
class SsShareSeller extends \yii\db\ActiveRecord
{

    const STATUS_ADD_PRODUCT_BASKET = 1;
    const STATUS_ORDER = 2;

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
            [['created_at', 'fuser_id', 'share_id', 'product_id'], 'required'],
            [['created_at', 'fuser_id', 'user_id', 'share_id', 'status', 'product_id', 'updated_at', 'order_id'], 'integer'],
            [['share_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsShare::className(), 'targetAttribute' => ['share_id' => 'id']],
            [['fuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopFuser::className(), 'targetAttribute' => ['fuser_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrder::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'created_at' => 'created_at',
            'fuser_id' => 'fuser_id',
            'user_id' => 'user_id',
            'share_id' => 'share_id',
            'status' => 'status',
            'product_id' => 'product_id',
            'updated_at' => 'updated_at',
            'order_id' => 'order_id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShare()
    {
        return $this->hasOne(SsShare::className(), ['id' => 'share_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFuser()
    {
        return $this->hasOne(ShopFuser::className(), ['id' => 'share_id']);
    }

    /**
     * @param $shareId
     * @param $productId
     * @param int $status
     * @return bool
     */
    public static function add($shareId, $productId, $status = self::STATUS_ADD_PRODUCT_BASKET)
    {

        if (!$shareId) {
            return false;
        }

        $seller = new self;
        $seller->created_at = time();
        $seller->fuser_id = User::getSessionId();
        $seller->user_id = User::getAuthorizeId();
        $seller->share_id = \common\helpers\Strings::onlyInt($shareId);
        $seller->product_id = $productId;
        $seller->status = $status;

        if (!$seller->save()) {
            \Yii::error('Какая- то проблема со вставкой в ss_shares_selling ' . print_r($seller->getErrors(), true) . ' Атрибуты' .
                print_r($seller->getAttributes(), true));
        }

        return true;
    }

    /**
     * @param ShopFuser $fuser
     * @param ShopOrder $order
     * @return int - the number of rows updated
     */
    public static function setStatusOrderByFuser(ShopFuser $fuser, $order)
    {
        return self::updateAll(
            [
                'status' => self::STATUS_ORDER,
                'order_id' => $order->id,
                'updated_at' => time(),
            ],

            ['fuser_id' => $fuser->id, 'status' => self::STATUS_ADD_PRODUCT_BASKET]);
    }
}
