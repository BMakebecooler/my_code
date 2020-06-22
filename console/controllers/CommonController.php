<?php
/**
 * Общий контроллер для того что не удостаивается своего персонального контроллера
 *
 * php yii common/brand-reslug
 * php yii common/sync-brand-popularity-by-tree
 */

namespace console\controllers;


use common\models\Brand;
use common\models\CmsContentElement;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\Inflector;

class CommonController extends Controller
{
    //Перегенерирует code из name
    public function actionBrandReslug()
    {
        $brandsQuery = CmsContentElement::find()
            ->byContent(Brand::CONTENT_ID)
            ->orderBy(['name' => SORT_ASC]);

        $brandsCount = $brandsQuery->count();

        $this->stdout("Брендов всего найдено: {$brandsCount}" . PHP_EOL);
        $this->stdout("Формирую slug..." . PHP_EOL);

        if ($brandsCount) {

            $count = $brandsCount;
            $counterStep = $count / 100; //каждый 1 процента, сколько это в штуках

            $counterGlobal = 0;
            $counter = 0;
            Console::startProgress(0, $count);

            /** @var CmsContentElement $brand */
            foreach ($brandsQuery->each() as $brand) {
                $counterGlobal++;
                $counter++;

                if ($counter >= $counterStep || $counterGlobal == $count) {
                    $counter = 0;
                    Console::updateProgress($counterGlobal, $count);
                }

                if ($brand->name) {
                    $brandSlug = Inflector::slug($brand->name);

                    $brand->code = $brand->guid_id ? $brandSlug : "NOGUID_{$brand->id}_{$brandSlug}";

                    try {
                        if (!$brand->save(false, ['code'])) {
                            $this->stdout("ERROR! Text: " . var_export($brand->getErrors(), true) . PHP_EOL, Console::FG_RED);
                        }
                    } catch (\Exception $e) {
                        $this->stdout("CATCH ERROR! Text: " . $e->getMessage() . PHP_EOL, Console::FG_RED);

                        $brand->code = "DUPLICATE_{$brand->id}_{$brandSlug}";

                        if (!$brand->save(false, ['code'])) {
                            $this->stdout("ERROR! Text: " . var_export($brand->getErrors(), true) . PHP_EOL, Console::FG_RED);
                        }
                    }

                }
            }
        }

        $this->stdout("Done" . PHP_EOL);

        return true;
    }

    public function actionSyncBrandPopularityByTree ()
    {
        return \common\helpers\Brand::syncBrandPopularityByTree();
    }

}