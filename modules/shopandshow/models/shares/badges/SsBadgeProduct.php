<?php

namespace modules\shopandshow\models\shares\badges;

use common\models\cmsContent\CmsContentElement;

/**
 * This is the model class for table "ss_badges_products".
 *
 * @property integer $badge_id
 * @property integer $bitrix_id
 * @property integer $product_id
 *
 * @property CmsContentElement $product
 *
 * @property SsBadge $badge
 */
class SsBadgeProduct extends \yii\db\ActiveRecord
{
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
            [['badge_id', 'bitrix_id'], 'required'],
            [['badge_id', 'bitrix_id', 'product_id'], 'integer'],
            [['badge_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsBadge::className(), 'targetAttribute' => ['badge_id' => 'id']],
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
            'product_id' => 'Product ID'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBadge()
    {
        return $this->hasOne(SsBadge::className(), ['id' => 'badge_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'product_id']);
    }

    public static function primaryKey()
    {
        return ['badge_id', 'product_id'];
    }

}
