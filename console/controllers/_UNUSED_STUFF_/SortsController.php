<?php

/**
 * php ./yii sync/sorts
 */

namespace console\controllers\sync;

use common\models\cmsContent\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsContentProperty;
use yii\db\Exception;
use yii\helpers\Console;

/**
 * Class SortsController
 *
 * @package console\controllers
 */
class SortsController extends SyncController
{

    protected
        $sortProperties = [
            'SORT_EFIR',
            'SORT_POPULAR',
            'SORT_SALE'
        ];

    public function actionIndex()
    {

        foreach ($this->sortProperties as $sortProp) {

            $prop = $this->getLinkedPropByCode($sortProp);

            $query = "
                INSERT INTO ".CmsContentElementProperty::tableName()." (id, created_at, updated_at, property_id, element_id, value, value_num, value_enum)
                SELECT
                    ifnull(exists_value.id, 'default'),
                    UNIX_TIMESTAMP(b.date_create),
                    UNIX_TIMESTAMP(b.timestamp_x),
                    {$prop['local_id']},
                    ce.id,
                    CASE WHEN bep.value IS NOT NULL THEN bep.value ELSE '2013-01-01 00:00:00' END,
                    bep.value_num,
                    bep.value_enum
                FROM ".CmsContentElement::tableName()." ce
                LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id

                LEFT JOIN front2.b_iblock_element_property bep ON bep.iblock_element_id = ce.bitrix_id and bep.iblock_property_id={$prop['front_id']}
                LEFT JOIN front2.b_iblock_property_enum bpe ON bpe.property_id=bep.iblock_property_id AND bpe.id=bep.value_num
                LEFT JOIN ".CmsContentElementProperty::tableName()." exists_value ON exists_value.element_id=ce.id  and exists_value.property_id={$prop['local_id']}
                where
                    ce.content_id in (2)
                GROUP BY ce.id
                ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value), value_num=VALUES(value_num), value_enum=VALUES(value_enum)
        ";

            $this->stdout("Updating Products {$sortProp} property ", Console::FG_YELLOW);

            try {

                $transaction = \Yii::$app->db->beginTransaction();

                $affected = \Yii::$app->db->createCommand($query)->execute();
                $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

                $transaction->commit();

            } catch (\yii\db\Exception $e) {

                $this->logError($e);

                $transaction->rollBack();
                return false;
            }

        }

        return true;

    }

}