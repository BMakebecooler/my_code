<?php

namespace modules\shopandshow\controllers\support;

use common\lists\Contents;
use common\models\cmsContent\forms\SupportQuestion;
use skeeks\cms\helpers\RequestResponse;
use yii\web\Controller;

/**
 * Class Controller
 * @package modules\shopandshow\controllers
 */
class QuestionsController extends Controller
{

    /**
     * рейтинг для "Вы нашли ответ на ваш вопрос?"
     */
    public function actionSetRating()
    {
        $rr = new RequestResponse();
        $rr->success = false;

        if ($rr->isRequestAjaxPost()) {
            $modelId = \Yii::$app->request->post('question-id');
            $like = \Yii::$app->request->post('like');
            $dislike = \Yii::$app->request->post('dislike');

            if ($question = Contents::getContentElementById($modelId)) {
                if ($like) {
                    $count = ((int)$question->relatedPropertiesModel->getAttribute('like')) + 1;
                    $question->relatedPropertiesModel->setAttribute('like', $count);
                    $question->relatedPropertiesModel->save();
                    $rr->success = true;
                } elseif ($dislike) {
                    $count = ((int)$question->relatedPropertiesModel->getAttribute('dislike')) + 1;
                    $question->relatedPropertiesModel->setAttribute('dislike', $count);
                    $question->relatedPropertiesModel->save();
                    $rr->success = true;
                }
            }
        }

        return $rr;
    }

    /**
     * @return array|\yii\web\Response
     */
    public function actionCreate()
    {
        $rr = new RequestResponse();

        $model = new SupportQuestion();

        //Запрос на валидацию ajax формы
        if ($rr->isRequestOnValidateAjaxForm()) {
            return $rr->ajaxValidateForm($model);
        }

        //Запрос ajax post
        if ($rr->isRequestAjaxPost()) {
            if ($model->load(\Yii::$app->request->post()) && ($model->processed())) {
                $rr->success = true;
                $rr->message = 'Добавлен вопрос';
            } else {
                $rr->success = false;
                $rr->message = 'Не удалось добавить вопрос';
            }

            return $rr;

        } else if (\Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && ($model->processed())) {

                /**
                 *
                 */
                if ($model->parent_id) {
                    return $this->redirect(['/profile/dialog', 'id' => $model->parent_id]);
                } else if ($model->element_id) {
                    $product = Contents::getContentElementById($model->element_id);
                    return $this->redirect($product->absoluteUrl);
                }
            }
        }
    }
}
