<?php


namespace common\helpers;


use common\models\ProductParam;
use common\models\ProductParamType;
use common\models\SizeProfileParams;
use common\models\SizeProfile as SizeProfileModel;
use yii\db\Exception;

class SizeProfile
{
    public static $sizeProfileEnabled = true;

    public static $paramAdditionalName = 'size_profile_timestamp';

    public static $paramName = 'size_profile_id';

    public static $sizeCodes = [
        'KFSS_ETALON___ODEJDA',
        'KFSS_RAZMER_OBUVI',
        'KFSS_RAZMER_KOLTSA'
    ];

    public static $sizes = [
        'male' => [
            'KFSS_ETALON___ODEJDA',
            'KFSS_RAZMER_OBUVI',
            'KFSS_RAZMER_KOLTSA'
        ],
        'female' => [
            'KFSS_ETALON___ODEJDA',
            'KFSS_RAZMER_OBUVI',
            'KFSS_RAZMER_KOLTSA'
        ],
//        'male_child' => [
//            'KFSS_ETALON___ODEJDA',
//            'KFSS_RAZMER_OBUVI',
//            'KFSS_RAZMER_KOLTSA'
//        ],
//        'female_child' => [
//            'KFSS_ETALON___ODEJDA',
//            'KFSS_RAZMER_OBUVI',
//            'KFSS_RAZMER_KOLTSA'
//        ]
    ];

    public static function getProfileSizes($sizeProfileId = null)
    {
        $return = [];
        foreach (self::$sizes as $type => $sizes) {
            foreach ($sizes as $name) {
                $data = ProductParam::find()
                    ->leftJoin(ProductParamType::tableName(), ProductParamType::tableName() . '.id=' . ProductParam::tableName() . '.type_id')
                    ->andWhere(['code' => $name])
                    ->andWhere(['>', 'count_can_sale', 0])
                    ->orderBy('name')
                    ->asArray()
                    ->all();
                $data_new = [];
                foreach ($data as $part) {
                    $part_new = null;
                    if ($name == 'KFSS_ETALON___ODEJDA') {
                        if ($part['name'] >= 40 && $part['name'] <= 80) {
                            $part_new = $part;
                        }
                    } else {
                        $part_new = $part;
                    }
                    if ($part_new) {
                        if (!$sizeProfileId) {
                            $part_new['check'] = false;
                        } else {
                            $model = SizeProfileParams::find()
                                ->andWhere(['param_id' => $part_new['id']])
                                ->andWhere(['size_profile_id' => $sizeProfileId])
                                ->andWhere(['type' => $type])
                                ->one();
                            if ($model) {
                                $part_new['check'] = true;
                            } else {
                                $part_new['check'] = false;
                            }
                        }
                        $data_new[] = $part_new;
                    }
                }
                $return[$type][$name] = $data_new;
            }
        }

        return $return;
    }

    public static function deleteSizeProfileParams($sizeProfileId)
    {
        SizeProfileParams::deleteAll(['size_profile_id' => $sizeProfileId]);
    }

    public static function insertSizeprofile($params = [])
    {
        $user = \Yii::$app->user->identity;

        $model = new SizeProfileModel();
        $model->name = $params['name'] ?? SizeProfileModel::$metaTitleDefault;
        $model->type = $params['type'] ?? SizeProfileModel::$typeDefault;
        $model->session_id = session_id();

        if ($user) {
            $model->user_id = $user->id;
        }

        try {
            $model->save();
            return $model->id;
        } catch (Exception $exception) {
            return null;
        }
    }

    public static function updatetSizeProfile($model, $params)
    {
        $model->name = $params['name'] ?? SizeProfileModel::$metaTitleDefault;
        $model->type = $params['type'] ?? SizeProfileModel::$typeDefault;
        try {
            $model->save();
            return $model->id;
        } catch (Exception $exception) {
            return null;
        }
    }

    public static function addSizeProfileParams($sizeProfileId, $paramId, $type)
    {
        $model = SizeProfileParams::find()
            ->andWhere(['param_id' => $paramId])
            ->andWhere(['size_profile_id' => $sizeProfileId])
            ->andWhere(['type' => $type])
            ->one();
        if (!$model) {
            $model = new SizeProfileParams();
            $model->param_id = $paramId;
            $model->size_profile_id = $sizeProfileId;
            $model->type = $type;
            $model->save();
        }
    }

    public static function getSizeProfile()
    {
        $user = \Yii::$app->user->identity;
        $model = null;

        //если есть авторизованный юзер. ищем есть ли размерный профиль, привязанный к юзеру,
        // если есть то возвращаем его
        if ($user) {
            $model = SizeProfileModel::find()
                ->andWhere(['user_id' => $user->id])
                ->one();
            if ($model) {
                return $model;
            }
        }

        //ищем есть размерный профиль, сохраненный в куках
        $sizeProfileId = \Yii::$app->request->cookies->getValue(self::$paramName);
        if ($sizeProfileId) {

            $model = SizeProfileModel::findOne($sizeProfileId);

            //если есть размерный профиль в куках и есть авторизованый юзер, но профиль к юзеру
            // не привязан, то привязываем
            if ($model && $user && ($model->user_id != $user->id)) {
                $model->user_id = $user->id;
                $model->save();
            }
        }

        return $model;
    }

    public static function getOthersParamTypes()
    {
        $return = [];
        $data = ProductParamType::find()
            ->select(['id'])
            ->andWhere(['not in', 'code', self::$sizeCodes])
            ->asArray()
            ->all();

        foreach ($data as $row) {
            $return[] = $row['id'];
        }

        return $return;

    }
}