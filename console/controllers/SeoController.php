<?php

namespace console\controllers;

use common\models\CmsContentElement;
use common\models\Product;
use common\seo\YmlCatalogFactory;
use Yii;
use common\models\CmsTree;
use yii\console\Controller;
use yii\db\ActiveRecord;

class SeoController extends Controller
{
    public function actionUpdateAll()
    {
        /** @var CmsTree $cmsTree */
        foreach (CmsTree::find()->each() as $cmsTree) {
            $cmsTree->trigger(ActiveRecord::EVENT_AFTER_INSERT);
        }
        /** @var CmsContentElement $content */
        foreach (CmsContentElement::find()->each() as $content) {
            $content->forceUpdateSeoFields = true;
            $content->trigger(ActiveRecord::EVENT_AFTER_INSERT);
        }
    }

    public function actionContentUpdateOne($id)
    {
        $model = CmsContentElement::findOne((int)$id);
        /** @var CmsContentElement $content */
        if ($model) {
            $model->forceUpdateSeoFields = true;
            $model->save();
        }
    }

    public function actionLotUpdateAll()
    {
        $query = CmsContentElement::find()
            ->where([
                'IN',
                'content_id',
                [CARD_CONTENT_ID, OFFERS_CONTENT_ID, PRODUCT_CONTENT_ID]
            ]);

        foreach ($query->each() as $model) {
            $model->forceUpdateSeoFields = true;
            $model->save();
        }
    }
    public function actionLotUpdateOne($id)
    {
        $model = Product::findOne((int)$id);
        if ($model) {
            $model->forceUpdateSeoFields = true;
            $model->save();
        }
    }


    public function actionTreeUpdateAll()
    {
        foreach (CmsTree::find()->each() as $model) {
            $model->forceUpdateSeoFields = true;
            $model->save();
        }
    }

    public function actionTreeUpdateOne($id)
    {
        $model = CmsTree::findOne((int)$id);
        /** @var CmsTree $content */
        if ($model) {
            $model->forceUpdateSeoFields = true;
            $model->save();
        }
    }

    public function actionMakeTurpoPageYml()
    {
        try {
            $dir = Yii::getAlias('@frontend/web/export');

            if (!is_dir($dir)) {
                mkdir($dir, '0777');
            }
            YmlCatalogFactory::create()
                ->make()
                ->save(Yii::getAlias("$dir/yandex-turbo_page.xml"));
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }
}