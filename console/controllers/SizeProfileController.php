<?php
/**
 * php yii size-profile/build-tree
 * php yii size-profile/sync-with-analytics
 *
 */


namespace console\controllers;

use common\helpers\Strings;
use common\models\BUFEcommClientSize;
use common\models\generated\models\ProductParam;
use common\models\ProductParamType;
use common\models\SizeProfile;
use common\models\SizeProfile as SizeProfileModel;
use yii\base\Exception;
use yii\console\Controller;
use common\helpers\SizeProfile as SizeProfileHelper;

class SizeProfileController extends Controller
{
    public function actionBuildTree($id)
    {
        $model = SizeProfile::findOne($id);
        if($model){
            $tree_ids = $model->buildTreeIds();
            $model->tree_ids = serialize($tree_ids);
            $model->save();
        }
    }


    protected function insertSizeProfilParams($model,$sizes,$type)
    {
        foreach ($sizes as $size) {
            $size = Strings::clearParamName($size);
            $sizeModel = ProductParam::find()
                ->leftJoin(ProductParamType::tableName(),ProductParamType::tableName().'.id = '.ProductParam::tableName().'.type_id')
                ->andWhere([ProductParam::tableName().'.name' => $size])
                ->andWhere([ProductParamType::tableName().'.code' => $type])
                ->one();
            if($sizeModel){
                SizeProfileHelper::addSizeProfileParams($model->id, $sizeModel->id, $model->type);
            }
        }
    }

    public function actionSyncWithAnalytics()
    {
        $result = BUFEcommClientSize::find();
        foreach ($result->each() as $row){

            $model = SizeProfile::find()
                ->andWhere(['user_id' => $row->CLIENT_ID])
                ->one();

            if(!$model) {
                $model = new SizeProfile();
                $model->user_id = $row->CLIENT_ID;
                $model->name = SizeProfileModel::$metaTitleDefault;
                $model->type = SizeProfileModel::$typeDefault;
                $model->session_id = 'test';
            }

            try {
                $model->save();
            }catch (Exception $e){

            }

            if($model->id) {
                $this->stderr('Добавление размера для размерного профиля :' . $model->id .  PHP_EOL);

                SizeProfileHelper::deleteSizeProfileParams($model->id);

                $top = $row->xTop ? explode('-', $row->xTop) : [];
                $bottom = $row->xBottom ? explode('-', $row->xBottom) : [];
                $compl = $row->xCompl ? explode('-', $row->xCompl) : [];

                $clothSizes = array_unique(array_merge($top, $bottom, $compl));
                $this->insertSizeProfilParams($model,$clothSizes,'KFSS_ETALON___ODEJDA');

                $shoesSizes = $row->xShoe ? explode('-',$row->xShoe) : [];
                $this->insertSizeProfilParams($model,$shoesSizes,'KFSS_RAZMER_OBUVI');

                $ringSizes = $row->xRing ? explode('-',$row->xRing) : [];
                $this->insertSizeProfilParams($model, $ringSizes,'KFSS_RAZMER_KOLTSA');

            }
        }
    }
}