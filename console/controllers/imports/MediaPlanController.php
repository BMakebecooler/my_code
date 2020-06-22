<?php

/**
 * php ./yii imports/media-plan/all
 * php ./yii imports/media-plan/day-lots
 * php ./yii imports/media-plan/air-blocks + (Установлены на крон)
 * php ./yii imports/media-plan/air-day-lots-time + (Установлены на крон)
 */

namespace console\controllers\imports;

use common\helpers\Msg;
use modules\shopandshow\models\mediaplan\AirBlock;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use yii\base\Exception;
use yii\helpers\Console;


/**
 * Class MediaPlanController
 *
 * @package console\controllers
 */
class MediaPlanController extends \yii\console\Controller
{

    public function init()
    {
        parent::init();

        set_time_limit(0);
    }

    public function actionAll()
    {

        try {

            $this->actionAirDayLotsTime();

            $this->stdout('done', Console::FG_GREEN);

        } catch (Exception $e) {
            \Yii::error('ss Импорт медиаплана не прошел! ' . $e->getMessage());

            $this->stdout('error', Console::FG_RED);
        }
    }

    /**
     * Список лотов которые шли или будут идти в указанный день
     */
    public function actionAirDayLotsTime()
    {
        $dates = [
            'today' => new \DateTime(),
            'yesterday' => new \DateTime('yesterday'),
            'before yesterday' => new \DateTime('-2 day'),
        ];

        foreach ($dates as $date) {

            $date = $date->format('Y-m-d');

            $attempt = 0;
            do {
                try {
                    $response = \Yii::$app->mediaPlanApi->airBlocks($date);
                } catch (\yii\httpclient\Exception $e) {
                    $attempt++;
                    echo $e->getMessage().PHP_EOL;
                    echo "[{$attempt}] sleeping 10 sec...".PHP_EOL;
                    sleep(10);
                } catch (\Exception $e) {
                    var_dump($e->getMessage());
                    throw $e;
                }
            }
            while(empty($response) && $attempt < 10);

            if ($response->isOk) {
                $blocks = $response->data;
                $counter = 0;
                $count = count($blocks);

                if ($blocks) {
                    $this->stdout("Импорт блоков прямых эфиров за {$date} начат!\n", Console::FG_GREEN);

                    $currentBlocks = AirBlock::find()
                        ->andWhere(['>=', 'begin_datetime', strtotime($date)])
                        ->andWhere(['<', 'begin_datetime', strtotime("{$date} 23:59:59")])
                        ->select('block_id')
                        ->column();

                    $blocksToRemove = array_diff($currentBlocks, array_column($blocks, 'blockId'));
                    if ($blocksToRemove) {
                        $this->stdout("Удаляем старые блоки\n", Console::FG_YELLOW);
                        AirBlock::removeBlocks($blocksToRemove);
                    }

                    Console::startProgress(0, $count);

                    foreach ($blocks as $block) {
                        $counter++;

                        AirBlock::addBlock($block);

                        $this->actionArBlocksLot($block['blockId']);

                        Console::updateProgress($counter, $count);

                        sleep(1); // Иначе АПИ падает
                    }

                    Console::endProgress();
                } else {
                    $this->stdout("\tAirDayLotsTime за {$date} - нет данных! \n", Console::FG_RED);
                }
            } else {
                $this->stdout("\tAirDayLotsTime за {$date} - не прошел! \n", Console::FG_RED);
                $this->stdout(print_r($response->errorMessage, true));
                $this->stdout(print_r($response->httpClientRequest->getUrl(), true));
                $this->stdout(print_r($response->httpClientResponse->getHeaders(), true));
            }
        }
    }

    /**
     * http://mp2.shopandshow.ru/api//v1/airBlockLots?blockId=19651&extended=true - ex api
     * Импорт лотов по блоку
     * @param $blockId
     */
    public function actionArBlocksLot($blockId)
    {
        AirDayProductTime::deleteAll(['block_id' => $blockId]);

        $attempt = 0;
        do {
            try {
                $responseBlockLots = \Yii::$app->mediaPlanApi->airBlocksLots($blockId);
            } catch (\yii\httpclient\Exception $e) {
                $attempt++;
                echo $e->getMessage().PHP_EOL;
                echo "[{$attempt}] sleeping 10 sec...".PHP_EOL;
                sleep(10);
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                throw $e;
            }
        }
        while(empty($responseBlockLots) && $attempt < 10);

        if ($responseBlockLots->isOk) {

            $lots = $responseBlockLots->data;
            $counter = 0;
            $count = count($lots);

            if ($lots) {

                $this->stdout("Импорт лотов в блоке {$blockId} начат!\n", Console::FG_GREEN);

                Console::startProgress(0, $count);

                foreach ($lots as $lot) {

                    $counter++;
                    $result = AirDayProductTime::addProductByBlock($blockId, $lot);

                    if ($result) {
                    }

                    Console::updateProgress($counter, $count);
                }

                Console::endProgress();
            } else {
                $this->stdout("\tactionArBlocksLot в блоке {$blockId} - нет данных! \n", Console::FG_RED);
            }
        } else {
            $this->stdout("\tactionArBlocksLot в блоке {$blockId} - не прошел! \n", Console::FG_RED);
        }
    }
}