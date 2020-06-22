<?php

/**
 * php ./yii sync/stocks
 */

namespace console\controllers\sync;

use common\helpers\Msg;
use skeeks\cms\agent\models\CmsAgent;
use skeeks\cms\components\Cms;
use yii\helpers\Console;


/**
 * Class StockController
 *
 * @package console\controllers
 */
class StocksController extends SyncController
{

    public function actionIndex()
    {

        $queryModifications = "
                update shop_product t1
                inner join (
                    SELECT
                        b.id,
                        ce.bitrix_id,
                        CASE WHEN bc.quantity > 0 THEN bc.quantity ELSE 0 END as front_quantity,
                        ce.id as element_id
                    FROM cms_content_element ce
                    LEFT JOIN front2.b_catalog_product bc ON bc.id = ce.bitrix_id
                    LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id
                    WHERE
                        ce.content_id in (2, 10) 
                        AND ce.tree_id IS NOT NULL
                        AND b.iblock_id in (10, 11)
                        group by ce.bitrix_id
                ) t2 ON t2.element_id=t1.id
                set t1.quantity=t2.front_quantity
        ";

        $queryProducts = "
                update shop_product t1
                inner join (
                    SELECT
                        sum(CASE WHEN t.quantity > 0 THEN t.quantity ELSE 0 END) as sum_quantity,
                        ce.parent_content_element_id as element_id
                    FROM cms_content_element ce, shop_product t
                    WHERE
                        ce.content_id = 10
                        AND ce.tree_id IS NOT NULL
                        AND ce.active = 'Y'
                        AND t.id = ce.id
                        group by ce.parent_content_element_id
                ) t2 ON t2.element_id=t1.id
                set t1.quantity=t2.sum_quantity
        ";

        $this->stdout("Updating stocks ", Console::FG_YELLOW);

        $insertTransaction = \Yii::$app->db->beginTransaction();

        try {


            $affected = \Yii::$app->db->createCommand($queryModifications)->execute();
            $this->stdout(" done. Affected Modifications " . $affected . "\n", Console::FG_GREEN);

            $affected = \Yii::$app->db->createCommand($queryProducts)->execute();
            $this->stdout(" done. Affected Products " . $affected . "\n", Console::FG_GREEN);

            $insertTransaction->commit();

//            $this->recalculateQuantity();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            if ($agent = CmsAgent::find()->andWhere("name = 'sync/stocks'")->one()) {
                $agent->is_running = Cms::BOOL_N;
                $agent->next_exec_at = time() + 120; // повторим через минуту
                $agent->save();
            }

            \Yii::error('Импорт стоков не прошел, повторим чз минуту!' . $e->getMessage());

            $insertTransaction->rollBack();
            return false;
        }

        return true;

    }

    /**
     * НЕ ДОДЕЛАЛ
     * Пересчитать количество предложений для главного товара
     * @return int
     */
    private function recalculateQuantity()
    {
        $query = <<<SQL
            UPDATE shop_product t1
                INNER JOIN (
                    SELECT sp.id, SUM(sp.quantity) AS quantity
                    FROM shop_product AS sp
                    INNER JOIN cms_content_element AS cce ON cce.id = sp.id
                    WHERE cce.parent_content_element_id IS NOT NULL AND cce.parent_content_element_id = 251567
                    GROUP BY cce.parent_content_element_id
                ) t2 ON t2.id = t1.id
                    SET t1.quantity = t2.quantity
SQL;

        return \Yii::$app->db->createCommand($query)->execute();
    }

}