<?php


namespace common\components\sendSms;

use \modules\shopandshow\models\sms\Sms as SmsModel;
use yii\web\Application;
use Yii;

class SmsLogger
{

    public function addLog(array $data)
    {
        $ip = '';

        if (Yii::$app instanceof Application) {
            $ip = (string)Yii::$app->getRequest()->getUserIP();
        }

        $model = new SmsModel([
            'phone' => $data['phone'],
            'text' => $data['text'],
            'provider' => $data['provider'],
//            'type' => $type,
//            'for_user_id' => $forUserId,
//            'must_sent_at' => $mustSentAt,
//            'fuser_id' => $fUserId,
            'ip' => $ip,
        ]);
        $model->save();

        return $model->id;

    }

    public function updateLog(int $id,array $data)
    {
        $model = SmsModel::find()->where(['id' => $id])->one();
        $model->status = $data['status'];
        $model->provider_answer = $data['answer'];
        $model->save();
    }

    public function check(int $id)
    {
        $model = SmsModel::find()->where(['id' => $id])->one();
        return $model ? true : false;
    }
}
