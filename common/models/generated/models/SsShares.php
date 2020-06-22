<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shares".
 *
 * @property integer $id ID
 * @property string $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $begin_datetime Begin Datetime
 * @property integer $end_datetime End Datetime
 * @property integer $image_id Image ID
 * @property integer $bitrix_sands_schedule_id Bitrix Sands Schedule ID
 * @property integer $bitrix_banner_id Bitrix Banner ID
 * @property integer $bitrix_info_block_id Bitrix Info Block ID
 * @property integer $promo_type Promo Type
 * @property integer $count_page_views Count Page Views
 * @property integer $count_click Count Click
 * @property integer $count_click_email Count Click Email
 * @property string $banner_type Banner Type
 * @property string $promo_type_code Promo Type Code
 * @property string $name Name
 * @property string $code Code
 * @property string $promocode Promocode
 * @property string $url Url
 * @property string $active Active
 * @property integer $bitrix_product_id Bitrix Product ID
 * @property integer $image_product_id Image Product ID
 * @property string $description Description
 * @property integer $share_schedule_id Share Schedule ID
 * @property integer $is_hidden_catalog Is Hidden Catalog
 * @property integer $schedule_tree_id Schedule Tree ID
 *
     * @property CmsContentElement $imageProduct
     * @property SsSharesSchedule $shareSchedule
     * @property SsSharesProducts[] $ssSharesProducts
     * @property SsSharesSelling[] $ssSharesSellings
    */
class SsShares extends \common\ActiveRecord
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
        return 'ss_shares';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['updated_at', 'begin_datetime', 'end_datetime'], 'required'],
            [['updated_at', 'begin_datetime', 'end_datetime', 'image_id', 'bitrix_sands_schedule_id', 'bitrix_banner_id', 'bitrix_info_block_id', 'promo_type', 'count_page_views', 'count_click', 'count_click_email', 'bitrix_product_id', 'image_product_id', 'share_schedule_id', 'is_hidden_catalog', 'schedule_tree_id'], 'integer'],
            [['banner_type', 'promo_type_code', 'name', 'code', 'promocode', 'description'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 1056],
            [['active'], 'string', 'max' => 1],
            [['image_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['image_product_id' => 'id']],
            [['share_schedule_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsSharesSchedule::className(), 'targetAttribute' => ['share_schedule_id' => 'id']],
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
            'bitrix_sands_schedule_id' => 'Bitrix Sands Schedule ID',
            'bitrix_banner_id' => 'Bitrix Banner ID',
            'bitrix_info_block_id' => 'Bitrix Info Block ID',
            'promo_type' => 'Promo Type',
            'count_page_views' => 'Count Page Views',
            'count_click' => 'Count Click',
            'count_click_email' => 'Count Click Email',
            'banner_type' => 'Banner Type',
            'promo_type_code' => 'Promo Type Code',
            'name' => 'Name',
            'code' => 'Code',
            'promocode' => 'Promocode',
            'url' => 'Url',
            'active' => 'Active',
            'bitrix_product_id' => 'Bitrix Product ID',
            'image_product_id' => 'Image Product ID',
            'description' => 'Description',
            'share_schedule_id' => 'Share Schedule ID',
            'is_hidden_catalog' => 'Is Hidden Catalog',
            'schedule_tree_id' => 'Schedule Tree ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getImageProduct()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'image_product_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShareSchedule()
    {
        return $this->hasOne($this->called_class_namespace . '\SsSharesSchedule', ['id' => 'share_schedule_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsSharesProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\SsSharesProducts', ['banner_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsSharesSellings()
    {
        return $this->hasMany($this->called_class_namespace . '\SsSharesSelling', ['share_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsSharesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsSharesQuery(get_called_class());
    }
}
