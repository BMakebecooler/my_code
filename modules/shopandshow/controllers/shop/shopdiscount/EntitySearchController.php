<?php
namespace modules\shopandshow\controllers\shop\shopdiscount;

use \skeeks\cms\shop\controllers\AdminDiscountController as SXAdminDiscountController;
use common\models\cmsContent\CmsContentElement;

/**
 * Class EntitySearchController
 * @package modules\shopandshow\controllers
 */
class EntitySearchController extends SXAdminDiscountController
{
    public function init()
    {
        parent::init();

        $this->modelClassName           = CmsContentElement::className();
    }

    public function actionSearch($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = ['results' => []];

        $contentElement = CmsContentElement::find()
                          ->active()
                          ->where(['content_id' => PRODUCT_CONTENT_ID])
                          ->andFilterWhere([
                              'OR',
                              ['like', 'name', $q],
                              //['like', 'code', $q.'%', false],
                              ['like', 'code', $q],
                          ])
                          ->select(['id', 'name', 'code'])
                          ->orderBy('name');

        $out['results'] = $contentElement->asArray()->all();

        return $out;
    }
}
