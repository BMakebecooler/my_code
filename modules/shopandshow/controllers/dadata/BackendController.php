<?php

namespace modules\shopandshow\controllers\dadata;

use common\helpers\User as UserHelper;
use skeeks\cms\helpers\RequestResponse;
use yii\web\Controller;

/**
 * Class BackendController
 */
class BackendController extends Controller
{
    /**
     * @return RequestResponse
     */
    public function actionSaveAddress()
    {
        $rr = new RequestResponse();

        \Yii::error("Dadata Save", 'dadata');
        \Yii::error(var_export(\Yii::$app->request->post(), true), 'dadata');

        \Yii::$app->kfssApiV2->unsetCookieLastRecalc();

        $profileParams = [];

        //\Yii::info(var_export(\Yii::$app->request->post(), true), 'dadata');

        if ($geoobject = \Yii::$app->request->post('geoobject')) {

            $profileParams['kladr_id'] = @$geoobject['data']['kladr_id'] ?: '7700000000000';

            //$profileParams['city'] = @$geoobject['data']['city'] ?: $geoobject['data']['settlement'];
            $profileParams['Region'] = @$geoobject['data']['region_with_type'] ?: '';
//            $profileParams['region'] = @$geoobject['data']['region_with_type'] ?: '';
            $profileParams['region_kladr_id'] = @$geoobject['data']['region_kladr_id'] ?: '';
//            $profileParams['city'] = @$geoobject['data']['city_with_type'] ?: $geoobject['data']['settlement_with_type'];

            $profileParams['city'] = @$geoobject['data']['city_with_type'];

            if (!empty(@$geoobject['data']['settlement_with_type'])){
                $profileParams['city'] .= (!empty($profileParams['city']) ? ', ':'') . @$geoobject['data']['settlement_with_type'];
            }

            //$profileParams['StreetName'] = @$geoobject['data']['street']; //Название улицы
            $profileParams['StreetName'] = @$geoobject['data']['street_with_type']; //Название улицы
            $profileParams['StreetNumber'] = \common\helpers\Order::getDeliveryAddressHouseFull($geoobject['data']); //Номер дома
            $profileParams['BuildNumber'] = @$geoobject['data']['block']; //Номер строения
            $profileParams['DoorNumber'] = @$geoobject['data']['flat'];

            $profileParams['FiasCodeCity'] = @$geoobject['data']['city_kladr_id'];
            $profileParams['FiasCodeStreet'] = @$geoobject['data']['street_kladr_id'];
            $profileParams['FiasCodeBuilding'] = @$geoobject['data']['house_kladr_id'];

            $profileParams['postal_code'] = @$geoobject['data']['postal_code'];
        } else {
            $profile = \Yii::$app->shop->shopFuser->getProfileParams();

            $address = \Yii::$app->dadataSuggest->address;

            $profileParams['city'] = @$profile['city'] ?: @$address->data['city'] ?: @$address->data['settlement'] ?: 'Москва';
            $profileParams['city_kladr_id'] = @$profile['city_kladr_id'] ?: @$address->data['kladr_id'] ?: '7700000000000';
            $profileParams['postal_code'] = @$profile['postal_code'] ?: @$address->data['postal_code'] ?: '101000';
        }

        if (array_filter($profileParams)) {
            $rr->success = true;
            $dadataSessionData = [
                'unrestricted_value' => $profileParams['city'],
                'data' => $geoobject['data']
            ];

            \Yii::$app->dadataSuggest->saveAddress($dadataSessionData);

            \Yii::info("ProfileParams", 'dadata');
            \Yii::info(var_export($profileParams, true), 'dadata');
            if (UserHelper::isAuthorize()) {
                $saveResult = \Yii::$app->shop->shopFuser->saveProfileParams($profileParams);

                if ($saveResult !== true) {
                    $rr->success = false;
                    $rr->message = 'не удалось сохранить адрес в профиле ' . print_r($saveResult, true);
                }
            }
        }

        //Обновляем возможно имеющийся заказ
        \Yii::$app->kfssApiV2->updateOrder();

        return $rr;
    }

    /**
     * @return RequestResponse
     */
    /*public function actionGetAddress()
    {
        $rr = new RequestResponse();

        $rr->data = [
            'geoobject' => \Yii::$app->dadataSuggest->address,
            'unrestrictedValue' => \Yii::$app->dadataSuggest->address->unrestrictedValue,
            'regionString' => \Yii::$app->dadataSuggest->address->regionString,
            'shortAddressString' => \Yii::$app->dadataSuggest->address->shortAddressString,
        ];
        $rr->success = true;

        return $rr;
    }*/
}