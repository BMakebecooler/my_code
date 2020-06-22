<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 19/02/2019
 * Time: 13:04
 */

namespace console\controllers;


use common\models\cmsContent\CmsContentElement;
use yii\console\Controller;

class GuidController extends Controller
{

    public function actionClear()
    {
        $this->stdout("Очистка GUID'ов" . PHP_EOL);
        // бывает что гуид создался, а сам элемент нет, освобождаем такие гуиды

        //Товары
        $guidFixProductsSql = <<<SQL
delete from ss_guids where id in (
  select g.id from (
    select id from ss_guids g where entity_type = 3 and not exists (
        select 1 from cms_content_element ce where ce.guid_id = g.id
    )
  )  as g
);
SQL;
        $affected = \Yii::$app->db->createCommand($guidFixProductsSql)->execute();

        $this->stdout("Очищено ГУИДов товаров: {$affected}");

        CmsContentElement::deleteAll(['guid_id' => null, 'content_id' => [PRODUCT_CONTENT_ID, CARD_CONTENT_ID, OFFERS_CONTENT_ID, KFSS_PRODUCT_CONTENT_ID]]);

        //Типы цен !!! ЧТО ТО НЕ ТАК! Удалило ишнее! Перепроверить!
//        $guidFixPriceTypesSql = <<<SQL
//DELETE FROM ss_guids WHERE id IN (
//    SELECT g.id FROM (
//        SELECT * FROM ss_guids AS guids WHERE guids.entity_type=6 AND NOT EXISTS(
//            SELECT 1 FROM shop_type_price AS prices WHERE prices.guid_id=guids.id AND prices.id > 100
//        )
//    ) AS g
//);
//SQL;
//        $affected = \Yii::$app->db->createCommand($guidFixPriceTypesSql)->execute();
//
//        $this->stdout("Очищено ГУИДов типов цен: {$affected}");

        return true;
    }

}