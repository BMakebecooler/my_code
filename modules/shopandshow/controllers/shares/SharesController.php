<?php

namespace modules\shopandshow\controllers\shares;

use modules\shopandshow\models\shares\SsShare;
use skeeks\cms\base\Controller;
use skeeks\cms\helpers\RequestResponse;

/**
 * Class SharesController
 * @package modules\shopandshow\controllers\shares
 */
class SharesController extends Controller
{


    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * Инкремент просмотров указанных баннеров
     *
     * @return RequestResponse
     */
    public function actionIncrViews()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost() && $sharesIds = \Yii::$app->request->post('shares')) {
            $sharesIdsStr = implode(', ', $sharesIds);
            foreach ($sharesIds as $shareId) {
                //TODO ключить когда с редисом разберемся почему глючит
                //\Yii::$app->redis->incr("view_banner_{$shareId}");
            }

            $rr->success = true;
            $rr->message = "Просмотры баннеров ({$sharesIdsStr}) обновлены.";

            return $rr;
        }
    }

    /**
     * Инкремент кол-ва кликов по указанному баннеру
     * @return RequestResponse
     */
    public function actionIncrClicks()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost() && $shareId = \Yii::$app->request->post('shareId')) {
            $share = SsShare::findOne($shareId);
            if ($share) {
                $share->count_click++;
                if ($share->save()) {
                    $rr->success = true;
                    $rr->message = "Просмотры баннера #{$shareId} обновлены ({$share->count_click}).";
                } else {
                    $rr->success = false;
                    $rr->message = "Ошибка при сохранеии обновления баннера #{$shareId} (" . $share->getErrors() . ").";
                }
            } else {
                $rr->success = false;
                $rr->message = "Баннер #{$shareId} не найден.";
            }

            return $rr;
        }
    }

    /**
     * Инкремент просмотров страницы с указанными баннерами
     *
     * @return RequestResponse
     */
    public function actionIncrPageViews()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost() && $sharesIds = \Yii::$app->request->post('shares')) {

            $updatedBannersNum = SsShare::updateAllCounters(['count_page_views' => 1], ['id' => array_unique($sharesIds)]);

            $rr->success = true;

            if ($updatedBannersNum) {
                $rr->message = "Обновлен просмотр страницы для {$updatedBannersNum} баннеров.";
            } else {
                $rr->message = "Выполнено, но не обновлено ни одного баннера. ";
            }

            return $rr;
        }
    }
}