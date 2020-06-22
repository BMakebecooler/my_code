<?php

namespace common\components\queue\handler\kfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;

use common\models\Brand as BrandModel;
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
        $guid = $this->data->Guid;
        $brandGuid = $this->data->BrandGuid ?? null;
        $seasonGuid = $this->data->SeasonGuid ?? null;

        $product = parent::getProductByGuid($guid);

        if($brandGuid){
            $brand = BrandModel::find()->byNewGuid($brandGuid)->one();
            if(!$brand){
                throw new Exception('Error find brand guid ' .$brandGuid);
            }
            $product->new_brand_id = $brand->id;
        }

        if ($seasonGuid) {
            $season = Season::find()->byNewGuid($seasonGuid)->one();
            if(!$season){
                throw new Exception('Error find season guid ' .$seasonGuid);
            }
            $product->new_season_id = $season->id;
        }

        if (!$product->save()) {
            throw new Exception('Error save new properties product errors' . print_r($product->errors, true));
        }

        return true;

    }
}