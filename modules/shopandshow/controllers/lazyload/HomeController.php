<?php

namespace modules\shopandshow\controllers\lazyload;

use common\widgets\content\lookbook\LookBook;
use common\widgets\onair\OnAir;
use common\widgets\cts\Cts;
use skeeks\cms\base\Controller;
use skeeks\cms\helpers\RequestResponse;

/**
 * Class HomeController
 * @package modules\shopandshow\controllers\lazyload
 */
class HomeController extends Controller
{

    public function actionLookBook()
    {
        $rr = new RequestResponse();
        $rr->success = false;

        if ($rr->isRequestAjaxPost()) {
            $rr->success = true;
            $rr->data = [
                'html' => LookBook::widget(),
            ];
        }

        return $rr;
    }

    public function actionCts()
    {
        $rr = new RequestResponse();
        $rr->success = false;

        if ($rr->isRequestAjaxPost()) {
            $rr->success = true;
            $rr->data = [
                'html' => Cts::widget([
                    'params' => [
                        'section' => [
                            'class' => ' with-shadow'
                        ]
                    ]
                ]),
            ];
        }

        return $rr;
    }

    // disable not use
//    public function actionOnair()
//    {
//        $rr = new RequestResponse();
//        $rr->success = false;
//
//        if ($rr->isRequestAjaxPost()) {
//            $rr->success = true;
//            $rr->data = [
//                'html' => OnAir::widget([
//                    'limitProduct' => 20,
//                    'showProductCurrentHour' => true,
//                ]),
//            ];
//        }
//
//        return $rr;
//    }

    /**
     * Выбор посетителей
     * @return RequestResponse
     */
    public function actionVisitorsChoice()
    {
        $rr = new RequestResponse();
        $rr->success = false;

        if ($rr->isRequestAjaxPost()) {
            $rr->success = true;
            $rr->data = [
                'html' => $this->renderAjax('@template/widgets/ContentElementsCms/home/visitors-slides/index'),
            ];
        }

        return $rr;
    }
}