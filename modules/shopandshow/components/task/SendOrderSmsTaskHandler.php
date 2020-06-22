<?php

namespace modules\shopandshow\components\task;
use common\components\rbac\CmsManager;
use common\helpers\User;
use common\models\Sms;
use modules\shopandshow\models\shop\ShopFuser;


/**
 * Class SendOrderSmsTaskHandler
 */
class SendOrderSmsTaskHandler extends BaseTaskHandler
{
    /**
     * @var string - телефон клиента кому отправляем смс
     */
    public $phone;

    /**
     * @var string - текст сообщения
     */
    public $text;

    /**
     * @var int
     */
    public $fuser_id;

    /**
     * @return bool
     */
    public function handle()
    {
        return $this->sendSms();
    }

    /**
     * Отправка sms
     * @return
     */
    private function sendSms()
    {
        //* Проверяем, нужно ли отправлять смс текущему пользователю *//

        $fuser = ShopFuser::findOne($this->fuser_id);

        if ($fuser){
            /** @var \common\models\user\User $user */
            $user = $fuser->getUser()->one();

            if (User::hasRole($user->id, CmsManager::ROLE_DEMO)){
                echo "Skip send SMS for num '{$this->phone}' [{$this->fuser_id}]" . PHP_EOL;
                return true;
            }
        }

        //* /Проверяем, нужно ли отправлять смс текущему пользователю *//

        $sendSmsRes = \Yii::$app->sms->sendSms(
            $this->phone,
            $this->text
        );

        return $sendSmsRes;
    }

}