<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 28.03.17
 * Time: 11:58
 */

namespace common\lists;

use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use modules\shopandshow\lists\Shares;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\components\Cms;
use Yii;

class Contents
{

    /**
     * @param $code
     * @return CmsContent
     */
    public static function getContentByCode($code)
    {
        $db = Yii::$app->db;

        return $db->cache(function ($db) use ($code) {
            return CmsContent::findOne(['code' => $code]);
        }, HOUR_5);
    }

    /**
     * @param $code
     * @return int|null
     */
    public static function getIdContentByCode($code)
    {
        if ($content = self::getContentByCode($code)) {
            return $content->id;
        }

        return null;
    }

    /**
     * @param $code
     * @param $contentId
     * @return CmsContentProperty
     */
    public static function getContentPropertyByCode($code, $contentId = PRODUCT_CONTENT_ID)
    {
        return CmsContentProperty::findOne(['code' => $code, 'content_id' => $contentId]);
    }

    /**
     * @param $code
     * @param $contentId
     * @return int|null
     */
    public static function getIdContentPropertyByCode($code, $contentId = PRODUCT_CONTENT_ID)
    {
        if ($contentProperty = self::getContentPropertyByCode($code, $contentId)) {
            return $contentProperty->id;
        }

        return null;
    }

    /**
     * @param $code
     * @param $contentId
     * @return CmsContentElement
     */
    public static function getContentElementByCode($code, $contentId = PRODUCT_CONTENT_ID)
    {
        return CmsContentElement::findOne(['code' => $code, 'content_id' => $contentId]);
    }

    /**
     * @param $id
     * @return CmsContentElement
     */
    public static function getContentElementById($id)
    {

        return CmsContentElement::findOne($id);

        $db = Yii::$app->db;

        return $db->cache(function ($db) use ($id) {

        }, HOUR_1);
    }

    /**
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function getContentElementByIdOrBitrixId($id)
    {
        return CmsContentElement::find()->where([
            'or',
            ['id' => $id],
            ['bitrix_id' => $id],
        ])->andWhere(['content_id' => PRODUCT_CONTENT_ID])
            ->one();
    }

    /**
     * @param $bitrixId
     * @param $contentId int | array
     * @return CmsContentElement
     */
    public static function getContentElementByBitrixId($bitrixId, $contentId = null)
    {
        return CmsContentElement::find()->where(['bitrix_id' => $bitrixId])->andFilterWhere(['content_id' => $contentId])->one();
    }

    /**
     * Получить инфо в json для отдачи клиенту
     * @param $productId
     * @return array|bool
     */
    public static function getInfoProduct($productId)
    {
        $cmsContentElement = CmsContentElement::findOne($productId);

        if (!$cmsContentElement) {
            return false;
        }

        $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement);

        $data = [
            'id' => $cmsContentElement->id,
            'name' => $cmsContentElement->name,
            'url' => $cmsContentElement->url,
            'price' => $shopProduct->basePrice(),
            'user_id' => \common\helpers\User::getAuthorizeId(),
            'tree_id' => $cmsContentElement->tree_id,
        ];

        if ($user = \common\helpers\User::getUser()) {
            if ($user->email) {
                $data['user_visit'] = $user->email;
            }
        }

        return $data;
    }

    /**
     * Вернуть ЦТСный баннер
     * @return bool|null|\yii\db\ActiveRecord|\modules\shopandshow\models\shares\SsShare
     */
    public static function getCtsShare()
    {
        $db = Yii::$app->db;

        return $db->cache(function () {

            if (!$share = Shares::getShareByTypeEfir()) {
                return false;
            }

            return $share;
        }, MIN_30);
    }

    /**
     * Вернуть ЦТСный товар
     * @return bool|null|\yii\db\ActiveRecord|ShopContentElement
     */
    public static function getCtsProduct()
    {
        $db = Yii::$app->db;

        return $db->cache(function () {

            if (!$share = Shares::getShareByTypeEfir()) {
                return false;
            }

            return ShopContentElement::find()
                ->andWhere(['cms_content_element.bitrix_id' => $share->bitrix_product_id])
                ->andWhere('cms_content_element.tree_id IS NOT NULL')
//            ->andWhere('cms_content_element.id=777627')
                ->one();
        }, MIN_30);
    }

    /**
     * Вернуть ЦТС товары
     * @return array|bool|\yii\db\ActiveRecord[]|ShopContentElement[]
     */
    public static function getCtsProducts()
    {
        $db = Yii::$app->db;

        $ctsShares = SsShare::find()
            ->select('bitrix_product_id')
            ->andWhere(['banner_type' => SsShare::BANNER_TYPE_CTS])
            ->andWhere(['not', ['image_id' => null]])
            ->andWhere(['not', ['active' => Cms::BOOL_N]])
            ->andWhere('begin_datetime <= :time AND end_datetime >= :time', [
                ':time' => time(),
            ]);

        return ShopContentElement::find()
            ->andWhere(['cms_content_element.bitrix_id' => $ctsShares])
            ->andWhere('cms_content_element.tree_id IS NOT NULL')
//            ->andWhere('cms_content_element.id=777627')
            ->all();

        return $db->cache(function () {

        }, MIN_30);
    }

    /**
     * вспомогательная функция, достает id элементов по их bitrix_id
     * @param array $bitrixIds
     *
     * @return array
     */
    public static function getIdsByBitrixIds(array $bitrixIds)
    {
        $params = [];
        $condition = \Yii::$app->db->getQueryBuilder()->buildCondition(['IN', 'bitrix_id', $bitrixIds], $params);

        $lotsSql = "SELECT id, bitrix_id from cms_content_element WHERE {$condition} AND content_id IN (2,10)";

        $lots = \Yii::$app->db->createCommand($lotsSql, $params)->queryAll();

        $bitrixMap = \common\helpers\ArrayHelper::map($lots, 'bitrix_id', 'id');

        return $bitrixMap;
    }

    /**
     * Вернуть ид дочерних элементов
     * @param $id
     * @return array
     */
    public static function getChildrensContentElementIds($id)
    {
        $sql = <<<SQL
SELECT GROUP_CONCAT(modification.id SEPARATOR ', ') AS products
FROM cms_content_element AS modification 
INNER JOIN shop_product AS sp ON sp.id = modification.id
LEFT JOIN ss_shop_product_prices AS spp ON spp.product_id = modification.id
WHERE parent_content_element_id IN (
    SELECT cart.id
    FROM cms_content_element AS cart 
    INNER JOIN shop_product AS sp ON sp.id = cart.id
    LEFT JOIN ss_shop_product_prices AS spp ON spp.product_id = cart.id
    WHERE cart.active = 'Y' AND cart.parent_content_element_id = :id -- AND sp.quantity >=1
) AND modification.active = 'Y' -- AND sp.quantity >=1
SQL;

        $elements = \Yii::$app->db->createCommand($sql, [
            ':id' => $id
        ])->queryOne();

        if (array_filter($elements)) {
            return ArrayHelper::arrayToInt(explode(',', $elements['products']));
        }

        return [];
    }

    /**
     * Получить ид свойств по их коду
     * @param $codes
     * @return array
     */
    public static function getPropertiesIdsByCode($codes)
    {
        $codeString = join(',', ArrayHelper::arrayToString((array)$codes));

        $sql = <<<SQL
SELECT GROUP_CONCAT(property.id SEPARATOR ', ') AS properties
FROM cms_content_property AS property 
WHERE property.code IN ($codeString)
SQL;

        $properties = Yii::$app->db->createCommand($sql)->queryOne();

        if (array_filter($properties)) {
            return ArrayHelper::arrayToInt(explode(',', $properties['properties']));
        }

        return [];
    }

}