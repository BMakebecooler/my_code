<?php
/**
 * Base SyncController
 */

namespace console\controllers\sync;


use yii\base\Exception;
use yii\helpers\Console;


/**
 * Class SyncController
 *
 * @package console\controllers
 */
class SyncController extends \yii\console\Controller
{

    const
        /** Синхронизировать все значения свойств элементов */
        SYNC_MODE_FULL = 'full',

        /** Синхронизировать только обновленные значения свойств элементов */
        SYNC_MODE_UPDATED = 'new';

    const
        VERBOSE_ERRORS = 0,
        VERBOSE_ACTIONS = 1,
        VERBOSE_ALL = 3;

    const
        /** IBLOCK_ID */
        BITRIX_PRODUCT_BLOCK_ID=10,
        BITRIX_OFFERS_BLOCK_ID=11,

        /** CONTENT_ID */
        LOCAL_PRODUCT_BLOCK_ID=2,
        LOCAL_OFFERS_BLOCK_ID=10;

    /** @var Verbosity level  */
    public $verbosityLevel = self::VERBOSE_ALL;

    public $syncMode = null;

    protected $agentStartTime;

    protected $logger;

    protected $shopAndShow;

    public function beforeAction($action)
    {

        $this->verbosityLevel = self::VERBOSE_ACTIONS;

        $this->stdout("\nBegin: ".$action->getUniqueId()."\n\n", Console::FG_YELLOW);

        $this->agentStartTime = time();

        return true;
    }

    public function afterAction($action, $result)
    {

        $this->stdout("\n\nElapsed: ".(time() - $this->agentStartTime)."sec.\n", Console::FG_YELLOW);

        return parent::afterAction($action, $result);
    }

    protected function logError($e, $data = [])
    {

        $this->stdout(" Error\n", Console::FG_RED);
        $this->stdout(" Data: ".json_encode($data)."\n", Console::FG_YELLOW);
        $this->stdout("Error: ".$e->getMessage(), Console::FG_PURPLE);

    }

    protected function fixTableCollation($table, $column = 'code', $collation = 'utf8_unicode_ci')
    {

        $this->stdout("Altering table {$table} collation to {$collation}\n", Console::FG_YELLOW);
        $this->stdout("ALTER TABLE {$table} CHARACTER SET utf8 COLLATE {$collation}\n", Console::FG_GREY );
        if ( \Yii::$app->db->createCommand("ALTER TABLE {$table} CHARACTER SET utf8 COLLATE {$collation}")->execute() )
            $this->stdout("Ok\n", Console::FG_GREEN);

        $field = \Yii::$app->db->createCommand("SHOW FIELDS FROM {$table} where Field ='{$column}'")->queryOne();

        $field['Type'] = strtoupper($field['Type']);
        $field['Null'] = $field['Null'] == 'NO' ? 'NOT NULL' : '';

        $this->stdout("Altering table {$table} change {$column} column collation to {$collation}\n", Console::FG_YELLOW);
        $this->stdout("ALTER TABLE {$table} CHANGE {$column} {$column} {$field['Type']} CHARACTER SET utf8 COLLATE {$collation} {$field['Null']}\n", Console::FG_GREY );

        if ( \Yii::$app->db->createCommand("ALTER TABLE {$table} CHANGE {$column} {$column} {$field['Type']} CHARACTER SET utf8 COLLATE {$collation} {$field['Null']}")->execute() )
            $this->stdout("Ok\n", Console::FG_GREEN);


    }

    /**
     * @param $property_code mixed
     *  array => [ remote_property_code => local_property_code ]
     *  string property_code ( in case that they are same )
     */
    protected function setSqlVariables(int $local_prop_id, int $front_prop_id)
    {

        $this->stdout("set @local_property_id = {$local_prop_id};\n");
        \Yii::$app->db->createCommand()->execute("set @local_property_id = {$local_prop_id};");

        $this->stdout("set @front_property_id = {$front_prop_id};\n");
        \Yii::$app->db->createCommand()->execute("set @front_property_id = {$front_prop_id};");

        \Yii::$app->db->createCommand()->execute("set sql_mode='';");

        return true;

    }

    protected function clearProductsProps($propId)
    {

        if ( $this->syncMode === SyncController::SYNC_MODE_FULL ) {

            $this->stdout("Cleaning values for prop " . $propId, Console::FG_YELLOW);
            $affected = \Yii::$app->db->createCommand("DELETE FROM cms_content_element_property where property_id={$propId} and element_id IN (SELECT id FROM cms_content_element WHERE content_id IN (2,10));")->execute();
            $this->stdout(" done. Affected {$affected} rows\n", Console::FG_GREEN);

        }

    }

    protected function delay(int $sec)
    {
        $this->stdout("Sleeping for {$sec}sec. ", Console::FG_GREY);
        for ($k=1; $k<=$sec; $k++) {
            $this->stdout("{$k}... ");
            sleep(1);
        }
        $this->stdout("\n");
    }

    protected function getLinkedProps(string $type, string $component = '')
    {

        $query = "
            SELECT
                lp.CODE as local_code, lp.ID as local_id,
                fp.CODE as front_code, fp.ID as front_id
            FROM cms_content_property lp
            LEFT JOIN front2.b_iblock_property fp ON fp.ID = lp.vendor_id
            WHERE
              lp.property_type='{$type}'
              AND fp.ID is NOT NULL
        ";

        if ( strlen($component) > 0 )
            $query .= " lp.component='{$component}' ";

        $this->stdout("Select linked props: ", Console::FG_GREY);

        $propsLinks = \Yii::$app->db->createCommand($query)->queryAll();

        $this->stdout(" ".count($propsLinks).".\n", Console::FG_GREEN);
//        $this->stdout("Codes: ".implode(', ', $propsLinks).".\n", Console::FG_GREEN);

        return $propsLinks;

    }

    protected function getLinkedPropByCode(string $code)
    {

        $query = "
            SELECT
                lp.CODE as local_code, lp.ID as local_id,
                fp.CODE as front_code, fp.ID as front_id
            FROM cms_content_property lp
            LEFT JOIN front2.b_iblock_property fp ON fp.ID = lp.vendor_id
            WHERE
              lp.code='{$code}'
              AND fp.ID is NOT NULL
        ";

        $this->stdout("Select linked props: ", Console::FG_GREY);

        $propsLinks = \Yii::$app->db->createCommand($query)->queryOne();

        $this->stdout(" ".count($propsLinks).".\n", Console::FG_GREEN);

        return $propsLinks;

    }

    protected function getPropertyLastUpdatedTimestamp($propId)
    {

        $_ =  \Yii::$app->db->createCommand("
            SELECT MAX(updated_at)
            FROM cms_content_element_property
            WHERE property_id={$propId}
        ")->queryScalar();

        return (int) $_;

    }

    /**
     *
     * Возвращает связь Свойства
     *
     *
     * @param array $prop [ 'field' => 'value' ],
     *      eg. [ 'code' => 'COLOR_REF' ]
     *      CASE SENSITIVE
     *
     * @return array|null Property link array
     * [
     *  'local_id' => ID локального свойства
     *  'local_code' => CODE локального свойства
     *
     *  'front_id' => ID битрикс свойства
     *  'front_code' => CODE битрикс свойства
     * ]
     *
     */
    protected function getPropLink(array $prop)
    {

        if (empty($prop))
            return null;

        $_field = key($prop);
        $_value = array_shift($prop);

        $query = "
            SELECT
                lp.CODE as local_code, lp.ID as local_prop_id, lp.content_id as local_content_id, lp.property_type as local_type, lp.multiple as local_is_multiple,
                fp.CODE as front_code, fp.ID as front_prop_id, fp.iblock_id as front_iblock_id, fp.PROPERTY_TYPE as front_type, fp.MULTIPLE as front_is_multiple
            FROM cms_content_property lp
            LEFT JOIN front2.b_iblock_property fp ON fp.ID = lp.vendor_id
            WHERE
              lp.{$_field}='{$_value}'
              AND fp.ID is NOT NULL
        ";

        $this->stdout("Get property link: {$_field} => {$_value}\n", Console::FG_YELLOW);

        $propLinks = \Yii::$app->db->createCommand($query)->queryAll();

        if ($propLinks == null) {
            $this->stdout("not found!\n", Console::FG_RED);
            return null;
        }

        foreach ($propLinks as $propLink) {

            $this->stdout("\n[" . str_pad("-", 74, '-', STR_PAD_BOTH) . "]\n", Console::FG_GREY);
            $this->stdout("[ " . str_pad("Property link map", 72, ' ', STR_PAD_BOTH) . " ]\n", Console::FG_GREY);
            $this->stdout("[" . str_pad("-", 74, '-', STR_PAD_BOTH) . "]\n", Console::FG_GREY);

            $this->stdout(
                "|" . str_pad("DB", 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad("ID", 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad("IBLOCK ID", 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad("CODE", 30, ' ', STR_PAD_BOTH) .
                "|" . str_pad("TYPE", 10, ' ', STR_PAD_BOTH) . "|\n", Console::FG_GREY);

            $this->stdout("[" . str_pad("-", 74, '-', STR_PAD_BOTH) . "]\n", Console::FG_GREY);

            $this->stdout(
                "|" . str_pad("FRONT", 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad($propLink['front_prop_id'], 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad($propLink['front_iblock_id'], 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad($propLink['front_code'], 30, ' ', STR_PAD_BOTH) .
                "|" . str_pad($propLink['front_type'], 10, ' ', STR_PAD_BOTH) . "|\n", Console::FG_GREY);

            $this->stdout(
                "|" . str_pad("SITE", 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad($propLink['local_prop_id'], 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad($propLink['local_content_id'], 10, ' ', STR_PAD_BOTH) .
                "|" . str_pad($propLink['local_code'], 30, ' ', STR_PAD_BOTH) .
                "|" . str_pad($propLink['local_type'], 10, ' ', STR_PAD_BOTH) . "|\n", Console::FG_GREY);
            $this->stdout("[" . str_pad("---", 74, '-', STR_PAD_BOTH) . "]\n\n", Console::FG_GREY);

        }

        return $propLinks;

    }


    protected function logQuery(string $query)
    {

        if ( $this->verbosityLevel === static::VERBOSE_ALL )
            $this->stdout("Query:\n>>>\n\n{$query}\n\n<<<\n", Console::FG_GREY);

    }

    /**
     * @return int Time in seconds from start
     */
    protected function getActionDuration()
    {
        return ( time() - $this->agentStartTime );
    }

}






class SyncControllerException extends Exception {

}