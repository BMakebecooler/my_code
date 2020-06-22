<?php

namespace modules\shopandshow\models\shares;

use common\models\cmsContent\CmsContentElement;

/**
 * This is the model class for table "ss_banner_products".
 *
 * @property integer $banner_id
 * @property integer $bitrix_id
 * @property integer $product_id
 * @property integer $priority
 * @property integer $is_hidden_catalog
 *
 * @property CmsContentElement $product
 *
 * @property SsShare $banner
 */
class SsShareProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_shares_products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['banner_id', 'bitrix_id'], 'required'],
            [['banner_id', 'bitrix_id', 'product_id', 'priority', 'is_hidden_catalog'], 'integer'],
            [['banner_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsShare::className(), 'targetAttribute' => ['banner_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'banner_id' => 'Banner ID',
            'bitrix_id' => 'Bitrix ID',
            'product_id' => 'Product ID',
            'priority' => 'Priority',
            'is_hidden_catalog' => 'is_hidden_catalog',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShare()
    {
        return $this->hasOne(SsShare::className(), ['id' => 'banner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'product_id']);
    }
}
