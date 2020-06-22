<?php

namespace console\controllers\mq;

use common\models\user\User;
use yii\base\Exception;
use yii\helpers\Inflector;

/**
 * Class UsersController
 *
 * @package console\controllers\mq
 */
class UsersController extends ListenerController
{

    public $queueName = 'front.users';
    public $routingKey = 'front.user.update';

    private function createUser()
    {
    }

    public function updateUser()
    {
        $model = $this->getModelGuid($this->data->guid);

        if ($model == null) {
            $this->log("User with guid {$this->data->guid} not found");
            //throw new Exception("Order with ID {$this->data->external_order_id} not found");
            return true;
        }

        $model->bitrix_id = isset($this->data->bitrix_id) ? $this->data->bitrix_id : '';

        if (!$model->validate(['bitrix_id'])) {
            return false;
            throw new Exception("User model data not valid: " . json_encode($model->getErrors()));
        }

        if (!$model->save(false, ['bitrix_id'])) {
            return false;

            throw new Exception("User model data not valid: " . json_encode($model->getErrors()));
        }

        return true;
    }

    private function deleteUser()
    {
    }

    /**
     * @param $id
     * @return User
     */
    private function getModel($id)
    {
        return User::findOne($id);
    }

    /**
     * @param $guid
     * @return User|null|\yii\db\ActiveRecord
     */
    private function getModelGuid($guid)
    {
        $user = User::find();
        $user->with(['guid']);
        $user->andWhere('ss_guid.guid = :guid', [':guid' => $guid]);

        return $user->one();
    }

}