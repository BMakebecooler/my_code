<?php

namespace modules\shopandshow\components\front;


use modules\shopandshow\components\api\FrontApi;
use skeeks\cms\base\Component;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 01.06.17
 * Time: 20:34
 */
class Front extends Component
{

    /**
     * @param $loginPass
     * @return array|boolean
     */
    public function getBitrixUserApiAuth($loginPass)
    {
        $url = 'https://old.shopandshow.ru/tools/new_site_auth.php';

        $frontApi = new FrontApi();
        $frontApi->setBaseUrl($url);

        $response = $frontApi->send(null, [
            'security_code' => 'LE48SddQtPkoWsSaraF810eI22D1a60X',
            'data' => $loginPass
        ], 'POST');

        if ($response->isOk) {

            $data = $response->data;

            if (isset($data['error'])) {
                $errorTxt = Json::encode($data);
                \Yii::warning($errorTxt, 'bitrixUserApiAuth');
                return false;
            }

            return [
                'user_id' => isset($data['user_id']) ? (int)$data['user_id'] : null,
                'auth' => isset($data['auth']) ? (bool)$data['auth'] : null,
            ];
        }

        return false;
    }


}