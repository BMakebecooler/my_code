<?php

/**
 * Основные типы цен
 */

namespace modules\shopandshow\models\shop;

use common\helpers\ArrayHelper;
use modules\shopandshow\models\common\Guid;
use modules\shopandshow\models\common\GuidBehavior;
use skeeks\cms\shop\models\ShopTypePrice as SxShopTypePrice;

/**
 * @property GuidBehavior $guid
 */
class ShopTypePrice extends SxShopTypePrice
{

    const TYPE_BASE = 'BASE';
    const TYPE_DISCOUNT = 'DISCOUNT';
    const TYPE_SHOPANDSHOW = 'SHOPANDSHOW';
    const TYPE_SALE = 'SALE';
    const TYPE_TODAY = 'TODAY';
    const TYPE_DISCOUNTED = 'DISCOUNTED';


    const NAME_BASE = 'Основная цена';
    const NAME_DISCOUNT = 'Цена со скидкой';
    const NAME_SHOPANDSHOW = 'Цена Shop&show';
    const NAME_SALE = 'Цена "распродажи';
    const NAME_TODAY = 'Цена "только сегодня';
    const NAME_DISCOUNTED = 'Цена премьеры';

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_BASE => self::NAME_BASE,
            self::TYPE_DISCOUNT => self::NAME_DISCOUNT,
            self::TYPE_SHOPANDSHOW => self::NAME_SHOPANDSHOW,
            self::TYPE_SALE => self::NAME_SALE,
            self::TYPE_TODAY => self::NAME_TODAY,
            self::TYPE_DISCOUNTED => self::NAME_DISCOUNTED,
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            GuidBehavior::className() => GuidBehavior::className()
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuidRelation()
    {
        return $this->hasOne(Guid::className(), ['id' => GuidBehavior::ATTRIBUTE_GUID_ID]);
    }

    /** Получение типа цены по ГУИДу, с возможностью создания нового типа цены если такой гуид не нашелся
     *
     * @param $guid
     * @param bool $addIfNotExist
     * @return array|bool|ShopTypePrice|null|\yii\db\ActiveRecord
     */
    public static function getShopTypePriceByGuid($guid, $addIfNotExist = false)
    {
        //Если тип цены не определен - то продолжать смысла нет
        if (empty($guid)){
            return false;
        }

        $shopTypePrice = self::find()->innerJoin('ss_guids', 'shop_type_price.guid_id=ss_guids.id')->andWhere(['guid' => $guid])->one();

        if (!$shopTypePrice && $addIfNotExist){
            $shopTypePrice = new self();

            $shopTypePrice->guid->setGuid($guid);
            $shopTypePrice->code = uniqid();
            $shopTypePrice->name = uniqid();

            if (!$shopTypePrice->save()) {
                \Yii::error($shopTypePrice->getErrors(), "modules\shopandshow\models\shop\ShopTypePrice");
                return false;
            }
        }

        return $shopTypePrice;
    }


}