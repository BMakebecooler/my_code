<?php

/**
 * php ./yii sync/visible
 * php ./yii sync/visible/products
 * php ./yii sync/visible/offers
 */

namespace console\controllers\sync;

use common\helpers\Msg;
use yii\helpers\Console;


/**
 * Class VisibleController
 *
 * @package console\controllers
 */
class VisibleController extends SyncController
{

    public function actionIndex()
    {

        $this->actionProducts();
        $this->actionOffers();

    }

    public function actionProducts()
    {

        $query = "
                update cms_content_element t1
                inner join (
                    SELECT
                        ce.id as element_id,
                        CASE WHEN b.active='N' THEN 'N' ELSE 'Y' END as visible
                    FROM cms_content_element ce
                    LEFT JOIN front2.b_iblock_element_property bep ON bep.iblock_element_id = ce.bitrix_id and bep.iblock_property_id=94
                    LEFT JOIN front2.b_iblock_property_enum bpe ON bpe.property_id=bep.iblock_property_id AND bpe.id=bep.value_num
                    LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id
                    where
                        ce.content_id in (2)
                ) t2 ON t2.element_id=t1.id
                set t1.active=t2.visible
        ";

        $this->stdout("Updating Products ACTIVE field ", Console::FG_YELLOW);

        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            \Yii::error('Импорт видимости товаров не прошел!');

            $transaction->rollBack();
            return false;
        }

        return true;

    }

    public function actionOffers()
    {

        $query = "
                update cms_content_element t1
                inner join (
                    SELECT
                        ce.id as element_id,
                        CASE WHEN b.active='N' OR bep.VALUE IS NOT NULL THEN 'N' ELSE 'Y' END as visible
                    FROM cms_content_element ce
                    LEFT JOIN front2.b_iblock_element_property bep ON bep.iblock_element_id = ce.bitrix_id and bep.iblock_property_id=94
                    LEFT JOIN front2.b_iblock_property_enum bpe ON bpe.property_id=bep.iblock_property_id AND bpe.id=bep.value_num
                    LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id
                    where
                        ce.content_id in (10)
                ) t2 ON t2.element_id=t1.id
                set t1.active=t2.visible
        ";

        $this->stdout("Updating Offers ACTIVE field ", Console::FG_YELLOW);

        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            \Yii::error('Импорт видимости предложений не прошел!');

            $transaction->rollBack();
            return false;
        }

        return true;

    }
}