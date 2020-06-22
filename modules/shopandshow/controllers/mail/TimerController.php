<?php
namespace modules\shopandshow\controllers\mail;

use modules\shopandshow\widgets\mail\GifTimerWidget;
use skeeks\cms\base\Controller;
use Yii;

/**
 * Class TimerController
 * @package modules\shopandshow\controllers\mail
 */
class TimerController extends Controller
{

    public function actionIndex()
    {
        $timerEnd = \Yii::$app->request->get('time') ? : strtotime('tomorrow');

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-type:image/gif');

        echo GifTimerWidget::widget(['timerEnd' => $timerEnd]);

        exit;
    }
}
