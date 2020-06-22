<?php

namespace common\components\sms;

use common\helpers\Strings;
use lowbase\sms\Sms as LBSms;
use \modules\shopandshow\models\sms\Sms as SmsModel;
use Yii;
use yii\base\Exception;
use yii\web\Application;

class Sms extends LBSms implements SmsInterface
{

    /**
     * Лимит на отправку не чаще чем 1 минут
     */
    const MAX_TIME_LIMIT_REQUEST = 60;

    const SMS_TYPE_FOR_ADMINS = 1;
    const SMS_TYPE_GET_PASSWORD = 2;
    const SMS_TYPE_CREATE_ORDER = 3;

    /**
     * Send Sms and put info into database
     * @param $phone
     * @param $text
     * @param bool $saveInfo
     * @param null $type
     * @param null $forUserId
     * @param null $mustSentAt
     * @param array $options
     * @param null $fUserId
     * @return bool
     */
    public function sendSms($phone, $text,
                            $saveInfo = true,
                            $type = null,
                            $forUserId = null,
                            $mustSentAt = null,
                            $options = [],
                            $fUserId = null
    )
    {

        $phone = $this->preparePhone($phone);

        $ip = '';

        if (Yii::$app instanceof Application) {
            $ip = (string)Yii::$app->getRequest()->getUserIP();
        }

        $model = new SmsModel([
            'phone' => $phone,
            'text' => $text,
            'provider' => $this->currentServiceName,
            'type' => $type,
            'for_user_id' => $forUserId,
            'must_sent_at' => $mustSentAt,
            'fuser_id' => $fUserId,
            'ip' => $ip,
        ]);

        if ($model->validate()) {

            try {
                $result = $this->currentService->sendSms($phone, $text, $mustSentAt, $options);

            } catch (Exception $exception) {

                \Yii::error('Ошибка отправки смс ' . $exception->getMessage());

                return false;
            }

            if ($saveInfo) {
                $model->status = $result['status'];
                $model->provider_answer = $result['answer'];
                if (isset($result['id'])) {
                    $model->provider_sms_id = $result['id'];
                }
                $model->save();
            }

            unset($this->availableServices[$this->currentServiceName]);

            while ($this->cascade && $result['status'] === SmsModel::STATUS_FAILED && count($this->availableServices)) {
                // retry send sms with new Service
                $currentServiceName = array_keys($this->availableServices)[0];
                $this->useService($currentServiceName);
                $result['status'] = $this->sendSms($phone, $text, $saveInfo, $type, $forUserId, $mustSentAt, $options);
            }

            return $result['status'];
        } else {
            var_dump($model->getAttributes());
            var_dump($model->getErrors());
        }

        return false;
    }

    /**
     * @param $phone
     * @param $text
     * @param $fUserId
     * @param array $options
     * @return bool
     */
    public function sendSmsFromFuser($phone, $text, $fUserId = null, $options = [], $type = null)
    {
        $fUserId = ($fUserId) ?: \Yii::$app->shop->shopFuser->id;

        return $this->sendSms($phone, $text, true, $type, null, null, $options, $fUserId);
    }

    /**
     * Можно ли отправлять смс
     * @param $phone
     * @param null $fUserId
     * @return bool
     */
    public function canRequest($phone, $fUserId = null)
    {
        $phone = $this->preparePhone($phone);

        $fUserId = ($fUserId) ?: \Yii::$app->shop->shopFuser->id;

//        isset(Yii::$app->getRequest()) ?? Yii::$app->getRequest()->getUserIP() : ''

        $sms = SmsModel::find()
            ->andWhere('phone = :phone', [':phone' => $phone])
//            ->andWhere('fuser_id = :fuser_id', [':fuser_id' => $fUserId])
            ->andWhere('provider = :provider', [':provider' => $this->currentServiceName])
//            ->andWhere('ip = :ip', [':ip' => ])
            ->andWhere('created_at >= :created_at', [
                ':created_at' => date('Y-m-d H:i:s', (time() - self::MAX_TIME_LIMIT_REQUEST)),
            ])
            ->limit(1)
            ->one();

        return !$sms;
    }

    /**
     * Привести телефон в формат сервиса
     * @param $phone
     * @return mixed
     */
    protected function preparePhone($phone)
    {
        return Strings::onlyInt($phone);
    }

}