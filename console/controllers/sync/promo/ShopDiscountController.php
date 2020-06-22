<?php

/**
 * php ./yii sync/promo/shop-discount
 */

namespace console\controllers\sync\promo;

use common\helpers\Msg;
use common\models\cmsContent\CmsContentElement;
use kartik\datecontrol\DateControl;
use modules\shopandshow\models\shop\ShopDiscount;
use console\controllers\sync\SyncController;
use modules\shopandshow\models\shop\shopdiscount\Configuration;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValueForLots;
use modules\shopandshow\models\shop\shopdiscount\Entity;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use skeeks\sx\File;
use yii\helpers\Console;

/**
 * Class CouponsController
 *
 * @package console\controllers
 */
class ShopDiscountController extends SyncController
{

    /** @var Cluster Хранилище картинок */
    public $clusterId = 'element_images';
    // список свойств акции с их кодами
    protected $promoProperties = [];
    // перечислимые доступные значения свойств
    protected $promoEnum = [];
    // список промокодов
    protected $promoCodes = [];

    /**
     * Старт синхронизации
     */
    public function actionIndex()
    {

        $this->actionSyncPromo();

    }

    /**
     * Синхронизирует акции из раздела SANDS_PROMO f1
     */
    public function actionSyncPromo()
    {
        $this->initPromoProperties();
        $this->initPromoEnum();
        $this->initPromoCodes();

        $this->stdout("Sync promo discounts\n", Console::FG_CYAN);

        $query = "
            SELECT 
                ibe.*
            FROM
                front2.b_iblock ib
                LEFT JOIN front2.b_iblock_element ibe ON ib.id = ibe.iblock_id
                LEFT JOIN shop_discount sd on sd.bitrix_id = ibe.id
            WHERE
                ib.name = 'SANDS_PROMO' and ibe.active_to > NOW()
        ";

        $bitrixDiscounts = \Yii::$app->db->createCommand($query)->queryAll();

        $affected = 0;
        $errors = [];
        $total = count($bitrixDiscounts);

        $this->stdout("Got {$total} values to parse\n", Console::FG_GREEN);

        foreach ($bitrixDiscounts as $bitrixDiscount) {

            ++$affected;

            $this->stdout("[{$affected} of {$total}] >> ", Console::FG_GREEN);

            $this->stdout("Discount: {$bitrixDiscount['NAME']}\n", Console::FG_YELLOW);

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if ($this->setDiscountFromBitrix($bitrixDiscount)) {
                    $this->stdout('OK', Console::FG_GREEN);
                    $transaction->commit();
                } else {
                    //throw new \Exception('Failed to sync discount: '.$bitrixDiscount['NAME']);
                    $this->stdout(' canceled', Console::FG_YELLOW);
                    $transaction->rollBack();
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                // не прерываем цикл, пусть другие акции продолжают синхронизацию
                array_push($errors, $e->getMessage());
            }

            $this->stdout("\n");
        }

        if ($errors) {
            \Yii::error('SANDS_PROMO не затянулся!');

            throw new \Exception(join("\n", $errors));
        }

        $this->stdout("Discounts synchronized!\n\n", Console::FG_GREEN);
    }

    /**
     * Инициализация списка возможных атрибутов
     */
    protected function initPromoProperties()
    {
        $query = "
            SELECT 
                ibp.ID, ibp.CODE
            FROM
                front2.b_iblock ib
                LEFT JOIN front2.b_iblock_property ibp on ib.id = ibp.iblock_id
            WHERE
                ib.name = 'SANDS_PROMO' and ibp.active = 'Y'
        ";

        $properties = \Yii::$app->db->createCommand($query)->queryAll();

        $this->promoProperties = \common\helpers\ArrayHelper::map($properties, 'ID', 'CODE');
    }

    /**
     * инициализация списка возможных значений для перечислимых атрибутов
     */
    protected function initPromoEnum()
    {
        $query = "
            SELECT 
                ibpe.ID, ibpe.VALUE
            FROM
                front2.b_iblock ib
                LEFT JOIN front2.b_iblock_property ibp on ib.id = ibp.iblock_id
                LEFT JOIN front2.b_iblock_property_enum ibpe on ibp.id = ibpe.property_id
            WHERE
                ib.name = 'SANDS_PROMO' and ibp.active = 'Y'
        ";

        $properties = \Yii::$app->db->createCommand($query)->queryAll();

        $this->promoEnum = \common\helpers\ArrayHelper::map($properties, 'ID', 'VALUE');
    }

    /**
     * инициализация списка возможных значений для перечислимых атрибутов
     */
    protected function initPromoCodes()
    {
        $promoCodes = \Yii::$app->get('front_db')->createCommand(
            'SELECT ID, UF_CODE FROM sands_promo_codes'
        )->queryAll();

        $this->promoCodes = \common\helpers\ArrayHelper::map($promoCodes, 'ID', 'UF_CODE');
    }

    /**
     * создает ShopDiscount на основании полученных данных
     * @param array $bitrixDiscount
     *
     * @return bool
     * @throws \Exception
     */
    protected function setDiscountFromBitrix(array $bitrixDiscount)
    {
        $shopDiscount = ShopDiscount::findOne(['bitrix_id' => $bitrixDiscount['ID']]);
        if (!$shopDiscount) {
            $shopDiscount = new ShopDiscount();
            $shopDiscount->site_id = \Yii::$app->cms->site->id;
            $shopDiscount->currency_code = 'RUB';
            $shopDiscount->max_discount = 0;
            $shopDiscount->bitrix_id = $bitrixDiscount['ID'];
        }
        $shopDiscount->name = $bitrixDiscount['NAME'];
        $shopDiscount->active = $bitrixDiscount['ACTIVE'];
        $shopDiscount->active_from = strtotime($bitrixDiscount['ACTIVE_FROM']);
        $shopDiscount->active_to = strtotime($bitrixDiscount['ACTIVE_TO']);
        $shopDiscount->code = $bitrixDiscount['CODE'];

        // достаем доп. атрибуты акции
        $bitrixProps = $this->getDiscountParams($bitrixDiscount['ID']);

        // устанавливаем их
        if (!$this->setShopDiscountParams($shopDiscount, $bitrixProps)) {
            return false;
        }

        /** Сохраняем акцию */
        if (!$shopDiscount->save(false)) {
            $msg = "Не удалось создать акцию " . print_r($shopDiscount->getErrors(), true);
            throw new \Exception($msg);
            //$this->stdout($msg, Console::FG_RED);
            //return false;
        }

        // загрузка конфигурации
        if (!$this->setConfigurations($shopDiscount, $bitrixProps)) {
            return false;
        }

        // Использование промокодов (купоны)
        if (!$this->setDiscountCoupons($shopDiscount, $bitrixProps, $bitrixDiscount)) {
            return false;
        }

        return true;
    }

    /**
     * Достает доп. атрибуты по id акции
     * @param int $iblockId
     *
     * @return array $bitrixProps
     * @throws \Exception
     */
    protected function getDiscountParams($iblockId)
    {
        $query = "
            SELECT 
                *
            FROM
                front2.b_iblock_element_prop_s65
            WHERE
                IBLOCK_ELEMENT_ID = :id
        ";
        $properties = \Yii::$app->db->createCommand($query, [':id' => $iblockId])->queryOne();
        if (!$properties) {
            $msg = 'cant found properties for ' . $iblockId;
            throw new \Exception($msg);
            //$this->stdout($msg, Console::FG_RED);
            //return false;
        }

        $bitrixProps = [];

        foreach ($properties as $prop_name => $value) {
            list($type, $prop_id) = explode("_", $prop_name);
            if ($type != 'PROPERTY') continue;

            if (!array_key_exists($prop_id, $this->promoProperties)) continue;
            $propCode = $this->promoProperties[$prop_id];

            $bitrixProps[$propCode] = $value;
        }
        return $bitrixProps;
    }

    /**
     * Устанавливаем все необходимые для работы акции атрибуты
     *
     * @param ShopDiscount $shopDiscount
     * @param array $bitrixProps
     *
     * @return bool
     * @throws \Exception
     */
    protected function setShopDiscountParams(ShopDiscount $shopDiscount, array $bitrixProps)
    {
        // Отмена других акций
        $shopDiscount->last_discount = 'N';
        if ($bitrixProps['CANCEL_AFTER'] && $bitrixProps['CANCEL_AFTER'] == 'Y') {
            $shopDiscount->last_discount = 'Y';
        }

        // Тип акции
        $promoType = $this->promoEnum[$bitrixProps['PROMO_TYPE']];
        if ($promoType != 'Скидка') {
            $this->stdout("Тип акции '{$promoType}' не поддерживается", Console::FG_RED);
            return false;
        }

        // тип скидки
        $discountType = $this->promoEnum[$bitrixProps['PROMO_SALE_TYPE']];
        if ($discountType == 'Фиксированная') {
            $shopDiscount->value_type = ShopDiscount::VALUE_TYPE_F;
        } elseif ($discountType == 'Процент') {
            $shopDiscount->value_type = ShopDiscount::VALUE_TYPE_P;
        } else {
            $this->stdout("Тип скидки '{$discountType}' не поддерживается", Console::FG_RED);
            return false;
        }

        // 	Сумма скидки
        $shopDiscount->value = $bitrixProps['PROMO_SALE_SUM'];

        $image_id = $bitrixProps['PROMO_BANNER_IMAGE_CODE'];
        if (!empty($image_id)) {
            $query = "
                SELECT 
                    CONCAT(f.subdir,'/', f.file_name)
                FROM
                    front2.b_file f
                WHERE
                    f.id = :id
            ";

            $image = \Yii::$app->db->createCommand($query, [':id' => $image_id])->queryScalar();
            if (empty($image)) {
                $msg = "Cant find file property by id: {$image_id}";
                throw new \Exception($msg);
                //$this->stdout($msg, Console::FG_RED);
                //return false;
            }
            $vendorFilePath = \Yii::$app->params['storage']['vendorImagesPath'] . '/' . $image;

            $vendorFile = new File($vendorFilePath);

            if ($vendorFile->isExist() === false) {
                $msg = "FILE NOT EXIST: {$vendorFilePath}";
                throw new \Exception($msg);
                //$this->stdout($msg, Console::FG_RED);
                //return false;
            }

            /** Копируем фаил чтобы не удалять у вендора (в нашем случае из папки оригиналов) */
            $tmpFile = new File('/tmp/' . md5(time() . $vendorFilePath) . "." . $vendorFile->getExtension());

            $vendorFile->copy($tmpFile);

            $file = \Yii::$app->storage->upload($tmpFile, [
                'original_name' => $image,
            ], \Yii::$app->params['storage']['clusters'][$this->clusterId]
            );

            $shopDiscount->link('image', $file);
        }

        return true;
    }

    /**
     * Создает конфигурацию акции
     * @param ShopDiscount $shopDiscount
     * @param array $bitrixProps
     *
     * @return bool
     * @throws \Exception
     */
    protected function setConfigurations(ShopDiscount $shopDiscount, array $bitrixProps)
    {
        // конфигурация уже загружена. Чистим
        if (!empty($shopDiscount->configurations)) {
            foreach ($shopDiscount->configurations as $configuration) $configuration->delete();
        }

        // Условие акции
        $promoCondition = $this->promoEnum[$bitrixProps['PROMO_CONDITION']];
        switch ($promoCondition) {
            case 'Для товара':
            case 'От суммы заказа':
            case 'Для брендов':
            case 'Для раздела Распродажа':
                $this->stdout("Условие акции '{$promoCondition}' не поддерживается", Console::FG_RED);
                return false;
            case 'Для группы товаров':
                $lots = @unserialize($bitrixProps['FOR_GROUP']);

                if (empty($lots) || empty($lots['VALUE'])) {
                    $msg = "Не удалось получить список лотов: " . strlen($bitrixProps['FOR_GROUP']);
                    throw new \Exception($msg);
                    //$this->stdout("Не удалось получить список лотов: ".$bitrixProps['FOR_GROUP'], Console::FG_RED);
                    //return false;
                }
                return $this->addConditionForLots($shopDiscount, $lots['VALUE']);
            case 'Без условий':
            default:
                return $this->addConditionEmptyCondition($shopDiscount);
        }
    }

    /**
     * Создает купоны для акции
     *
     * @param ShopDiscount $shopDiscount
     * @param array $bitrixProps
     * @param array $bitrixDiscount
     *
     * @return bool
     * @throws \Exception
     */
    protected function setDiscountCoupons(ShopDiscount $shopDiscount, array $bitrixProps, array $bitrixDiscount)
    {
        $usePromoCodes = $this->promoEnum[$bitrixProps['USE_PROMO_CODE']];

        if ($usePromoCodes == 'Да') {
            $promoCodes = @unserialize($bitrixProps['FOR_PROMO_CODE']);
            if (empty($promoCodes)) {
                $msg = "Не удалось получить список промокодов: " . $bitrixProps['FOR_PROMO_CODE'];
                throw new \Exception($msg);
                //$this->stdout($msg, Console::FG_RED);
                //return false;
            }

            $this->addConditionForPromoCode($shopDiscount);

            // удаляем промокод у нас, если он изменился в битриксе
            foreach ($shopDiscount->getShopDiscountCoupons()->all() as $shopDiscountCoupon) {
                $exists = false;
                foreach ($promoCodes['VALUE'] as $promoCodeId) {
                    if (!$this->promoCodes[$promoCodeId]) {
                        $msg = "Не удалось найти промокод по id: " . $promoCodeId;
                        throw new \Exception($msg);
                        //$this->stdout($msg, Console::FG_RED);
                        //continue;
                    }

                    $coupon = trim($this->promoCodes[$promoCodeId]);
                    if ($coupon == $shopDiscountCoupon->coupon) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) $shopDiscountCoupon->delete();
            }

            // синхронизируем новые
            foreach ($promoCodes['VALUE'] as $promoCodeId) {
                if (!$this->promoCodes[$promoCodeId]) {
                    $msg = "Не удалось найти промокод по id: " . $promoCodeId;
                    throw new \Exception($msg);
                    //$this->stdout($msg, Console::FG_RED);
                    //continue;
                }

                $coupon = trim($this->promoCodes[$promoCodeId]);

                $shopDiscountCoupon = ShopDiscountCoupon::findOne(['shop_discount_id' => $shopDiscount->id, 'coupon' => $coupon]);
                if (!$shopDiscountCoupon) {
                    $shopDiscountCoupon = new ShopDiscountCoupon();
                    $shopDiscountCoupon->shop_discount_id = $shopDiscount->id;
                    $shopDiscountCoupon->coupon = $coupon;
                    $shopDiscountCoupon->max_use = 0;
                }
                $shopDiscountCoupon->is_active = 1;
                $shopDiscountCoupon->active_from = strtotime($bitrixDiscount['ACTIVE_FROM']);
                $shopDiscountCoupon->active_to = strtotime($bitrixDiscount['ACTIVE_TO']);

                $shopDiscountCoupon->save(false);
            }
        } else {
            foreach ($shopDiscount->getShopDiscountCoupons()->all() as $shopDiscountCoupon) $shopDiscountCoupon->delete();
        }
        return true;
    }

    protected function addConditionEmptyCondition(ShopDiscount $shopDiscount)
    {
        $entity = Entity::findOne(['class' => 'EmptyCondition']);
        $configuration = new Configuration([
            'shop_discount_id' => $shopDiscount->id,
            'shop_discount_entity_id' => $entity->id
        ]);
        return $configuration->save(false);
    }

    protected function addConditionForPromoCode(ShopDiscount $shopDiscount)
    {
        $entity = Entity::findOne(['class' => 'ForPromoCode']);
        $configuration = new Configuration([
            'shop_discount_id' => $shopDiscount->id,
            'shop_discount_entity_id' => $entity->id
        ]);
        return $configuration->save(false);
    }

    protected function addConditionForLots(ShopDiscount $shopDiscount, array $lots)
    {
        $entity = Entity::findOne(['class' => 'ForLots']);
        $configuration = new Configuration([
            'shop_discount_id' => $shopDiscount->id,
            'shop_discount_entity_id' => $entity->id
        ]);
        $configuration->save(false);

        $values = [];

        foreach ($lots as $lotId) {
            $CmsContentElement = CmsContentElement::findOne(['bitrix_id' => trim($lotId)]);
            if ($CmsContentElement) {
                $values[] = $CmsContentElement->id;
            }
        }

        $configurationValue = new ConfigurationValueForLots([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $values
        ]);
        return $configurationValue->save(false);
    }
}