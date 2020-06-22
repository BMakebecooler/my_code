<?php
namespace modules\shopandshow\models\shop\forms;

use common\helpers\User;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use yii\base\Model;
use yii\helpers\Json;

class FinishOrder extends Model
{
    public $name;
    public $email;
    public $address;
    public $kladr_id;
    public $description;
    public $geoobject;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'email'],
            [['name', 'address', 'kladr_id', 'description', 'geoobject'], 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'email' => 'Электронная почта',
            'address' => 'Адрес',
            'kladr_id' => 'Кладр',
            'description' => 'Комментарии, пожелания',
            'geoobject' => 'geoobject'
        ];
    }

    /**
     * редактирование заказа
     * @param ShopOrder $model
     * @return array|bool
     */
    public function processed(ShopOrder $model)
    {
        $model->user_description = $this->description;
        if ($model->status_code == ShopOrderStatus::STATUS_DELAYED) {
            $model->setStatus(ShopOrderStatus::STATUS_WAIT_PAY);
        }
        $model->save(false, ['user_description', 'status_code']);

        $geoobject = Json::decode($this->geoobject);

        $params = \Yii::$app->shop->shopFuser->getProfileParams();

        /**
         * https://dadata.ru/api/suggest/#how-granular-address
         */
        $params['Region'] = $geoobject['region_with_type'];
        $params['District'] = $geoobject['area_with_type'] ?: $geoobject['city_district_with_type'];
        $params['SettlementType'] = $geoobject['settlement_type_full'] ?: $geoobject['city_type_full'];
        $params['SettlementName'] = $geoobject['settlement'] ?: $geoobject['city'];
        $params['StreetName'] = join('. ', array_filter([$geoobject['street_type'], $geoobject['street']]));
        $params['StreetNumber'] = join('. ', array_filter([$geoobject['house_type'], $geoobject['house']]));
        $params['BuildNumber'] = join('. ', array_filter([$geoobject['block_type'], $geoobject['block']]));
        $params['DoorNumber'] = join('. ', array_filter([$geoobject['flat_type'], $geoobject['flat']]));

        $params['kladr_id'] = $geoobject['kladr_id'];
        $params['address'] = join(', ', [$params['StreetName'], $params['StreetNumber'], $params['BuildNumber'], $params['DoorNumber']]);

        $params['FiasCodeProvince'] = $geoobject['region_kladr_id'] ?: $geoobject['region_kladr_id'];
        $params['FiasCodeDistrict'] = $geoobject['city_district_kladr_id'] ?: $geoobject['city_district_kladr_id'];
        $params['FiasCodeCity'] = $geoobject['city_kladr_id'] ?: $geoobject['city_kladr_id'];
        $params['FiasCodeStreet'] = $geoobject['street_kladr_id'] ?: $geoobject['street_kladr_id'];
        $params['FiasCodeBuilding'] = $geoobject['house_kladr_id'] ?: $geoobject['house_kladr_id'];

        if ($user = $model->user) {
            $user->name = $this->name;
            $user->email = $this->email;

            if (!$user->save(true, ['name', 'email'])) {
                return false;
            }
        }

        return \Yii::$app->shop->shopFuser->saveProfileParams($params);
    }


    /**
     * Человек зашел на страницу редактирования заказа
     * @param ShopOrder $model
     * @return bool
     */
    public function prepare(ShopOrder $model)
    {
        if ($model->status_code != ShopOrderStatus::STATUS_WAIT_PAY && $model->status_code != ShopOrderStatus::STATUS_DELAYED) {
            return false;
        }

        $model->setStatus(ShopOrderStatus::STATUS_DELAYED);
        $model->save(false, ['status_code']);

        $profile = \Yii::$app->shop->shopFuser->getProfileParams();
        if ($user = User::getUser()) {
            $this->name = $user->name;
            $this->email = $user->email;
        }
        $this->address = $profile['address'];
        $this->kladr_id = $profile['kladr_id'];
        $this->description = $model->user_description;

        return true;
    }
}
