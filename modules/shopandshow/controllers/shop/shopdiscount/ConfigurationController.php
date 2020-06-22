<?php
namespace modules\shopandshow\controllers\shop\shopdiscount;

use yii\web\Controller;
use yii\db\Exception;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValue;
use modules\shopandshow\models\shop\shopdiscount\Configuration;
use modules\shopandshow\models\shop\shopdiscount\Entity;

/**
 * Class ConfigurationController
 * @package modules\shopandshow\controllers
 */
class ConfigurationController extends Controller
{
    public static function actionSave(ShopDiscount $shopDiscount)
    {
        if(self::isCreate()) {
            self::actionAdd($shopDiscount);
        }

        if ($deleteIds = self::isDelete()) {
            self::actionDeleteMultiple($deleteIds);
        }
    }

    /**
     * Проверяет, добавляется ли новое условие
     * @return bool
     */
    protected static function isCreate()
    {
        $configuration = \Yii::$app->request->post('Configuration');
        $entity_id = isset($configuration['shop_discount_entity_id']) ? $configuration['shop_discount_entity_id'] : null;
        return !empty($entity_id);
    }

    /**
     * Проверяет, удаляется ли условие, возвращая список удаляемых конфигураций
     * @return array
     */
    protected static function isDelete()
    {
        $configuration = \Yii::$app->request->post('Configuration');
        if(isset($configuration['delete'])) return $configuration['delete'];
        return [];
    }

    /**
     * Добавляет новую конфигурацию
     * @param ShopDiscount $shopDiscount
     * @throws Exception
     */
    protected static function actionAdd(ShopDiscount $shopDiscount)
    {
        $model = new Configuration(['shop_discount_id' => $shopDiscount->id]);
        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            if(!$model->save()){
                throw new Exception('Не удалось сохранить условия');
            }

            /* @var $entity Entity */
            $entity = $model->getEntity()->one();
            $configurationClassname = ConfigurationValue::getClassNameByEntityClass($entity->class);

            /* @var $configurationModel ConfigurationValue */
            $configurationModel = new $configurationClassname(['shop_discount_configuration_id' => $model->id]);
            if ($configurationModel->load(\Yii::$app->request->post()) && $configurationModel->validate()) {
                if(!$configurationModel->save()){
                    throw new Exception('Не удалось сохранить значения условий');
                }
            }
        }
    }

    /**
     * Удаляет выбранные конфигурации
     * @param array $deleteIds
     * @throws Exception
     */
    protected static function actionDeleteMultiple(array $deleteIds)
    {
        foreach ($deleteIds as $id) {
            $model = Configuration::findOne($id);
            if(!$model->delete()) throw new Exception('Не удалось удалить конфигурацию');
        }
    }
}
