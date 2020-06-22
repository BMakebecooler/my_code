<?php

/**
 * php ./yii imports/comments/import
 * php ./yii imports/comments/import-from-files
 */

namespace console\controllers\imports;

use common\lists\Contents;
use skeeks\cms\components\Cms;
use skeeks\cms\reviews2\models\Reviews2Message;
use yii\db\Connection;
use yii\helpers\Console;

/**
 * Class CommentsController
 * @package console\controllers
 */
class CommentsController extends \yii\console\Controller
{

    /**
     * Ид раздела с комментариями в битриксе
     */
    const IBLOCK_BITRIX_COMMENTS = 41;


    /** @var Connection */
    protected $frontDb;

    /** @var  Connection */
    protected $db;

    public function init()
    {
        parent::init();

        $this->frontDb = \Yii::$app->get('front_db');
        $this->db = \Yii::$app->get('db');
    }

    public function actionImport()
    {
        if ($this->import()) {
            $this->stdout("Комменты импортированы!\n", Console::FG_GREEN);
        } else {
            $this->stdout("Комменты не импортированы!\n", Console::FG_RED);
        }

        $this->stdout("Импорт Комментов закончен!\n", Console::FG_YELLOW);
    }

    public function actionImportFromFiles()
    {
        //$files = [__DIR__.'/files/reviews_1_1.csv', __DIR__.'/files/reviews_2_1.csv'];
        //$files = [];
        $files = [__DIR__.'/files/comments_for_import.csv'];

        if ($this->importFromFiles($files)) {
            $this->stdout("Комменты импортированы!\n", Console::FG_GREEN);
        } else {
            $this->stdout("Комменты не импортированы!\n", Console::FG_RED);
        }

        $this->stdout("Импорт Комментов из файлов закончен!\n", Console::FG_YELLOW);
    }

    /**
     * @return bool
     */
    protected function import()
    {

//        Reviews2Message::deleteAll('status = 0');

        $sql = <<<SQL
        SELECT 
            e.id,
            property.property_247 AS product_id,
            property.property_248 AS author_name,
            property.property_249 AS author_email,
            property.property_250 AS text_comment,
            e.ACTIVE AS active
        FROM front2.b_iblock_element AS e
        LEFT JOIN b_iblock_element_prop_s41 AS property ON property.IBLOCK_ELEMENT_ID = e.ID
        WHERE e.iblock_id = 41 AND e.ACTIVE = 'Y' AND property.property_248 IS NOT NULL
        GROUP BY property.property_247, property.property_248, property.property_249
SQL;

        $blockElements = $this->frontDb->createCommand($sql, [
            ':block_id' => self::IBLOCK_BITRIX_COMMENTS,
        ])->queryAll();

        $comments = [];

        foreach ($blockElements as $element) {

            try {
                $textComment = unserialize($element['text_comment']);
            } catch (\Exception $exception) {
                continue;
            }

            if (!isset($textComment['TEXT'])) {
                continue;
            }

            $comments[] = [
                'id' => $element['id'],
                'product_id' => $element['product_id'],
                'author_name' => $element['author_name'],
                'author_email' => $element['author_email'],
                'text_comment' => $textComment['TEXT'],
                'active' => $element['active'],
            ];
        }

        return $this->loadComments($comments);
    }

    /**
     * @return bool
     */
    protected function importFromFiles(array $files)
    {
        $comments = [];

        foreach ($files as $file) {
            $this->stdout("Начинаем сбор данных из файла: $file\n", Console::FG_YELLOW);

            if (!file_exists($file)) {
                $this->stdout("Файл '$file' не найден\n", Console::FG_RED);

                continue;
            }

            $rows = file($file);

            $randomUsers = \common\models\user\User::find()->asArray()->all();

            while (sizeof($randomUsers) < sizeof($rows)) {
                $randomUsers = $randomUsers + $randomUsers;
            }

            shuffle($randomUsers);

            foreach ($rows as $row) {
                if (empty($row)) {
                    continue;
                }

                $items = explode(';', $row);

                // нет второго итема => коммент не указан
                if (count($items) < 2) {
                    continue;
                }

                list($lot, $userName, $comment) = $items;

                $lot = trim($lot);
                $userName = trim($userName);
                $comment = trim(trim($comment), '"');

                // коммент не указан (пустая строка)
                if (empty($lot) || empty($comment) || empty($userName) || strlen($comment) <= 5) {
                    continue;
                }

                if (!preg_match('/^[\d\-\s]+$/', $lot)) {
                    $this->stdout("Некорректный номер лота ($lot) => пропускаем \n", Console::FG_PURPLE);
                    continue;
                }

                $bitrixId = ltrim(str_replace(['[', ']', '-', ' '], '', $lot), '0');

                $randomUser = array_pop($randomUsers);

                $userName = $userName ?: $randomUser['name'] ?: $randomUser['username'];

                $comments[] = [
                    'id' => null,
                    'product_id' => $bitrixId,
                    'author_name' => $userName,
                    'author_email' => null, //$element['author_email'],
                    'text_comment' => $comment,
                    'active' => Cms::BOOL_Y,
                ];
            }
        }

        return $this->loadComments($comments);
    }

    protected function loadComments(array $comments)
    {
        if (!$comments) {
            return false;
        }

        $this->stdout("Начинаем импорт полученных данных в БД \n", Console::FG_YELLOW);
        $count = sizeof($comments);
        $counter = 0;
        Console::startProgress(0, $count);
        foreach ($comments as $comment) {

            $counter++;
            Console::updateProgress($counter, $count);
            $element = Contents::getContentElementByIdOrBitrixId($comment['product_id']);

            if (!$element) {
                continue;
            }

            $newComment = new Reviews2Message();
            $newComment->content_id = PRODUCT_CONTENT_ID;
            $newComment->element_id = $element->id;
            $newComment->comments = $comment['text_comment'];
            $newComment->rating = ($comment['active'] == Cms::BOOL_N) ? rand(2, 4) : 5;
            $newComment->status = ($comment['active'] == Cms::BOOL_N) ? Reviews2Message::STATUS_NEW : Reviews2Message::STATUS_ALLOWED;
            $newComment->user_name = $comment['author_name'];
            $newComment->user_email = $comment['author_email'];

            if (!$newComment->save()) {
                var_dump($newComment->getErrors());
//                die();
            }
            unset($newComment);
        }

        return true;
    }
}