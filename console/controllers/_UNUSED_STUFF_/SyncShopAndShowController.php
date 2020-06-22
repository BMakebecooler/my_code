<?php

namespace console\controllers;



use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use console\models\imports\ImportCategories;
use console\models\imports\ImportItemsPropertiesValues;
use console\models\imports\ImportItemsPropertyValues;
use console\models\imports\ImportModification;
use console\models\imports\ImportPhoto;
use console\models\imports\ImportProduct;
use console\models\imports\ImportProductProperties;
use console\models\imports\ImportProductSaleProperties;
use console\models\imports\ImportSaleProduct;
use console\models\imports\ImportSearchColor;
use console\models\imports\ImportSaleItemsPropertyLists;
use console\models\imports\ImportActivePrice;
use console\models\sas\ActivePriceModel;
use console\models\sas\CategoryModel;
use console\models\sas\ModificationModel;
use console\models\sas\PhotoModel;
use console\models\sas\ProductModel;
use console\models\sas\ProductPropertyModel;
use console\models\sas\ProductSaleModel;
use console\models\sas\SearchColorModel;
use console\models\sas\SaleItemProperyListModel;
use skeeks\cms\components\Cms;
use skeeks\cms\Exception;
use skeeks\cms\mail\helpers\Html;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\CmsTree;
use skeeks\cms\models\CmsUser;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\sx\File;
use Yii;
use yii\db\Connection;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use modules\shopandshow\models\shop\ShopContentElement;


/**
 * Class SyncShopAndShowController
 *
 * php ./yii sync-shop-and-show/sync-prices
 *
 * @package console\controllers
 */
class SyncShopAndShowController extends \yii\console\Controller
{

    /** @var Connection */
    protected $frontDb;


    /** @var  Connection */
    protected $db;

    /** @var  CmsUser */
    protected $user;

    protected
         static $priceTypes;


    const
        BITRIX_PRODUCTS_IBLOCK_ID = 10,     // Товары
        BITRIX_SALE_ITEMS_IBLOCK_ID = 11,   // Модификации

        SITE_PRODUCTS_CONTENT_ID = 2,       // Товары
        SITE_SALE_ITEMS_CONTENT_ID = 10;    // Модификации


    public function beforeAction($action)
    {
        /** No mercy */
        ini_set('memory_limit', '-1');


        /** Init DB connections */
        $this->getDb();
        $this->getReplica();

        return true;

    }

    protected function getReplica(){

        if ( $this->frontDb !== null )
            return $this->frontDb;

        $this->frontDb = \Yii::$app->get('front_db');

    }

    protected function getDb(){

        if ( $this->db !== null )
            return $this->db;

        $this->db = \Yii::$app->get('db');

    }



    public function actionPlusBuyProperties()
    {

        $startTime = time();


        $this->stdout("Sync PLUS_BUY (С этим товаром покупают)\n", Console::FG_CYAN);

        $query = "
                SELECT
                    UNIX_TIMESTAMP() as created_at,
                    UNIX_TIMESTAMP() as updated_at,
                    cp.id as property_id,
                    ce.id as element_id,
                    bp.USER_TYPE,
                    bp.USER_TYPE_SETTINGS,
                    cep.id as value
                FROM front2.b_iblock_element b
                LEFT JOIN front2.b_iblock_property bp ON bp.IBLOCK_ID=b.IBLOCK_ID
                LEFT JOIN front2.b_iblock_element_property bep ON bep.IBLOCK_ELEMENT_ID=b.ID AND bep.IBLOCK_PROPERTY_ID=bp.ID
                LEFT JOIN ss_web.cms_content_property cp ON cp.vendor_id = bp.id
                LEFT JOIN ss_web.cms_content c ON c.code=cp.code
                LEFT JOIN ss_web.cms_content_element ce ON ce.bitrix_id = b.id and ce.content_id=2
                LEFT JOIN ss_web.shop_product sp ON sp.id=ce.id
                LEFT JOIN ss_web.cms_content_element cep ON cep.bitrix_id = bep.VALUE  and cep.content_id=2
                WHERE b.id in (select bitrix_id from ss_web.cms_content_element e where e.content_id in (2))
                                      and cp.multiple='Y'
                                      and cp.property_type='E'
                                      and bep.VALUE is not null
                                      and bp.ID in (select e.vendor_id from ss_web.cms_content_property e where e.content_id in (2))
                                      and bp.id=202
                                      
        ";

        $bitrixProps = $this->db->createCommand($query)->queryAll();

        if ( !$bitrixProps || count($bitrixProps) < 1 ) {
            $this->stdout("No props\n\n");
            // Yii::$app->end();
        }

        $this->stdout("Got ".count($bitrixProps)." to insert\n", Console::FG_GREEN);



        $transactionDelete = $this->db->beginTransaction();

        /** Todo: Подумать надо ли хранить историю изменений */
        $this->stdout("Delete all properties ", Console::FG_YELLOW);
        $affected = $this->db->createCommand("DELETE FROM ss_web.cms_content_element_property WHERE property_id=662;")->execute();
        $this->stdout(" done. Affected ".(int) $affected."\n", Console::FG_GREEN);

        $affected = $processed = 0;
        $total = count($bitrixProps);

        foreach ( $bitrixProps as $prop ) {

            ++$affected;

            $this->stdout("\n[{$affected} of {$total}] >> ", Console::FG_GREEN);

            $this->stdout("Product/Offer: {$prop['element_id']} property {$prop['property_id']}", Console::FG_YELLOW);

            $insertProp = trim(sprintf("INSERT INTO ss_web.cms_content_element_property ( created_at, updated_at, element_id, property_id, value) VALUES ( UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), %d, %d, %d)",
                $prop['element_id'],
                $prop['property_id'],
                $prop['value']
            ));

            try {

                $transaction = $this->db->beginTransaction();

                if ( $this->db->createCommand($insertProp)->execute() )
                    $this->stdout(" -> Ok\n", Console::FG_GREEN);

                $transaction->commit();
                ++$processed;

            } catch (\Exception $e) {

                $this->stdout("\tCant set зкщзукен {$e}\n", Console::FG_RED);
                $transaction->rollback();
                $transactionDelete->rollBack();
                return false;

            }


        }

        $transactionDelete->commit();

        $this->stdout("\n\nDone in ".(time()-$startTime)."s. Affected {$affected} of {$total} items. Processed: {$processed}\n\n", Console::FG_GREEN);
        return true;

    }

    public function actionSyncPhotos()
    {

        $startTime = time();


        $this->stdout("Sync PHOTOS\n", Console::FG_CYAN);

        $query = "
                select
                distinct f.ID,
                ce.id as element_id,
                ce.name,
                ce.bitrix_id,
                p.code as code,
                concat('/upload/', f.SUBDIR, '/', f.FILE_NAME) as local_file_path,
                UNIX_TIMESTAMP(f.TIMESTAMP_X) as updated_at
                from ss_web.cms_content_element ce
                left join front2.b_iblock_element_property ep ON ep.iblock_element_id=ce.bitrix_id
                left join front2.b_iblock_property p ON p.id=ep.iblock_property_id
                left join front2.b_file f ON f.id=ep.VALUE
                left join ss_web.cms_content_element_image ei ON ei.content_element_id=ce.id
                left join ss_web.cms_storage_file csf ON csf.id=ei.storage_file_id
                where
                    ce.bitrix_id=44730
                    and ep.iblock_property_id in (86,504)             
        ";

        $bitrixProps = $this->db->createCommand($query)->queryAll();

        if ( !$bitrixProps || count($bitrixProps) < 1 ) {
            $this->stdout("No photos\n\n");
            // Yii::$app->end();
        }

        $this->stdout("Got ".count($bitrixProps)." to insert\n", Console::FG_GREEN);


        $affected = $processed = 0;
        $total = count($bitrixProps);

        foreach ( $bitrixProps as $prop ) {

            ++$affected;

            $this->stdout("\n[{$affected} of {$total}] >> ", Console::FG_GREEN);

            $this->stdout("Product/Offer: {$prop['element_id']} photo {$prop['code']}", Console::FG_YELLOW);
            $this->stdout("Data: ".json_encode($prop));

            try {

                $realLocalFile = new File($prop['local_file_path']);

                $cmsContentElement = CmsContentElement::findOne($prop['element_id']);

                $file = \Yii::$app->storage->upload($realLocalFile, [
                    'name' => $cmsContentElement->name,
                    'updated_at' => $prop['updated_at'],
                    'original_name' => $prop['local_file_path'],
                ]);

                if ($prop['code'] == 'MAIN_PHOTO') {
                    $cmsContentElement->link('image', $file);
                } elseif ($prop['code'] == 'PHOTOS') {

                    /** Если у товара еще нет основного фото - прикрепить первое из дополнительных фото на место главного */
                    if ( $cmsContentElement->image === null )
                        $cmsContentElement->link('image', $file);

                    $cmsContentElement->link('images', $file);
                }
            } catch (\Exception $e) {
                $message = 'Not upload image to: ' . $cmsContentElement->id . " ({$realLocalFile})";
                var_dump($message);
                var_dump($e->getMessage());
            }


        }

        $this->stdout("\n\nDone in ".(time()-$startTime)."s. Affected {$affected} of {$total} items. Processed: {$processed}\n\n", Console::FG_GREEN);
        return true;

    }

    protected function getPriceTypes(){

        if (isset(self::$priceTypes)) {
            return self::$priceTypes;
        } else {
            $priceTypes = ShopTypePrice::find()
                ->all();
            if ($priceTypes) {

                foreach ($priceTypes as $type)
                    self::$priceTypes[$type->code] = $type;

                return self::$priceTypes;
            }
        }

        return null;

    }

    public function actionSyncPrices()
    {

        $this->syncItemPrices();
        $this->syncActivePrice();
        $this->pricesIndex();

    }

    protected function syncItemPrices()
    {

        $startTime = time();

        /** В тупую вставить цены из Битрикса, минуя историю изменения цен */
        $query = "
                SELECT
                    sp.id as product_id,
                    CASE
                        WHEN price_base.VALUE IS NOT NULL THEN price_base.VALUE
                        ELSE parent_price_base.VALUE
                    END AS BASE,
                    CASE
                        WHEN price_base.VALUE IS NOT NULL THEN price_base.VALUE
                        ELSE parent_price_base.VALUE
                    END AS SHOPANDSHOW,
                    CASE
                        WHEN price_sale.VALUE IS NOT NULL THEN price_sale.VALUE
                        ELSE parent_price_sale.VALUE
                    END AS SALE,
                    CASE
                        WHEN price_today.VALUE IS NOT NULL THEN price_today.VALUE
                        ELSE parent_price_base.VALUE
                    END AS TODAY,
                    CASE
                        WHEN price_discounted.VALUE IS NOT NULL THEN price_discounted.VALUE
                        ELSE parent_price_discounted.VALUE
                    END AS DISCOUNTED
                FROM front2.b_iblock_element b
                LEFT JOIN front2.b_iblock_element_property bep ON bep.IBLOCK_ELEMENT_ID=b.ID AND bep.IBLOCK_PROPERTY_ID=58
                LEFT JOIN front2.b_iblock_element_property parent_price_base ON parent_price_base.IBLOCK_ELEMENT_ID=bep.VALUE AND parent_price_base.IBLOCK_PROPERTY_ID=70
                LEFT JOIN front2.b_iblock_element_property parent_price_sale ON parent_price_sale.IBLOCK_ELEMENT_ID=bep.VALUE AND parent_price_sale.IBLOCK_PROPERTY_ID=174
                LEFT JOIN front2.b_iblock_element_property parent_price_today ON parent_price_today.IBLOCK_ELEMENT_ID=bep.VALUE AND parent_price_today.IBLOCK_PROPERTY_ID=73
                LEFT JOIN front2.b_iblock_element_property parent_price_discounted ON parent_price_discounted.IBLOCK_ELEMENT_ID=bep.VALUE AND parent_price_discounted.IBLOCK_PROPERTY_ID=71
                LEFT JOIN front2.b_iblock_element_property price_base ON price_base.IBLOCK_ELEMENT_ID=b.ID AND price_base.IBLOCK_PROPERTY_ID=70
                LEFT JOIN front2.b_iblock_element_property price_sale ON price_sale.IBLOCK_ELEMENT_ID=b.ID AND price_sale.IBLOCK_PROPERTY_ID=174
                LEFT JOIN front2.b_iblock_element_property price_today ON price_today.IBLOCK_ELEMENT_ID=b.ID AND price_today.IBLOCK_PROPERTY_ID=73
                LEFT JOIN front2.b_iblock_element_property price_discounted ON price_discounted.IBLOCK_ELEMENT_ID=b.ID AND price_discounted.IBLOCK_PROPERTY_ID=71
                LEFT JOIN front2.b_iblock_element_property bp ON bp.IBLOCK_ELEMENT_ID=b.ID AND bp.IBLOCK_PROPERTY_ID=419
                LEFT JOIN ss_web.cms_content_element ce ON ce.bitrix_id = b.id
                LEFT JOIN ss_web.shop_product sp ON sp.id=ce.id
                  WHERE b.id in (select bitrix_id from ss_web.cms_content_element e where e.content_id in (2,10))
        ";

        $bitrixPrices = $this->db->createCommand($query)->queryAll();

        if ( !$bitrixPrices || count($bitrixPrices) < 1 ) {
            $this->stdout("No prices\n\n");
            // Yii::$app->end();
        }

        $this->stdout("Got ".count($bitrixPrices)." to insert\n", Console::FG_GREEN);

        $affected = $processed = 0;
        $total = count($bitrixPrices);

        $priceTypes = $this->getPriceTypes();

        if ( count($priceTypes) < 1 ) {
            $this->stdout("No prices types configured!\n", Console::FG_RED);
            return;
        }

        foreach ( $bitrixPrices as $price ) {

            ++$affected;

            $this->stdout("\n[{$affected} of {$total}] >> ", Console::FG_GREEN);

            $this->stdout("Product/Offer: {$price['product_id']} \n\tBASE\t\t{$price['BASE']}\n\tSHOPANDSHOW\t{$price['SHOPANDSHOW']}\n\tTODAY\t\t{$price['TODAY']}\n\tSALE\t\t{$price['SALE']}\n\tDISCOUNTED\t{$price['DISCOUNTED']}\t", Console::FG_YELLOW);

            $insertPrices = "INSERT INTO ss_web.shop_product_price (created_at, updated_at, product_id, type_price_id, price, currency_code) VALUES ";

            foreach ( $priceTypes as $code => $type ) {

                if ( ! array_key_exists($code, $price) )
                    continue;

                $insertPrices .= sprintf(
                    "(UNIX_TIMESTAMP(),UNIX_TIMESTAMP(), %d, %d, %d, 'RUB')",
                    $price['product_id'],
                    $type->id,
                    filter_var($price[$code], FILTER_SANITIZE_NUMBER_INT)
                );

                if ( end($priceTypes) !== $type )
                    $insertPrices .= ",";

            }

            $insertPrices .= "ON DUPLICATE KEY UPDATE
                            updated_at=VALUES(updated_at),
                            price=VALUES(price)";

            try {

                $transaction = $this->db->beginTransaction();

                if ( $this->db->createCommand($insertPrices)->execute() )
                    $this->stdout(" -> Ok\n", Console::FG_GREEN);

                $transaction->commit();
                ++$processed;

            } catch (\Exception $e) {

                $this->stdout("\tCant set prices {$e}\n", Console::FG_RED);
                $transaction->rollback();
                continue;

            }


        }

        $this->stdout("\n\nDone in ".(time()-$startTime)."s. Affected {$affected} of {$total} items. Processed: {$processed}\n\n", Console::FG_GREEN);
        return true;


    }

    protected function syncActivePrice()
    {

        $startTime = time();


        $this->stdout("Sync PRICE_ACTIVE (Активна скидка?)\n", Console::FG_CYAN);

        $query = "
                SELECT
                    UNIX_TIMESTAMP() as created_at,
                    UNIX_TIMESTAMP() as updated_at,
                    cp.id as property_id,
                    ce.id as element_id,
                    rep.id as value
                FROM front2.b_iblock_element b
                LEFT JOIN front2.b_iblock_property bp ON bp.IBLOCK_ID=b.IBLOCK_ID
                LEFT JOIN front2.b_iblock_element_property bep ON bep.IBLOCK_ELEMENT_ID=b.ID AND bep.IBLOCK_PROPERTY_ID=bp.ID
                LEFT JOIN ss_web.cms_content_property cp ON cp.vendor_id = bp.id
                LEFT JOIN ss_web.cms_content c ON c.code=cp.code
                LEFT JOIN ss_web.cms_content_element ce ON ce.bitrix_id = b.id and ce.content_id=2
                LEFT JOIN ss_web.shop_product sp ON sp.id=ce.id
                LEFT JOIN ss_web.cms_content_element cep ON cep.bitrix_id = bep.VALUE  and cep.content_id=2
                LEFT JOIN ss_web.cms_content_element rep ON rep.code=bep.value AND rep.content_id=94
                WHERE b.id in (select bitrix_id from ss_web.cms_content_element e where e.content_id in (2))
                                      and bep.VALUE is not null
                                      and bp.ID in (select e.vendor_id from ss_web.cms_content_property e where e.content_id in (2))
                                      and bp.id=88
                                      
        ";

        $bitrixProps = $this->db->createCommand($query)->queryAll();

        if ( !$bitrixProps || count($bitrixProps) < 1 ) {
            $this->stdout("No props\n\n");
            // Yii::$app->end();
        }

        $this->stdout("Got ".count($bitrixProps)." to insert\n", Console::FG_GREEN);



        $transactionDelete = $this->db->beginTransaction();

        /** Todo: Подумать надо ли хранить историю изменений */
        $this->stdout("Delete all properties ", Console::FG_YELLOW);
        $affected = $this->db->createCommand("DELETE FROM ss_web.cms_content_element_property WHERE property_id=587;")->execute();
        $this->stdout(" done. Affected ".(int) $affected."\n", Console::FG_GREEN);

        $affected = $processed = 0;
        $total = count($bitrixProps);

        foreach ( $bitrixProps as $prop ) {

            ++$affected;

            $this->stdout("\n[{$affected} of {$total}] >> ", Console::FG_GREEN);

            $this->stdout("Product/Offer: {$prop['element_id']} property {$prop['property_id']}", Console::FG_YELLOW);

            $insertProp = trim(sprintf("INSERT INTO ss_web.cms_content_element_property ( created_at, updated_at, element_id, property_id, value) VALUES ( UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), %d, %d, %d)",
                $prop['element_id'],
                $prop['property_id'],
                $prop['value']
            ));

            try {

                $transaction = $this->db->beginTransaction();

                if ( $this->db->createCommand($insertProp)->execute() )
                    $this->stdout(" -> Ok\n", Console::FG_GREEN);

                $transaction->commit();
                ++$processed;

            } catch (\Exception $e) {

                $this->stdout("\tCant set зкщзукен {$e}\n", Console::FG_RED);
                $transaction->rollback();
                $transactionDelete->rollBack();
                return false;

            }


        }

        $transactionDelete->commit();

        $this->stdout("\n\nDone in ".(time()-$startTime)."s. Affected {$affected} of {$total} items. Processed: {$processed}\n\n", Console::FG_GREEN);
        return true;

    }

    protected function pricesIndex()
    {

        $startTime = time();


        $this->stdout("Rebuilding aggregated prices table (ss_shop_product_prices)\n", Console::FG_CYAN);

        $this->db->createCommand("SET sql_mode = '';")->execute();

        $this->db->createCommand("SET @base_price_id = (SELECT id FROM `shop_type_price` WHERE `def` = 'Y');")->execute();

        $this->stdout("Delete all entries ", Console::FG_YELLOW);
        $affected = $this->db->createCommand("DELETE FROM ss_web.ss_shop_product_prices WHERE 1=1;")->execute();
        $this->stdout(" done. Affected ".(int) $affected."\n", Console::FG_GREEN);

        $insertSql = <<<SQL
INSERT INTO ss_web.ss_shop_product_prices (product_id, type_price_id, price, min_price, max_price)

    SELECT product.id AS product_id, COALESCE(stp_offer.id, @base_price_id) AS type_price_id, COALESCE(sp_offer.price, sp_base.price) AS price,
    
    MIN(COALESCE(sp_min_max_offer.price, sp_min_max_product.price)) AS min_price, 
    MAX(COALESCE(sp_min_max_offer.price, sp_min_max_product.price)) AS max_price
    
    FROM ss_web.cms_content_element AS product
    
    LEFT JOIN ss_web.cms_content_element parent_products ON parent_products.`parent_content_element_id` = product.id
    
    LEFT JOIN ss_web.cms_content_element_property price_active_id ON price_active_id.`element_id` = product.id 
       AND price_active_id.property_id = (SELECT id FROM ss_web.cms_content_property WHERE `code` = 'PRICE_ACTIVE' AND content_id = 2)
    
    LEFT JOIN ss_web.cms_content_element_property price_active_type ON  price_active_type.`element_id` = price_active_id.value 
        AND price_active_type.`property_id` = (SELECT id FROM ss_web.cms_content_property WHERE `code` = 'PRICE_CODE')
    
    LEFT JOIN ss_web.shop_type_price AS stp_offer ON stp_offer.`code` = price_active_type.value

    LEFT JOIN ss_web.shop_product_price AS sp_offer ON sp_offer.product_id = parent_products.id AND (sp_offer.type_price_id = stp_offer.id OR sp_offer.type_price_id = @base_price_id) 
    
    LEFT JOIN  ss_web.shop_product_price AS sp_base ON sp_base.type_price_id = @base_price_id AND sp_base.product_id = product.id
    
    LEFT JOIN ss_web.shop_product_price AS sp_min_max_offer ON sp_min_max_offer.product_id IN (parent_products.id)
    LEFT JOIN ss_web.shop_product_price AS sp_min_max_product ON sp_min_max_product.product_id = product.id
    
    WHERE product.content_id IN (2, 10)
    
    GROUP BY product.id

SQL;

        $this->stdout("Inserting prices ", Console::FG_GREEN);

        if ( $affected = $this->db->createCommand($insertSql)->execute() )
            $this->stdout(" done. Affected ".$affected."\n", Console::FG_GREEN);


        $this->stdout("\n\nDone in ".(time() - $startTime)." \n\n", Console::FG_GREEN);
        return;

    }

}