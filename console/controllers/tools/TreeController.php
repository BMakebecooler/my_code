<?php

/**
 * php ./yii tools/tree/export-with-guids
 * php ./yii tools/tree/corrected-h1 console/controllers/tools/files/h1_catalog.csv
 * php yii tools/tree/rotate-catalog-tree
 *
 */
namespace console\controllers\tools;

use common\models\CmsTree;
use common\models\Tree;
use console\controllers\export\ExportController;
use modules\shopandshow\models\common\Guid;
use yii\helpers\Console;

/**
 * Class TreeController
 * @package console\controllers
 */
class TreeController extends ExportController
{

    /**
     * Экпортирует в файл список разделов с их ГУИДами
     * @return bool
     */
    public function actionExportWithGuids()
    {
        $this->stdout("Экпорт разделов с их ГУИДами" . PHP_EOL, Console::FG_GREEN);

        $trees = Tree::find()
            ->alias('tree')
            ->select('tree.name, tree.code, tree.dir, guid.guid')
            ->where(['not', ['tree.guid_id' => null]])
            ->innerJoin(Guid::tableName() . ' AS guid', "tree.guid_id=guid.id")
            ->all();


        if ($trees) {
            $count = count($trees);
            $counterStep = $count / 10; //каждый 1 процента, сколько это в штуках

            $this->stdout("Найдено разделов = {$count}" . PHP_EOL, Console::FG_GREEN);

            //Экспортируем в файл
            $dir = \Yii::getAlias('@frontend/web/export/');
            $filename = "trees_with_guids.csv";
            $fullPath = $dir . $filename;

            $file = fopen($fullPath, 'wb');

            $this->stdout("Экспортирую данные в файл '{$fullPath}'" . PHP_EOL, Console::FG_CYAN);

            if (!$file) {
                $this->stdout("Ошибка при создании файла '{$fullPath}'" . PHP_EOL, Console::FG_RED);
            } else {
                fputcsv($file, ['GUID', 'URL', 'NAME']);

                if ($trees) {
                    $counterGlobal = 0;
                    $counter = 0;
                    Console::startProgress(0, $count);
                    /** @var Tree $tree */
                    foreach ($trees as $tree) {
                        $counterGlobal++;
                        $counter++;

                        if ($counter >= $counterStep || $counterGlobal == $count) {
                            $counter = 0;
                            Console::updateProgress($counterGlobal, $count);
                        }
                        fputcsv($file, [$tree->guid, $tree->getUrl(), $tree->name]);
                    }
                }

                fclose($file);
            }
        } else {
            $this->stdout("Разделов не найдено" . PHP_EOL, Console::FG_CYAN);
        }

        $this->stdout("Готово" . PHP_EOL, Console::FG_GREEN);

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    public function actionCorrectedH1($file)
    {
        if (!file_exists($file)) {
            $this->stdout("Файл '$file' не найден\n", Console::FG_RED);

            return false;
        }

        $rows = file($file);

        foreach ($rows as $row) {
            if (empty($row)) {
                continue;
            }

            $items = explode(',', $row);

            if (count($items) !== 2) {
                continue;
            }

            list($url, $h1) = $items;

            $url = trim($url);
            $h1 = trim($h1);

            if (empty($url) || empty($h1)) {
                continue;
            }

            $url = str_replace('https://shopandshow.ru/', '', $url);
            $dir = rtrim($url, '/');

            $tree = Tree::findOne(['dir' => $dir]);

            if (!$tree) {
                $this->stdout("dir  " . $dir . " not found" . "\n", Console::FG_RED);
                continue;
            }

            $this->stdout("Было: " . $tree->name . ", стало: " . $h1 . "\n", Console::FG_GREEN);
            $tree->name = $h1;

            $tree->save(false, ['name']);
        }

        $this->stdout("H1 corrected done " . "\n", Console::FG_GREEN);
    }

    //Меняет классификатор и рубрикатор местами
    public function actionRotateCatalogTree()
    {
        $activeTree = CmsTree::find()->where(['id' => TREE_CATEGORY_ID_CATALOG])->one();
        $alternativeTree = CmsTree::find()->where(['id' => TREE_CATEGORY_ID_RUBRICATOR])->one();
        $tmpTree = CmsTree::find()->where(['code' => 'tree_temp'])->one();

        if ($activeTree && $alternativeTree && $tmpTree){

            Console::stdout("Rotate Catalog Tree" . PHP_EOL);

            //Логика
            //- все подразделы активной ветки перекидываем во времянку
            //- пожразделы альтернативной ветки закитываем в актуал
            //- освобождаем времянку перекидывая в альтернатив

            $affectedStep1 = CmsTree::updateAll(['pid' => $tmpTree->id], ['pid' => $activeTree->id]);

            Console::stdout("Step 1 (Active -> Temp). Affected = {$affectedStep1}" . PHP_EOL);

            if ($affectedStep1){
                $affectedStep2 = CmsTree::updateAll(['pid' => $activeTree->id], ['pid' => $alternativeTree->id]);

                Console::stdout("Step 2 (Alt -> Active). Affected = {$affectedStep2}" . PHP_EOL);

                if ($affectedStep2){
                    $affectedStep3 = CmsTree::updateAll(['pid' => $alternativeTree->id], ['pid' => $tmpTree->id]);

                    Console::stdout("Step 2 (Temp -> Alt). Affected = {$affectedStep3}" . PHP_EOL);
                }
            }

            Console::stdout("DONE" . PHP_EOL);
        }

        return true;
    }

}



