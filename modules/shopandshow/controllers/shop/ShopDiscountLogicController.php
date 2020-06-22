<?php
namespace modules\shopandshow\controllers\shop;

use yii\web\Controller;
use yii\db\Exception;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\SsShopDiscountLogic;

/**
 * Class ShopDiscountLogicController
 * @package modules\shopandshow\controllers
 */
class ShopDiscountLogicController extends Controller
{
    public static function actionSave(ShopDiscount $shopDiscount)
    {
        $models = $shopDiscount->getShopDiscountLogics()->all();

        if (self::isCreate()) {
            $models['new'] = new SsShopDiscountLogic(['shop_discount_id' => $shopDiscount->id]);
        }

        if (SsShopDiscountLogic::loadMultiple($models, \Yii::$app->request->post()) && SsShopDiscountLogic::validateMultiple($models)) {
            /** @var SsShopDiscountLogic $model */
            foreach ($models as $model) {
                if ($model->flag_delete) {
                    if (!$model->delete()) {
                        throw new Exception('Не удалось удалить скидку #'.$model->id);
                    }
                }
                elseif (!$model->save()) {
                    throw new Exception('Не удалось сохранить скидку #'.$model->id);
                }
            }
        }
        else {
            foreach ($models as $model) {
                if ($model->hasErrors()) {
                    throw new Exception(print_r($model->getErrors(), true));
                }
            }
            throw new Exception('Не удалось сохранить скидки');
        }
    }

    /**
     * Проверяет, добавляется ли новое условие
     * @return bool
     */
    protected static function isCreate()
    {
        $isCreate = \Yii::$app->request->post('ShopDiscountLogicCreate');
        return !empty($isCreate);
    }
}
