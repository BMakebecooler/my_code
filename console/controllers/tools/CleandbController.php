<?php

/**
 * php ./yii tools/cleandb/clear-expired-session
 * php ./yii tools/cleandb/clear-expired-fuser
 * php ./yii tools/cleandb/clear-empty-properties
 */

namespace console\controllers\tools;

use yii\helpers\Console;


/**
 * Class CleandbController
 * @package console\controllers
 */
class CleandbController extends \yii\console\Controller
{

    protected $expired;
    protected $deleteBy = 10000;

    /**
     * Удаление устаревших сессий
     * @return bool
     */
    public function actionClearExpiredSession()
    {

        $this->expired = time() - (DAYS_30 * 3); //Порог - старше 3 месяцев

        //Удаляем сессии у которых expired больше порога
        $sqlSession = <<<SQL
DELETE FROM session WHERE expire < :expire LIMIT {$this->deleteBy}
SQL;
        $deleteSessionsCommand = \Yii::$app->db->createCommand($sqlSession)->bindValue(':expire', $this->expired);

        $this->stdout("Начало очистки устаревших сессий.\n", Console::FG_GREEN);
        $i = 0;
        do {
            $i++;
            if ($affected = $deleteSessionsCommand->execute()) {
                $this->stdout("[{$i}] Удалено " . (int)$affected . " записей\n", Console::FG_GREEN);
                usleep(148);
            }
        } while ($affected > 0);

        $this->stdout("Блок сессий очищен.\n", Console::FG_GREEN);

        return true;
    }


    /**
     * Удаление устаревших фузеров без элементов корзин
     * @return bool
     */
    public function actionClearExpiredFuser()
    {
        //DELETE с LIMIT при таком вариате не работает
        return false;

        $this->expired = time() - (DAYS_30 * 3); //Порог - старше 3 месяцев

        //Так же чистим фузеров у которы нет юзера и к которым не привязаны ШопБаскеты
        $sqlFuser = <<<SQL
DELETE fuser
FROM shop_fuser AS fuser
LEFT JOIN shop_basket AS basket ON basket.fuser_id=fuser.id
WHERE fuser.created_at < :created_at
    AND fuser.user_id IS NULL
    AND basket.id IS NULL
LIMIT {$this->deleteBy}
SQL;

        $deleteFuserCommand = \Yii::$app->db->createCommand($sqlFuser)->bindValue(':created_at', $this->expired);

        $this->stdout("Начало очистки пустых устаревших фузеров.\n", Console::FG_GREEN);
        for ($i = 1; $i <= 100; $i++) {
            if ($affected = $deleteFuserCommand->execute()) {
                $this->stdout("[{$i}] Удалено " . (int)$affected . " записей\n", Console::FG_GREEN);
                usleep(148);
            }
        }
        $this->stdout("Блок фузеров очищен.\n", Console::FG_GREEN);

        return true;
    }

    /**
     * Удаление пустых свойств
     * @return bool
     */
    public function actionClearEmptyProperties()
    {
        $sqlEmptyProperty = <<<SQL
DELETE FROM cms_content_element_property WHERE value = '' AND property_id <> 83 LIMIT {$this->deleteBy}
SQL;
        $deleteEmptyPropertiesCommand = \Yii::$app->db->createCommand($sqlEmptyProperty);

        $this->stdout("Начало очистки пустых свойств.\n", Console::FG_GREEN);
        $i = 0;
        do {
            $i++;
            if ($affected = $deleteEmptyPropertiesCommand->execute()) {
                $this->stdout("[{$i}] Удалено " . (int)$affected . " записей\n", Console::FG_GREEN);
                usleep(148);
            }
        } while ($affected > 0);

        $this->stdout("Пустые свойства удалены.\n", Console::FG_GREEN);

        return true;
    }

    /**
     *  Очистка от типов цен которые не назначены ни на один товар
     *
     * @return bool
     */
    public function actionCleanShopTypePrice()
    {
        $sql = "
DELETE FROM shop_type_price WHERE id IN (
    SELECT t.id FROM
  (
    SELECT
      price_types.id
    FROM
      shop_type_price AS price_types
    LEFT JOIN shop_product_price AS prices
      ON price_types.id = prices.type_price_id
    WHERE
      price_types.id > 17 -- Что бы даже теоритически не затрагивались наши основные типы цен
      AND prices.id IS NULL
    GROUP BY price_types.id
  ) AS t
)";
        \Yii::$app->db->createCommand($sql)->execute();

        return true;
    }

}



