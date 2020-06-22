<?php
namespace modules\api\behaviors;

use skeeks\cms\models\User;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 31/01/2019
 * Time: 11:14
 */

class HttpBearerAuth extends \yii\filters\auth\HttpBearerAuth
{


    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('Authorization');
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $identity = User::find()->andWhere(['auth_key' => $matches[1]])->one();
            if ($identity === null) {
                $this->handleFailure($response);
            }
            return $identity;
        }

        return null;
    }
}