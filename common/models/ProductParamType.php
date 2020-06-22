<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-27
 * Time: 17:24
 */

namespace common\models;


use yii\db\Exception;

class ProductParamType extends \common\models\generated\models\ProductParamType
{


    public static function createFromCmsContent($cmsContent)
    {


        $guid = $cmsContent->ssGuids->guid;
        $model = ProductParamType::find()->andWhere(['guid' => $guid])->one();
        if (empty($model)) {
            $model = new self();
            $model->guid = $guid;
            $model->name = $cmsContent->name;
            $model->code = $cmsContent->code;
            if (!$model->save()) {
                echo 'Error save productParamType ' . $guid.PHP_EOL;
//                throw  new Exception('Error save productParamType ' . $guid);
            }
        }
        return $model;

    }
}