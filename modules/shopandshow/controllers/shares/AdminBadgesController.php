<?php

namespace modules\shopandshow\controllers\shares;

use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shares\badges\SsBadge;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;

/**
 * Class AdminBadgesController
 *
 * @package modules\shopandshow\controllers
 */
class AdminBadgesController extends AdminModelEditorController
{
    public function init()
    {
        $this->modelClassName = SsBadge::className();
        $this->name = 'Плашки';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    public function actionProductSearch($q = null)
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
                ['like', 'bitrix_id', $q],
            ])
            ->select(['id', 'name', 'code'])
            ->orderBy('name, id');

        $out['results'] = $contentElement->asArray()->all();

        return $out;
    }

    /**
     * Очистка свойства "Текста плашки" для товаров связанных с определенной плашкой
     * @return RequestResponse
     */
    public function actionClearBadgeText(){
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost() && $badgeId = \Yii::$app->request->post('badgeId')) {
            $badge = SsBadge::findOne(['id' => $badgeId]);

            if ($badge){
                $updatedRows = $badge->clearProductsBadgeText();

                if ($updatedRows){
                    $rr->success = true;
                    $rr->message = "Текста плашек очищены.";
                }else{
                    $rr->success = true;
                    $rr->message = "Очистка успешна, но ничего не обновлено.";
                }
            }else{
                $rr->success = false;
                $rr->message = "Плашки с ID='{$badgeId}' не найдено";
            }

            return $rr;
        }
    }
}
