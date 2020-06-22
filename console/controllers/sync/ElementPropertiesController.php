<?php

/**
 * php ./yii sync/element-properties
 * php ./yii sync/element-properties/product-names
 * php ./yii sync/element-properties --syncMode=new|full
 * php ./yii sync/element-properties/plus-buy
 * php ./yii sync/element-properties/lot-num
 * php ./yii sync/element-properties/product-names-clean
 * php yii sync/element-properties/sync-prop NOT_PUBLIC LOT_NUM NOT_PUBLIC ZAGOLOVOK_CTS | PLUS_BUY SORT_EFIR | SIZE_RINGS | PLUS_BUY
 * php yii sync/element-properties/sync-prop  COLOR_REF --syncMode=full
 * php yii sync/element-properties/sync-prop  TECHNICAL_DETAILS --syncMode=full
 * php yii sync/element-properties/sync-prop  VIDEO_PRICE_BASE --syncMode=full
 * php yii sync/element-properties/sync-prop  VIDEO_PRICE_DISCOUNTED --syncMode=full
 * php yii sync/element-properties/sync-prop  VIDEO_PRICE_TODAY --syncMode=full
 * php yii sync/element-properties/sync-prop PARAMETER1 --syncMode=full
 * php yii sync/element-properties/sync-prop PRICE_ACTIVE --syncMode=full
 * php yii sync/element-properties/sync-prop NOT_PUBLIC --syncMode=full
 * php yii sync/element-properties/sync-prop SIZE_CLOTHES --syncMode=full
 */

namespace console\controllers\sync;

use common\helpers\Msg;
use console\controllers\sync\helpers\SyncHelper;
use skeeks\cms\agent\models\CmsAgent;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentProperty;
use yii\helpers\Console;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;


/**
 * Class ElementPropertiesController
 *
 * @package console\controllers
 */
class ElementPropertiesController extends SyncController
{

    public function actionHourly()
    {
        $this->actionProductNamesClean();
    }

    public function actionIndex()
    {

        if ($this->syncMode === null) {
            $this->stdout("Sync mode is empty. Choose new or full\n");
            return 0;
        }

        if ($this->syncMode != SyncController::SYNC_MODE_UPDATED && $this->syncMode != SyncController::SYNC_MODE_FULL) {
            $this->stdout("Unsupported sync mode '{$this->syncMode}'. Choose --syncMode=new or full\n", Console::FG_RED);
            return 1;
        }

        $this->stdout("Prepare to sync property values\n", Console::FG_CYAN);

        $ifOk = false;

        try {
            $this->actionPlusBuy();
            $this->actionLotNum();

            // цвет, parameter1, parameter2
            $this->actionLinkedProps();


            $_updateableProps = CmsContentProperty::find()
                ->andWhere('multiple="N"')
                ->andWhere('property_type IN ("N","S","L","E")')
                ->andWhere('vendor_id IS NOT NULL')
                ->andWhere('content_id IN (2,10)')
                /** синхронизируется отдельно */
                ->andWhere('code NOT IN ("COLOR_REF", "PARAMETER1", "PARAMETER2", "PLUS_BUY", "PRICE_ACTIVE", "LOT_NUM")')
                ->all();

            $this->stdout("Props count to sync: " . count($_updateableProps) . "\n", Console::FG_YELLOW);

            foreach ($_updateableProps as $k => $prop) {
                $this->actionSyncProp([$prop['code']]);
            }


            $this->fixRingsSize();

            $ifOk = true;

        } catch (\yii\db\Exception $e) {
            $this->stdout("Перезапустим агент через минуту\n", Console::FG_RED);
        }

        if (!$ifOk) {

            \Yii::error('Импорт свойств не прошел!');

            if ($agent = CmsAgent::find()->andWhere(sprintf("name = 'sync/element-properties --syncMode={%s}'", $this->syncMode))->one()) {
                $agent->is_running = Cms::BOOL_N;
                $agent->next_exec_at = time() + 90; // повторим через минуту
                $agent->save();
            }
        }
    }

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['syncMode']);
    }

    public function actionLinkedProps()
    {
        $this->actionLinkedRefProp('COLOR_REF', 'front2.highload_color_reference');
        if (YII_ENV == 'production') {
            $this->actionLinkedRefProp('PARAMETER1', 'front2.highload_parameters_ref');
            $this->actionLinkedRefProp('PARAMETER2', 'front2.highload_parameters_ref');
        }
    }

    /**
     * Очистка битриксового формата названий файлов от номера лота и битриксИд
     *
     * @return bool
     */
    public function actionProductNamesClean()
    {
        $this->stdout("Обновление (очистка) названий товаров" . PHP_EOL, Console::FG_CYAN);

        $updated = 0;

        $query = "
        SELECT cce.id, cce.name
        FROM cms_content_element cce
        WHERE
          cce.content_id = ".PRODUCT_CONTENT_ID."
        ORDER BY cce.id";

        $products = \Yii::$app->db->createCommand($query)->queryAll();

        $this->stdout("Товаров для проверки - " . count($products) . PHP_EOL, Console::FG_YELLOW);
        $updates = [];

        foreach ($products as $product) {
            $_cleanName = SyncHelper::getCleanName($product['name']);

            if (strlen($_cleanName) < 1 || $_cleanName == $product['name']){
                continue;
            }
            $updates[$product['id']] = $_cleanName;
        }

        $productsForUpdateNum = count($updates);

        $this->stdout("Товаров для обновления названий - {$productsForUpdateNum}" . PHP_EOL, Console::FG_YELLOW);

        if ($updates){
            $updateNameSql = "
                UPDATE cms_content_element
                SET name=:productName
                WHERE id=:productId";
            
            $productName = '';
            $productId = 0;
            
            $command = \Yii::$app->db->createCommand($updateNameSql)
                ->bindParam(':productName', $productName)
                ->bindParam(':productId', $productId);
            
            try {
                foreach ($updates as $productId => $productName) {
                    if ($command->execute() > 0){
                        $updated++;
                    }
                }
            } catch (\yii\base\Exception $e) {
            }
        }

        $this->stdout("Готово. Обновлено товаров - {$updated}" . PHP_EOL, Console::FG_GREEN);

        return true;
    }


    public function actionPlusBuy()
    {

        $this->stdout("Syncing PLUS_BUY properties\n", Console::FG_CYAN);

        \Yii::$app->db->createCommand()->execute("set sql_mode='';\n");

        $prop = $this->getLinkedPropByCode('PLUS_BUY');

        $this->stdout("Link map: \n", Console::FG_GREEN);
        $this->stdout($prop['local_code'] . " <+> " . $prop['front_code'] . "\n" . $prop['local_id'] . " <+> " . $prop['front_id'] . "\n", Console::FG_YELLOW);

        $this->stdout("Cleaning old values ", Console::FG_YELLOW);

        $_affected = \Yii::$app->db->createCommand("DELETE FROM cms_content_element_property WHERE property_id={$prop['local_id']}")
            ->execute();

        $this->stdout(" done. Affected {$_affected} rows.\n", Console::FG_GREEN);

        $query = "
INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value, value_num, value_enum)
SELECT
    UNIX_TIMESTAMP(b.date_create),
    UNIX_TIMESTAMP(b.timestamp_x),
    {$prop['local_id']},
    ce.id,
    CASE WHEN also_buy.id IS NULL THEN '' ELSE also_buy.id END,
    also_buy.id,
    also_buy.id
FROM cms_content_element ce
LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id
LEFT JOIN front2.b_iblock_element_property bep ON bep.iblock_element_id = ce.bitrix_id and bep.iblock_property_id={$prop['front_id']}
LEFT JOIN cms_content_element also_buy ON also_buy.bitrix_id=bep.value_num
where
    ce.content_id in (2)
    and also_buy.id IS NOT NULL
        ";

        $this->stdout("\n" . $query . "\n\n", Console::FG_GREY);

        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

        return true;

    }

    /**
     * Синхронизирует свойство элемента
     *
     * php yii sync/element-properties/sync-prop [CODE|BITRIX_ID]
     * php yii sync/element-properties/sync-prop SHOPPINGCLUB_ACTIVE
     * php yii sync/element-properties/sync-prop SHOPPINGCLUB_ACTIVE --syncMode=full
     *
     * Case insensitive, массив
     * php yii sync/element-properties/sync-prop 530,price_active
     *
     * @param array $props
     * @return int
     */
    public function actionSyncProp(array $props)
    {

        $this->stdout("Prepare to sync properties: " . implode(', ', $props) . "\n\n", Console::FG_CYAN);
        $this->stdout("Sync mode : {$this->syncMode}\n", Console::FG_YELLOW);

        foreach ($props as $k => $prop) {

            $_links = null;

            if (preg_match('/^\d+$/', $prop) && (int)$prop > 0)
                $_links = $this->getPropLink(['vendor_id' => $prop]);
            elseif (is_string($prop))
                $_links = $this->getPropLink(['code' => strtoupper($prop)]);


            if ($_links === null) {
                $this->stdout("No prop link found\n", Console::FG_RED);
                return 1;
            }

            foreach ($_links as $_link) {

                if (strtoupper($_link['local_is_multiple']) === 'Y') {
                    $this->stdout("Multiple sync not implemented\n", Console::FG_YELLOW);
                    return 0;
                }

                switch (strtoupper($_link['local_type'])) {

                    /** Цифровые свойства */
                    case 'N':

                        $this->syncNumberProp($_link);

                        break;

                    /** Свойства - Привязка к элементу Справочник */
                    case 'E':
                    case 'L':

                        $this->syncRefProp($_link);

                        break;

                    /** Строковые свойства */
                    case 'S':

                        /** TODO: Надо завести типа свойстdf HTML, перенести нужные свойства туда и изменить код тут */
                        if (in_array($_link['local_prop_id'], [95, 97, 107]))
                            $this->syncHtmlProp($_link);
                        else
                            $this->syncStringProp($_link);

                        break;

                    default:
                        $this->stdout("Unsupported property type {$_link['local_type']}. Skipping...\n", Console::FG_RED);
                        continue 2;

                }

                $this->stdout("Done\n\n", Console::FG_YELLOW);

            }

        }

    }

    /** Синхронизирует числовые свойства */
    protected function syncNumberProp($prop)
    {

        /** @var выборка значений свойтсв $query */
        $selectQuery = "
            select distinct
                UNIX_TIMESTAMP(b.date_create) as created_at,
                UNIX_TIMESTAMP(b.timestamp_x) as updated_at,
                {$prop['local_prop_id']} as property_id,
                ce.id as element_id,
                bep.value_num as value,
                bep.value_num as value_num,
                bep.value_num as value_enum
            from front2.b_iblock_element_property bep
            left join front2.b_iblock_element b ON b.id=bep.iblock_element_id AND b.iblock_id={$prop['front_iblock_id']}
            left join front2.b_iblock_property bp ON bp.id=bep.iblock_property_id
            left join cms_content_element ce ON ce.bitrix_id=bep.iblock_element_id and ce.content_id={$prop['local_content_id']}
            where
                bep.iblock_property_id={$prop['front_prop_id']}
                and ce.id is not null
                and bp.iblock_id={$prop['front_iblock_id']}
                and ce.content_id={$prop['local_content_id']}
        ";

        if (strtolower($this->syncMode) === SyncController::SYNC_MODE_UPDATED) {
            $selectQuery = $this->cleanPropValuesForElements($selectQuery, $prop['local_prop_id']);

            if (is_bool($selectQuery))
                return 0;
        }

        /** чистим полностью значения свосйтсв */
        if (strtolower($this->syncMode) === SyncController::SYNC_MODE_FULL) {

            $this->stdout($this->ansiFormat("Warning!", Console::FG_RED, Console::BOLD) . " Old values will be deleted! You have 3 seconds to stop script\n");
            $this->delay(3);

            $this->stdout("Prepare to clean " . $prop['local_code'] . " property\n", Console::FG_GREY);
            $this->clearProductsProps((int)$prop['local_prop_id']);

        }

        $insertQuery = "
            INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value, value_num, value_enum)
            {$selectQuery}
            ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value), value_num=VALUES(value_num), value_enum=VALUES(value_enum)
        ";

        return $this->runSyncTrnsaction($insertQuery);

    }

    /** Синхронизирует строковые свойства - НЕ HTML */
    protected function syncStringProp($prop)
    {

        /** Внимание! Свойство NOT_PUBLIC в Битриксе списочное с одним элементом списка
         * В новом свойство переделал в строку
         */

        $selectQuery = "
            select distinct
                UNIX_TIMESTAMP(b.date_create) as created_at,
                UNIX_TIMESTAMP(b.timestamp_x) as updated_at,
                {$prop['local_prop_id']} as property_id,
                ce.id as element_id,
                CASE WHEN bp.code='NOT_PUBLIC' AND bep.value IS NOT NULL AND bep.value != 'N' THEN 'Y' ELSE bep.VALUE END as value,
                null as value_num,
                null as value_enum
            from front2.b_iblock_element_property bep
            left join front2.b_iblock_element b ON b.id=bep.iblock_element_id AND b.iblock_id={$prop['front_iblock_id']}
            left join front2.b_iblock_property bp ON bp.id=bep.iblock_property_id
            left join cms_content_element ce ON ce.bitrix_id=bep.iblock_element_id and ce.content_id={$prop['local_content_id']}
            where
                bep.iblock_property_id={$prop['front_prop_id']}
                and ce.id is not null
                and ce.content_id={$prop['local_content_id']}
                and b.iblock_id={$prop['front_iblock_id']}
        ";

        if (strtolower($this->syncMode) === SyncController::SYNC_MODE_UPDATED) {
            $selectQuery = $this->cleanPropValuesForElements($selectQuery, $prop['local_prop_id']);

            if (is_bool($selectQuery))
                return 0;
        }

        /** чистим полностью значения свосйтсв */
        if (strtolower($this->syncMode) === SyncController::SYNC_MODE_FULL) {

            $this->stdout($this->ansiFormat("Warning!", Console::FG_RED, Console::BOLD) . " Old values will be deleted! You have 3 seconds to stop script\n");
            //$this->delay(3);

            $this->stdout("Prepare to clean " . $prop['local_code'] . " property\n", Console::FG_GREY);
            $this->clearProductsProps((int)$prop['local_prop_id']);

        }

        $insertQuery = "
            INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value, value_num, value_enum)
            {$selectQuery}
            ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value), value_num=VALUES(value_num), value_enum=VALUES(value_enum)
        ";

        $result = $this->runSyncTrnsaction($insertQuery);

        /** свойство NOT_PUBLIC удаляем отдельно при обычной синхронизации, т.к. в битриксе флаг N не ставится, а запись просто удаляется */
        /** !!!! ТЕПЕРь СТАВИТСЯ ФЛАГ N !!!!!! */
        if ($prop['local_code'] == 'NOT_PUBLIC') {
            $itemsForDeleteQuery = "
                select cep.id
                    from cms_content_element ce
                    left join cms_content_element_property cep ON cep.element_id = ce.id AND cep.property_id = {$prop['local_prop_id']}
                    left join front2.b_iblock_element b ON b.id=ce.bitrix_id AND b.iblock_id={$prop['front_iblock_id']}
                    left join front2.b_iblock_element_property bep on bep.iblock_element_id = b.id and bep.iblock_property_id={$prop['front_prop_id']}
                    where ce.content_id = {$prop['local_content_id']}
                    #and cep.value = 'Y'
                    and (bep.value is null or bep.value = 'N')
                    and b.iblock_id={$prop['front_iblock_id']}
                ";
            $itemsIds = \Yii::$app->db->createCommand($itemsForDeleteQuery)->queryColumn();

            $params = [];
            $condition = \Yii::$app->db->getQueryBuilder()->buildCondition(['IN', 'id', $itemsIds], $params);
            $deleteQuery = "
                DELETE FROM cms_content_element_property where {$condition}
            ";
            \Yii::$app->db->createCommand($deleteQuery, $params)->execute();
        }

        return $result;

    }

    /** Синхронизирует строковые свойства - ТОЛЬКО HTML
     *
     * Хотя передавать можно и простой текст, но время на обработку и обновления свойства уйдет в разы больше
     */
    protected function syncHtmlProp($prop)
    {

        $selectQuery = "
            select distinct
                UNIX_TIMESTAMP(b.date_create) as created_at,
                UNIX_TIMESTAMP(b.timestamp_x) as updated_at,
                {$prop['local_prop_id']} as property_id,
                ce.id as element_id,
                bep.value as value,
                bp.user_type as user_type,
                bp.user_type_settings as user_type_settings
            from front2.b_iblock_element_property bep
            left join front2.b_iblock_element b ON b.id=bep.iblock_element_id AND b.iblock_id={$prop['front_iblock_id']}
            left join front2.b_iblock_property bp ON bp.id={$prop['front_prop_id']}
            left join cms_content_element ce ON ce.bitrix_id=bep.iblock_element_id and ce.content_id={$prop['local_content_id']}
            where
                bep.iblock_property_id={$prop['front_prop_id']}
                and ce.id is not null
                and bep.VALUE is not null
                and ce.content_id={$prop['local_content_id']}
            ";

        if (strtolower($this->syncMode) === SyncController::SYNC_MODE_UPDATED) {
            $selectQuery = $this->cleanPropValuesForElements($selectQuery, $prop['local_prop_id']);

            if (is_bool($selectQuery))
                return 0;
        }

        /** чистим полностью значения свосйтсв */
        if (strtolower($this->syncMode) === SyncController::SYNC_MODE_FULL) {

            $this->stdout($this->ansiFormat("Warning!", Console::FG_RED, Console::BOLD) . " Old values will be deleted! You have 3 seconds to stop script\n");
            //$this->delay(3);

            $this->stdout("Prepare to clean " . $prop['local_code'] . " property\n", Console::FG_GREY);
            $this->clearProductsProps((int)$prop['local_prop_id']);

        }

        $bitrixProps = \Yii::$app->db->createCommand($selectQuery)->queryAll();

        $this->stdout("Got " . count($bitrixProps) . " values to parse\n", Console::FG_GREEN);

        $_parsedValues = [];

        foreach ($bitrixProps as $p) {

            $_textValue = '';

//            $this->stdout("Prop type: {$p['user_type']}\n", Console::FG_YELLOW);
//            $this->stdout("Prop value:\n\n {$p['value']}\n\n", Console::FG_YELLOW);

            if ($p['user_type'] !== null && strtolower($p['user_type']) == 'html') {


                /** Holy fuck!
                 *
                 * Для HTML значений свойств. Ввиду наличия \n serialize() работает криво:
                 *
                 * PHP Notice 'yii\base\ErrorException' with message 'unserialize(): Error at offset 656 of 671 bytes'
                 * see http://stackoverflow.com/questions/10152904/unserialize-function-unserialize-error-at-offset
                 *
                 */

                $p['value'] = preg_replace('/\n/', '', $p['value']);

//                $this->stdout("Prop value (after \\n clean up):\n\n {$p['value']}\n\n", Console::FG_YELLOW);


                $p['value'] = preg_replace_callback('/s:(\d+):"(.*?)";/', function ($match) {
                    return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
                }, $p['value']);

//                $this->stdout("Prop value: (afre replacing)\n\n {$p['value']}\n\n", Console::FG_YELLOW);


                try {

                    $_ = @unserialize($p['value']);

                    if ($_ === false) {
                        $this->stdout("\tBAD SERIALIZED DATA! (elementId={$p['element_id']}, value: {$p['value']})\n", Console::FG_RED);
//                        $errors[] = "Product/Offer: ID={$p['element_id']} property ID={$p['property_id']}";
                        continue;
                    }

//                    $this->stdout("Prop value unserialized:\n\n ".json_encode($p['value']) . "\n\n", Console::FG_YELLOW);

                    //

                    if (!is_bool($_) && !is_string($_) && is_array($_) & array_key_exists('TEXT', $_))
                        $_textValue = $_['TEXT'];

                } catch (ErrorException $e) {
                    $this->stdout("\tBAD SERIALIZED DATA! (elementId={$p['element_id']}, value: {$p['value']})\n", Console::FG_RED);
//                    $errors[] = "Product/Offer: ID={$p['element_id']} property ID={$p['property_id']} - ".$e->getMessage();
                    continue;

                }

            } elseif ($p['user_type'] === null) {
                $_textValue = nl2br($p['value']);
            }

//            $this->stdout("Cleaned value:\n\n {$_textValue}\n\n", Console::FG_YELLOW);


            $_parsedValues[] = [
                'created_at' => $p['created_at'],
                'updated_at' => $p['updated_at'],
                'property_id' => $prop['local_prop_id'],
                'element_id' => $p['element_id'],
                'value' => Html::encode($_textValue)
            ];

        }

        $_insertQuery = "INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value) VALUES ";

        foreach (array_chunk($_parsedValues, 1000, true) as $chunk) {

            $query = '';

            foreach ($chunk as $v)
                $query .= "('" . implode("','", $v) . "'),";

            $_affected = \Yii::$app->db->createCommand($_insertQuery . substr($query, 0, -1))->execute();

            $this->stdout($_affected . "... ", Console::FG_GREEN);

        }

    }


    /** Синхронизирует ссылочные свойства */
    protected function syncRefProp($prop)
    {


        $selectQuery = "
            select distinct
                UNIX_TIMESTAMP(b.DATE_CREATE) as created_at,
                UNIX_TIMESTAMP(b.TIMESTAMP_X) as updated_at,
                {$prop['local_prop_id']} as property_id,
                ce.id as element_id,
                ev.id as value,
                ev.id as value_num,
                ev.id as value_enum
            from front2.b_iblock_element_property bep
            left join front2.b_iblock_element b ON b.id=bep.iblock_element_id AND b.iblock_id={$prop['front_iblock_id']}
            left join front2.b_iblock_property bp ON bp.id=bep.iblock_property_id
            LEFT JOIN cms_content c ON c.code=bp.code
            LEFT JOIN cms_content_element ev ON ev.bitrix_id=bep.value AND ev.content_id=c.id
            left join cms_content_element ce ON ce.bitrix_id=bep.iblock_element_id and ce.content_id={$prop['local_content_id']}
            where
                bep.iblock_property_id={$prop['front_prop_id']}
                and ce.content_id={$prop['local_content_id']}
                and b.iblock_id={$prop['front_iblock_id']}
                and ce.id is not NULL
                and ev.id is not null
        ";

        if (strtolower($this->syncMode) === SyncController::SYNC_MODE_UPDATED) {
            $selectQuery = $this->cleanPropValuesForElements($selectQuery, $prop['local_prop_id']);

            if (is_bool($selectQuery))
                return 0;
        }

        /** чистим полностью значения свосйтсв */
        if (strtolower($this->syncMode) === SyncController::SYNC_MODE_FULL) {

            $this->stdout($this->ansiFormat("Warning!", Console::FG_RED, Console::BOLD) . " Old values will be deleted! You have 3 seconds to stop script\n");
            //$this->delay(3);

            $this->stdout("Prepare to clean " . $prop['local_code'] . " property\n", Console::FG_GREY);
            $this->clearProductsProps((int)$prop['local_prop_id']);

        }

        $insertQuery = "
            INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value, value_num, value_enum)
            {$selectQuery}
            ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value), value_num=VALUES(value_num), value_enum=VALUES(value_enum)
        ";

        return $this->runSyncTrnsaction($insertQuery);

    }

    private function runSyncTrnsaction($query, $attempt = 0)
    {

        $this->stdout("Start transaction - ", Console::FG_YELLOW);

        try {

            \Yii::$app->db->close();
            \Yii::$app->db->open();

            $_transactionTime = time();

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout("done. Affected " . $affected . " rows in " . (time() - $_transactionTime) . "sec\n", Console::FG_GREEN);

            $transaction->commit();

            return true;

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();

            if ($attempt >= 3) {
                return false;
            }

            return $this->runSyncTrnsaction($query, ++$attempt);
        }

    }

    /** Магическая функция которая не должна существовать, но та мне казалось оптимальней чтобы не дуьлировать код
     *
     * На вход передается запрос выборки значений обновленный свойств
     * Если значений больше 0 - чистится устааревшие и (!) возвращается запрос для последущей синхронизации
     * Если значений = 0 - возвращается false
     */
    private function cleanPropValuesForElements($selectQuery, $propId)
    {

        $_propLastUpdatedTimestamp = $this->getPropertyLastUpdatedTimestamp($propId);

        $this->stdout("Property max updated date time : " . date("Y.m.d H:i:s", $_propLastUpdatedTimestamp) . "\n", Console::FG_YELLOW);

        /** ВНИМАНИЕ! алиасы таблиц я писал понятные для себя b - bitrix_element
         * у значений свойств нет даты обновления и ее приходится брать у элемента
         */
        $selectQuery .= " AND UNIX_TIMESTAMP(b.timestamp_x) > {$_propLastUpdatedTimestamp} ";

        /** удаляем значения обновленных свойств */
        $updatedValues = \Yii::$app->db->createCommand($selectQuery)->queryAll();

        if (count($updatedValues) == 0) {
            $this->stdout("Looks fresh!\n", Console::FG_GREEN);
            return false;
        }

        $this->stdout("Changed values count: " . count($updatedValues) . "\n", Console::FG_YELLOW);

        /** удаляем старые
         *
         * element_id - это поле cms_content_element_property. детали см. в приходящем $selectQuey
         */
        $elementIds = ArrayHelper::getColumn($updatedValues, 'element_id');

        $this->stdout("Cleaning old values ", Console::FG_YELLOW);

        $_affected = \Yii::$app->db->createCommand("DELETE FROM cms_content_element_property WHERE property_id={$propId} AND element_id IN (" . implode(', ', $elementIds) . ")")
            ->execute();

        $this->stdout(" done. Affected {$_affected} rows.\n", Console::FG_GREEN);

        return $selectQuery;

    }

    public function actionLotNum()
    {

        $this->stdout("Syncing LOT_NUM properties\n", Console::FG_CYAN);


        $prop = $this->getLinkedPropByCode('LOT_NUM');

        $this->stdout("Link map: \n", Console::FG_GREEN);
        $this->stdout($prop['local_code'] . " <+> " . $prop['front_code'] . "\n" . $prop['local_id'] . " <+> " . $prop['front_id'] . "\n", Console::FG_YELLOW);

        $this->syncMode = SyncController::SYNC_MODE_FULL;
        $this->clearProductsProps($prop['local_id']);

        $query = "
            INSERT INTO cms_content_element_property (created_at, updated_at, property_id, element_id, value, value_num, value_enum)
            SELECT
                UNIX_TIMESTAMP(b.date_create),
                UNIX_TIMESTAMP(b.timestamp_x),
                {$prop['local_id']},
                ce.id,
                b.CODE,
                NULL,
                NULL
            FROM cms_content_element ce
            LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id AND b.iblock_id=10
            where
                ce.content_id in (2)
                and b.id is not null
            ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value), value_num=VALUES(value_num), value_enum=VALUES(value_enum)
        ";

        $this->stdout("Begin sync - ", Console::FG_YELLOW);

        try {

            $_transactionTime = time();

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout("done. Affected " . $affected . " rows in " . (time() - $_transactionTime) . "sec\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

        $this->stdout($prop['local_code'] . " synced!\n\n", Console::FG_GREEN);

        return true;

    }

    /**
     * Синхронизирует свойства ЦВЕТ (COLOR_REF), PARAMETER1, PARAMETER2 и т.д.
     * @param string $propertyCode - код справочника
     * @param string $frontHighloadTable - highload таблица
     * @return bool
     */
    public function actionLinkedRefProp($propertyCode = 'COLOR_REF', $frontHighloadTable = 'front2.highload_color_reference')
    {
        $propLinks = $this->getLinkedPropByCode($propertyCode);
        $cmsContent = \common\models\cmsContent\CmsContent::findOne(['code' => $propertyCode]);

        \Yii::$app->db->createCommand()->execute("set sql_mode='';\n");

        $query = "
            INSERT INTO cms_content_element_property (id, created_at, updated_at, property_id, element_id, value, value_num, value_enum)
            SELECT
                ifnull(exists_value.id, 'default'),
                UNIX_TIMESTAMP(b.date_create),
                UNIX_TIMESTAMP(b.timestamp_x),
                {$propLinks['local_id']},
                ce.id,
                ref.id,
                ref.id,
                ref.id
            FROM cms_content_element ce
            LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id
            LEFT JOIN front2.b_iblock_element_property bep ON bep.iblock_element_id = ce.bitrix_id and bep.iblock_property_id={$propLinks['front_id']}
            LEFT JOIN {$frontHighloadTable} hlref ON hlref.UF_XML_ID=bep.VALUE
            LEFT JOIN cms_content_element ref ON ref.bitrix_id=hlref.id and ref.content_id={$cmsContent->id}            
            LEFT JOIN cms_content_element_property exists_value ON exists_value.element_id=ce.id  and exists_value.property_id={$propLinks['local_id']}
            where
                ce.content_id in (2,10)
                and bep.VALUE IS NOT NULL
            ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), value=VALUES(value), value_num=VALUES(value_num), value_enum=VALUES(value_enum)
        ";

        $this->stdout("Prepare to sync {$propertyCode} property\n", Console::FG_GREY);
        $this->stdout("Link map: \n", Console::FG_GREEN);
        $this->stdout($propLinks['local_id'] . " <+> " . $propLinks['front_id'] . "\n", Console::FG_YELLOW);
        $this->stdout("Begin sync - ", Console::FG_YELLOW);

        try {

            $_transactionTime = time();

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout("done. Affected " . $affected . " rows in " . (time() - $_transactionTime) . "sec\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

        $this->stdout("{$propertyCode} synced!\n\n", Console::FG_GREEN);

        return true;

    }

    // Удаляет лишний размер SIZE для колец, если указан SIZE_RING (88 - SIZE_RING, 100 - SIZE)
    protected function fixRingsSize()
    {
        $this->stdout("Prepare to delete sizes for rings\n", Console::FG_GREY);

        $query = <<<SQL
delete from cms_content_element_property where id in (
    select id from (
        select p1.id from cms_content_element_property p1 where ifnull(p1.value,'') != '' and p1.property_id = 100 and exists (
        select 1 from cms_content_element_property p2 where ifnull(p2.value,'') != '' and p2.property_id = 88 and p2.element_id = p1.element_id
        )
    ) a 
);
SQL;

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            $_transactionTime = time();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout("done. Affected " . $affected . " rows in " . (time() - $_transactionTime) . "sec\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

        return true;
    }

}