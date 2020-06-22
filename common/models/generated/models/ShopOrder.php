<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_order".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $site_id Site ID
 * @property integer $person_type_id Person Type ID
 * @property integer $buyer_id Buyer ID
 * @property string $payed Payed
 * @property integer $payed_at Payed At
 * @property integer $emp_payed_id Emp Payed ID
 * @property string $canceled Canceled
 * @property integer $canceled_at Canceled At
 * @property integer $emp_canceled_id Emp Canceled ID
 * @property string $reason_canceled Reason Canceled
 * @property string $status_code Status Code
 * @property integer $status_at Status At
 * @property integer $emp_status_id Emp Status ID
 * @property string $price_delivery Price Delivery
 * @property string $allow_delivery Allow Delivery
 * @property integer $allow_delivery_at Allow Delivery At
 * @property integer $emp_allow_delivery_id Emp Allow Delivery ID
 * @property string $price Price
 * @property string $currency_code Currency Code
 * @property string $discount_value Discount Value
 * @property integer $user_id User ID
 * @property integer $pay_system_id Pay System ID
 * @property integer $delivery_id Delivery ID
 * @property string $user_description User Description
 * @property string $additional_info Additional Info
 * @property string $ps_status Ps Status
 * @property string $ps_status_code Ps Status Code
 * @property string $ps_status_description Ps Status Description
 * @property string $ps_status_message Ps Status Message
 * @property string $ps_sum Ps Sum
 * @property string $ps_currency_code Ps Currency Code
 * @property integer $ps_response_at Ps Response At
 * @property string $comments Comments
 * @property string $tax_value Tax Value
 * @property string $stat_gid Stat Gid
 * @property string $sum_paid Sum Paid
 * @property integer $recuring_id Recuring ID
 * @property string $pay_voucher_num Pay Voucher Num
 * @property integer $pay_voucher_at Pay Voucher At
 * @property integer $locked_by Locked By
 * @property integer $locked_at Locked At
 * @property string $recount_flag Recount Flag
 * @property integer $affiliate_id Affiliate ID
 * @property string $delivery_doc_num Delivery Doc Num
 * @property integer $delivery_doc_at Delivery Doc At
 * @property string $update_1c Update 1c
 * @property string $deducted Deducted
 * @property integer $deducted_at Deducted At
 * @property integer $emp_deducted_id Emp Deducted ID
 * @property string $reason_undo_deducted Reason Undo Deducted
 * @property string $marked Marked
 * @property integer $marked_at Marked At
 * @property integer $emp_marked_id Emp Marked ID
 * @property string $reason_marked Reason Marked
 * @property string $reserved Reserved
 * @property integer $store_id Store ID
 * @property string $order_topic Order Topic
 * @property integer $responsible_id Responsible ID
 * @property integer $pay_before_at Pay Before At
 * @property integer $account_id Account ID
 * @property integer $bill_at Bill At
 * @property string $tracking_number Tracking Number
 * @property string $xml_id Xml ID
 * @property string $id_1c Id 1c
 * @property string $version_1c Version 1c
 * @property integer $version Version
 * @property string $external_order External Order
 * @property string $allow_payment Allow Payment
 * @property string $key Key
 * @property integer $bitrix_id Bitrix ID
 * @property string $order_number Order Number
 * @property integer $counter_send_queue Counter Send Queue
 * @property integer $counter_error_queue Counter Error Queue
 * @property string $last_send_queue_at Last Send Queue At
 * @property string $source Source
 * @property string $source_detail Source Detail
 * @property integer $guid_id Guid ID
 * @property integer $do_not_need_confirm_call Do Not Need Confirm Call
 * @property string $order_payment_number Order Payment Number
 * @property integer $count_payment Count Payment
 *
     * @property ShopBasket[] $shopBaskets
     * @property ShopAffiliate $affiliate
     * @property ShopBuyer $buyer
     * @property MoneyCurrency $currencyCode
     * @property ShopDelivery $delivery
     * @property CmsUser $lockedBy
     * @property ShopPaySystem $paySystem
     * @property ShopPersonType $personType
     * @property CmsSite $site
     * @property ShopOrderStatus $statusCode
     * @property CmsContentElement $store
     * @property CmsUser $user
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property ShopOrder2discountCoupon[] $shopOrder2discountCoupons
     * @property ShopOrderChange[] $shopOrderChanges
     * @property ShopUserTransact[] $shopUserTransacts
     * @property SsShopFuserDiscount[] $ssShopFuserDiscounts
    */
class ShopOrder extends \common\ActiveRecord
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
            'author' => \yii\behaviors\BlameableBehavior::class,
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'shop_order';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'site_id', 'person_type_id', 'buyer_id', 'payed_at', 'emp_payed_id', 'canceled_at', 'emp_canceled_id', 'status_at', 'emp_status_id', 'allow_delivery_at', 'emp_allow_delivery_id', 'user_id', 'pay_system_id', 'delivery_id', 'ps_response_at', 'recuring_id', 'pay_voucher_at', 'locked_by', 'locked_at', 'affiliate_id', 'delivery_doc_at', 'deducted_at', 'emp_deducted_id', 'marked_at', 'emp_marked_id', 'store_id', 'responsible_id', 'pay_before_at', 'account_id', 'bill_at', 'version', 'bitrix_id', 'counter_send_queue', 'counter_error_queue', 'guid_id', 'do_not_need_confirm_call', 'count_payment'], 'integer'],
            [['person_type_id', 'buyer_id', 'status_at', 'currency_code'], 'required'],
            [['price_delivery', 'price', 'discount_value', 'ps_sum', 'tax_value', 'sum_paid'], 'number'],
            [['comments'], 'string'],
            [['last_send_queue_at'], 'safe'],
            [['payed', 'canceled', 'status_code', 'allow_delivery', 'ps_status', 'recount_flag', 'update_1c', 'deducted', 'marked', 'reserved', 'external_order', 'allow_payment'], 'string', 'max' => 1],
            [['reason_canceled', 'user_description', 'additional_info', 'ps_status_description', 'ps_status_message', 'stat_gid', 'reason_undo_deducted', 'reason_marked', 'order_topic', 'xml_id', 'order_number', 'order_payment_number'], 'string', 'max' => 255],
            [['currency_code', 'ps_currency_code'], 'string', 'max' => 3],
            [['ps_status_code'], 'string', 'max' => 5],
            [['pay_voucher_num', 'delivery_doc_num'], 'string', 'max' => 20],
            [['tracking_number'], 'string', 'max' => 100],
            [['id_1c', 'version_1c'], 'string', 'max' => 15],
            [['key', 'source', 'source_detail'], 'string', 'max' => 32],
            [['key'], 'unique'],
            [['affiliate_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopAffiliate::className(), 'targetAttribute' => ['affiliate_id' => 'id']],
            [['buyer_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopBuyer::className(), 'targetAttribute' => ['buyer_id' => 'id']],
            [['currency_code'], 'exist', 'skipOnError' => true, 'targetClass' => MoneyCurrency::className(), 'targetAttribute' => ['currency_code' => 'code']],
            [['delivery_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDelivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
            [['locked_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['locked_by' => 'id']],
            [['pay_system_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPaySystem::className(), 'targetAttribute' => ['pay_system_id' => 'id']],
            [['person_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopPersonType::className(), 'targetAttribute' => ['person_type_id' => 'id']],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_id' => 'id']],
            [['status_code'], 'exist', 'skipOnError' => true, 'targetClass' => ShopOrderStatus::className(), 'targetAttribute' => ['status_code' => 'code']],
            [['store_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['store_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'site_id' => 'Site ID',
            'person_type_id' => 'Person Type ID',
            'buyer_id' => 'Buyer ID',
            'payed' => 'Payed',
            'payed_at' => 'Payed At',
            'emp_payed_id' => 'Emp Payed ID',
            'canceled' => 'Canceled',
            'canceled_at' => 'Canceled At',
            'emp_canceled_id' => 'Emp Canceled ID',
            'reason_canceled' => 'Reason Canceled',
            'status_code' => 'Status Code',
            'status_at' => 'Status At',
            'emp_status_id' => 'Emp Status ID',
            'price_delivery' => 'Price Delivery',
            'allow_delivery' => 'Allow Delivery',
            'allow_delivery_at' => 'Allow Delivery At',
            'emp_allow_delivery_id' => 'Emp Allow Delivery ID',
            'price' => 'Price',
            'currency_code' => 'Currency Code',
            'discount_value' => 'Discount Value',
            'user_id' => 'User ID',
            'pay_system_id' => 'Pay System ID',
            'delivery_id' => 'Delivery ID',
            'user_description' => 'User Description',
            'additional_info' => 'Additional Info',
            'ps_status' => 'Ps Status',
            'ps_status_code' => 'Ps Status Code',
            'ps_status_description' => 'Ps Status Description',
            'ps_status_message' => 'Ps Status Message',
            'ps_sum' => 'Ps Sum',
            'ps_currency_code' => 'Ps Currency Code',
            'ps_response_at' => 'Ps Response At',
            'comments' => 'Comments',
            'tax_value' => 'Tax Value',
            'stat_gid' => 'Stat Gid',
            'sum_paid' => 'Sum Paid',
            'recuring_id' => 'Recuring ID',
            'pay_voucher_num' => 'Pay Voucher Num',
            'pay_voucher_at' => 'Pay Voucher At',
            'locked_by' => 'Locked By',
            'locked_at' => 'Locked At',
            'recount_flag' => 'Recount Flag',
            'affiliate_id' => 'Affiliate ID',
            'delivery_doc_num' => 'Delivery Doc Num',
            'delivery_doc_at' => 'Delivery Doc At',
            'update_1c' => 'Update 1c',
            'deducted' => 'Deducted',
            'deducted_at' => 'Deducted At',
            'emp_deducted_id' => 'Emp Deducted ID',
            'reason_undo_deducted' => 'Reason Undo Deducted',
            'marked' => 'Marked',
            'marked_at' => 'Marked At',
            'emp_marked_id' => 'Emp Marked ID',
            'reason_marked' => 'Reason Marked',
            'reserved' => 'Reserved',
            'store_id' => 'Store ID',
            'order_topic' => 'Order Topic',
            'responsible_id' => 'Responsible ID',
            'pay_before_at' => 'Pay Before At',
            'account_id' => 'Account ID',
            'bill_at' => 'Bill At',
            'tracking_number' => 'Tracking Number',
            'xml_id' => 'Xml ID',
            'id_1c' => 'Id 1c',
            'version_1c' => 'Version 1c',
            'version' => 'Version',
            'external_order' => 'External Order',
            'allow_payment' => 'Allow Payment',
            'key' => 'Key',
            'bitrix_id' => 'Bitrix ID',
            'order_number' => 'Order Number',
            'counter_send_queue' => 'Counter Send Queue',
            'counter_error_queue' => 'Counter Error Queue',
            'last_send_queue_at' => 'Last Send Queue At',
            'source' => 'Source',
            'source_detail' => 'Source Detail',
            'guid_id' => 'Guid ID',
            'do_not_need_confirm_call' => 'Do Not Need Confirm Call',
            'order_payment_number' => 'Order Payment Number',
            'count_payment' => 'Count Payment',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBaskets()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasket', ['order_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getAffiliate()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopAffiliate', ['id' => 'affiliate_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getBuyer()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopBuyer', ['id' => 'buyer_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCurrencyCode()
    {
        return $this->hasOne($this->called_class_namespace . '\MoneyCurrency', ['code' => 'currency_code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getDelivery()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopDelivery', ['id' => 'delivery_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getLockedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'locked_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPaySystem()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPaySystem', ['id' => 'pay_system_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPersonType()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopPersonType', ['id' => 'person_type_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSite()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsSite', ['id' => 'site_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStatusCode()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopOrderStatus', ['code' => 'status_code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStore()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'store_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'user_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCreatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'created_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrder2discountCoupons()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder2discountCoupon', ['order_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrderChanges()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrderChange', ['shop_order_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopUserTransacts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopUserTransact', ['shop_order_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShopFuserDiscounts()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShopFuserDiscount', ['shop_order_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopOrderQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopOrderQuery(get_called_class());
    }
}
