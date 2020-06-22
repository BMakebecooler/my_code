<?php

namespace common\components\queue\handler\protocolkfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;
use common\models\Brand;
use common\models\Season;
use yii\db\Exception;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 17:30
 */
class MainProp extends AbstractHandler implements HandlerInterface
{

    public function execute()
    {
        $guid = $this->data['Guid'];
        $brandGuid = $this->data['BrandGuid'] ?? null;
        $seasonGuid = $this->data['SeasonGuid'] ?? null;

//        \common\helpers\Common::startTimer('getProductByGuid');
        $product = parent::getProductByGuid($guid);
//        echo \common\helpers\Common::getTimerTime('getProductByGuid');

        if ($brandGuid) {
//            \common\helpers\Common::startTimer('getProductBrand');
            $brand = Brand::find()->byGuid($brandGuid)->andWhere(['content_id' => 193])->one();
            if (!$brand) {
                throw new Exception('Error find brand guid ' . $brandGuid);
            }
            $product->new_brand_id = $brand->id;
//            echo \common\helpers\Common::getTimerTime('getProductBrand');
        }

        if ($seasonGuid) {
//            \common\helpers\Common::startTimer('getProductSeason');
            $season = Season::find()->byGuid($seasonGuid)->andWhere(['content_id' => 194])->one();
            if (!$season) {
                throw new Exception('Error find season guid ' . $seasonGuid);
            }
            $product->new_season_id = $season->id;
//            echo \common\helpers\Common::getTimerTime('getProductSeason');
        }

//        \common\helpers\Common::startTimer('saveProduct');
        if (!$product->save(false)) {
            throw new Exception('Error save new properties product errors' . print_r($product->errors, true));
        }
//        echo \common\helpers\Common::getTimerTime('saveProduct');

        return true;

    }
}