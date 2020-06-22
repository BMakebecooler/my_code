<?php
namespace modules\shopandshow\controllers\lookbook;

use common\lists\Contents;
use modules\shopandshow\models\users\UserVotes;
use skeeks\cms\helpers\RequestResponse;
use yii\web\Controller;

/**
 * Class RatingController
 */
class RatingController extends Controller
{
    public function actionVote()
    {
        $rr = new RequestResponse();

        if(\Yii::$app->user->isGuest) {
            $rr->success = false;
            $rr->message = 'Только авторизованный пользователь может голосовать';
            return $rr;
        }

        if ($rr->isRequestAjaxPost()) {
            $modelId = \Yii::$app->request->post('model');
            $rating = (float)\Yii::$app->request->post('rating');

            // защита от особо умных
            if($rating <= 0 || $rating > 10) {
                $rr->success = false;
                $rr->message = 'Ошибочное значение рейтинга';
                return $rr;
            }

            if (!$modelId) {
                $rr->success = false;
                $rr->message = 'Модель не указана';
                return $rr;
            }

            $model = Contents::getContentElementById($modelId);

            if (!$model || $model->content_id != LOOKBOOK_CLIENTS_CONTENT_ID) {
                $rr->success = false;
                $rr->message = 'Модель не найдена';
                return $rr;
            }

            $userVotes = UserVotes::findForLookbook()
                ->andWhere(['cms_user_id' => \Yii::$app->user->identity->id])
                ->andWhere(['cms_content_element_id' => $model->id])
                ->one();

            if($userVotes) {
                $rr->success = false;
                $rr->message = 'Вы уже голосовали за эту модель';
                return $rr;
            }

            $newUserVote = new UserVotes([
                'vote_id' => UserVotes::LOOKBOOK_VOTE_ID,
                'cms_user_id' => \Yii::$app->user->identity->id,
                'cms_content_element_id' => $model->id,
                'value' => (string)$rating
            ]);

            $transaction = \Yii::$app->db->beginTransaction();

            if(!$newUserVote->save()) {
                $transaction->rollBack();
                $rr->success = false;
                $rr->message = 'Ошибка при сохранении данных';
                return $rr;
            }

            $rating = UserVotes::findForLookbook()->andWhere(['cms_content_element_id' => $model->id]);
            $newRating = round($rating->sum('value')/$rating->count(), 1);

            try {
                $model->relatedPropertiesModel->setAttribute('countLike', (string)$newRating);
                if(!$model->relatedPropertiesModel->save()) {
                    $transaction->rollBack();
                    $rr->success = false;
                    $rr->message = 'Ошибка при обновлении данных';
                    return $rr;
                }
            }
            catch(\Exception $e) {
                $transaction->rollBack();
                $rr->success = false;
                $rr->message = 'Ошибка при обновлении данных';
                return $rr;
            }

            $transaction->commit();
            $rr->success = true;

            return $rr;
        }
    }
}
