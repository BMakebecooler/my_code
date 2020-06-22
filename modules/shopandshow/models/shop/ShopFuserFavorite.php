<?php

namespace modules\shopandshow\models\shop;

use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsUser;
use skeeks\cms\shop\models\ShopFuser;
use skeeks\cms\shop\models\ShopProduct;

/**
 * This is the model class for table "shop_fuser_favorites".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $shop_fuser_id
 * @property integer $shop_product_id
 * @property string $active
 * @property string $comment
 *
 * @property CmsUser $createdBy
 * @property ShopFuser $shopFuser
 * @property ShopProduct $shopProduct
 * @property CmsContentElement $cmsContentElement
 * @property CmsUser $updatedBy
 */
class ShopFuserFavorite extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_fuser_favorites';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'shop_fuser_id', 'shop_product_id'], 'integer'],
            [['shop_fuser_id', 'shop_product_id', 'active'], 'required'],
            [['comment', 'active'], 'string'],

//            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['shop_fuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopFuser::className(), 'targetAttribute' => ['shop_fuser_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['shop_product_id' => 'id']],
            [['shop_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['shop_product_id' => 'id']],
//            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Кто создал',
            'updated_by' => 'Кто обновил',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'shop_fuser_id' => 'Пользователь',
            'shop_product_id' => 'Товар',
            'active' => 'Активность',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        //return $this->hasOne(CmsUser::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopFuser()
    {
        return $this->hasOne(ShopFuser::className(), ['id' => 'shop_fuser_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'shop_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'shop_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
//        return $this->hasOne(CmsUser::className(), ['id' => 'updated_by']);
    }

    /**
     * @param $productId
     * @return bool
     */
    public static function changeFavorite($productId)
    {
        $favorite = self::find()
            ->where([
                'shop_product_id' => $productId,
                'shop_fuser_id' => \Yii::$app->shop->shopFuser->id,
            ])->one();

        /**
         * @var self $favorite
         */
        if ($favorite) {
            $favorite->active = $favorite->isActive() ? Cms::BOOL_N : Cms::BOOL_Y;
        } else {
            $favorite = new static();
            $favorite->shop_product_id = $productId;
            $favorite->active = Cms::BOOL_Y;
            $favorite->shop_fuser_id = \Yii::$app->shop->shopFuser->id;
            $favorite->created_by = \Yii::$app->shop->shopFuser->id;
            $favorite->created_at = time();
        }

        return $favorite->save();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active === Cms::BOOL_Y ? true : false;
    }
}
