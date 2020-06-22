<?php
namespace modules\shopandshow\controllers\shop;

use common\helpers\ArrayHelper;
use \skeeks\cms\shop\controllers\AdminDiscountController as SXAdminDiscountController;
use modules\shopandshow\models\shop\ShopDiscount;
use yii\web\Response;

/**
 * Class AdminDiscountController
 * @package modules\shopandshow\controllers
 */
class AdminDiscountController extends SXAdminDiscountController
{
    public function init()
    {
        parent::init();

        $this->modelClassName           = ShopDiscount::className();
    }


    /**
     * Экспорт товаров для загрузки в баннерную систему
     */
    public function actionExportProducts()
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;

        $discountId = \Yii::$app->request->get('id', 6500);

        $sql = <<<SQL
    SELECT cce.bitrix_id
    FROM `ss_shop_discount_values` AS dv
    INNER JOIN cms_content_element AS cce ON cce.id = dv.value AND cce.content_id IN(2,10)
    INNER JOIN ss_shop_discount_configuration AS sdc ON sdc.id = dv.shop_discount_configuration_id AND sdc.shop_discount_entity_id = 4
    INNER JOIN shop_discount AS sd ON sdc.shop_discount_id = sd.id AND sd.id = :discount_id
SQL;

        $data = \Yii::$app->db->createCommand($sql, [
            ':discount_id' => $discountId,
        ])->queryAll();

        $result = '';
        foreach ($data as $item) {
            $csvRow = [$item['bitrix_id']];
            $result .= join(';', ArrayHelper::arrayToString($csvRow)) . PHP_EOL;
        }

        //$result = iconv('UTF8', 'CP1251', $result);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products.csv"');

        echo $result;
    }
}
