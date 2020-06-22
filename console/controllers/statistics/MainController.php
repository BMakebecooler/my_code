<?php

/**
 * php ./yii statistics/main/products
 * php ./yii statistics/main/insert-log
 * php ./yii statistics/main/rating
 * php ./yii statistics/main/set-count-view
 * php ./yii statistics/main/set-view-product
 *
 * php ./yii statistics/main/banners-stat
 *
 * параметры "ДатаС ДатаПо КолвоВыкупленныхЗаказовМин ПроцентВыкупаМинимальный СчитатьЛиПроцентВыкупа КолвоВыкупленныхМакс"
 * php ./yii statistics/main/get-bitrix-orders-complete-stat 2018-07-01 2018-07-30 1 0.5 1 999999
 */

namespace console\controllers\statistics;

use common\helpers\Msg;
use common\helpers\Strings;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shares\SharesStat;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\statistic\Statistics;
use skeeks\cms\shop\models\ShopViewedProduct;
use Yii;
use yii\helpers\Console;
use console\controllers\export\ExportController;
/**
 * Class MainController
 * @package console\controllers
 */
class MainController extends ExportController
{

    /**
     * Установить просмотры
     */
    public function actionSetCountView()
    {

        $allViewsKeys = Yii::$app->redis->executeCommand('keys', [sprintf('%s*', ShopProduct::PRODUCT_COUNTER_VIEW_KEY_NAME)]);

        $countKeys = count($allViewsKeys);

        $this->stdout("В стеке $countKeys просмотров \n", Console::FG_YELLOW);

        foreach ($allViewsKeys as $key) {

            try {

                $countView = Yii::$app->redis->get($key); //Получаем кол-во просмотров

                $productId = str_replace(ShopProduct::PRODUCT_COUNTER_VIEW_KEY_NAME, '', $key);

                CmsContentElement::updateAllCounters(['show_counter' => $countView], 'id = :id', [':id' => $productId]);

                Yii::$app->redis->del($key);

            } catch (\Exception $e) {

                \Yii::error(sprintf('Пересчет просмотров товара не удался! (%s)', $e->getMessage()));

                break;
            }
        }

        $this->stdout("Установлено $countKeys просмотров \n", Console::FG_GREEN);
    }

    /**
     * Установить просмотры
     */
    public function actionSetViewProduct()
    {

        $allViewsKeys = Yii::$app->redis->executeCommand('keys', [sprintf('%s*', ShopProduct::PRODUCT_VIEWED_KEY_NAME)]);

        $countKeys = count($allViewsKeys);

        $this->stdout("В стеке $countKeys продуктов \n", Console::FG_YELLOW);

        foreach ($allViewsKeys as $key) {

            try {
                $allViewsValues = Yii::$app->redis->executeCommand('hgetall', [$key]);
                Yii::$app->redis->del($key);


                $productId = str_replace(ShopProduct::PRODUCT_VIEWED_KEY_NAME, '', $key);

                $this->stdout("В продукте {$productId} " . (sizeof($allViewsValues) / 2) . " просмотров \n", Console::FG_YELLOW);

                $values = [];
                for ($i = 0; $i < sizeof($allViewsValues); $i += 2) {
                    $value = [
                        'created_at' => $allViewsValues[$i + 1],
                        'shop_fuser_id' => $allViewsValues[$i],
                        'shop_product_id' => $productId,
                        'site_id' => \Yii::$app->cms->site->id
                    ];
                    array_push($values, $value);
                }

                if ($values) {

                    $db = \Yii::$app->db;

                    $sql = $db->queryBuilder->batchInsert(ShopViewedProduct::tableName(), array_keys($values[0]), $values);
                    $sql = 'INSERT IGNORE' . mb_substr($sql, strlen('INSERT'));

                    $db->createCommand($sql)->execute();

//                    \Yii::$app->db->createCommand()->batchInsert(ShopViewedProduct::tableName(), array_keys($values[0]), $values)->execute();
                }

            } catch (\Exception $e) {

                $this->stdout('Пересчет не удался ' . $e->getMessage(), Console::FG_RED);
                \Yii::error(sprintf('Пересчет просмотров продуктов не удался! (%s)', $e->getMessage()));

                return false;
            }
        }

        $this->stdout("Установлены просмотры для $countKeys продуктов \n", Console::FG_GREEN);
    }

    /**
     * Пересчитывает коэффициенты продуктов
     */
    public function actionProducts()
    {
        $query = <<<SQL
        
        SET @min_viewed := 0, @max_viewed := 0, 
            @min_ordered := 0, @max_ordered := 0, 
            @min_margin := 0, @max_margin := 0,
            @min_pzp := 0, @max_pzp := 0
            ;
        
            -- Получаем минимальное и максимальное число просмотров
        SELECT @min_viewed := 0, @max_viewed := MAX(views) 
        FROM (
            SELECT
                shop_product_id AS product_id,
                COUNT(*) as views
            FROM shop_viewed_product AS v
            INNER JOIN
              cms_content_element AS cce ON cce.id = v.shop_product_id
            WHERE v.created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY) --  :interval --
              AND cce.active = 'Y'
            GROUP BY shop_product_id
        ) AS t;
            
            -- Получаем минимальное и максимальное число заказов товара
        SELECT @min_ordered := 0, @max_ordered := MAX(count_) 
        FROM (
            SELECT
                cce.id as product_id,
                COUNT(*) as count_
            FROM shop_basket AS basket
            INNER JOIN
              cms_content_element AS cce ON cce.id = basket.main_product_id
            WHERE basket.created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY) AND basket.order_id IS NOT NULL -- :interval
            GROUP BY cce.id
        ) AS t;
            
            -- Принимаем за данность, что Минимальная маржа не может быть отрицательной
            -- Получаем минимальное и максимальное число маржи
        /*
        SELECT @min_margin := 0, @max_margin := MAX(marge) 
        FROM (
             SELECT
                (price.price - property.value_enum) AS marge
            FROM ss_shop_product_prices AS price
            INNER JOIN cms_content_element AS cce ON cce.id = price.product_id AND cce.content_id = 2
            INNER JOIN cms_content_element_property AS property ON property.element_id = price.product_id AND property.property_id = 
              (SELECT id FROM cms_content_property WHERE code = 'PURCHASE_PRICE') AND cce.active = 'Y'
        ) AS t;
        */
        SELECT @min_margin := 0, @max_margin := MAX(marge) 
        FROM (
             SELECT
                price.price AS marge
            FROM ss_shop_product_prices AS price
            INNER JOIN cms_content_element AS cce ON cce.id = price.product_id
            INNER JOIN shop_basket AS basket on basket.main_product_id = cce.id
              AND basket.created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY) 
              AND basket.order_id IS NOT NULL -- :interval
        ) AS t;
    
            -- Получаем минимальное и максимальное прибыль за показ
        SELECT @min_pzp := 0, @max_pzp := MAX(pzp) 
        FROM (
                    SELECT 
                          IFNULL(((ordered * margin) / viewed), 0) AS pzp
                    FROM (
                        SELECT 
                            sp.id AS element_id, 
                            IFNULL(viewed.views, @min_viewed) AS viewed,
                            IFNULL(orders.count_, @min_ordered) AS ordered,
                            IFNULL(margin.marge, @min_margin) AS margin
                        FROM
                            shop_product AS sp
                        INNER JOIN
                            cms_content_element AS cce ON cce.id = sp.id
                        LEFT JOIN (
                             SELECT
                                shop_product_id AS product_id,
                                COUNT(*) as views
                             FROM shop_viewed_product
                             WHERE created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY)
                             GROUP BY shop_product_id
                        ) AS viewed ON viewed.product_id = sp.id
                        LEFT JOIN (
                            SELECT
                                ce.id as product_id,
                                COUNT(*) as count_
                            FROM shop_basket AS basket
                            INNER JOIN cms_content_element as ce on ce.id = basket.main_product_id
                            WHERE basket.created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY)
                              AND basket.order_id IS NOT NULL 
                            GROUP BY ce.id
                        ) AS orders ON orders.product_id = sp.id
                        /*LEFT JOIN (
                             SELECT
                                price.product_id,
                                property.value_enum,
                                price.price,
                                IF(price.price - property.value_enum < 0, 0 , price.price - property.value_enum) AS marge
                            FROM ss_shop_product_prices AS price
                            INNER JOIN cms_content_element AS ce ON ce.id = price.product_id AND ce.content_id = 2
                            INNER JOIN cms_content_element_property AS property ON property.element_id = price.product_id AND property.property_id = 
                              (SELECT id FROM cms_content_property WHERE code = 'PURCHASE_PRICE')
                        ) AS margin ON margin.product_id = sp.id
                        */
                        LEFT JOIN (
                             SELECT
                                price.product_id,
                                price.price as marge
                            FROM ss_shop_product_prices AS price
                        ) AS margin ON margin.product_id = sp.id
                        WHERE cce.content_id = 2 
                          AND sp.quantity > 0 
                          AND cce.active = 'Y'
                    ) AS t                    
        ) AS t;
        
         DELETE FROM shop_product_statistic WHERE id > 0;
        
         INSERT INTO shop_product_statistic (id, k_viewed, k_ordered, k_margin, k_pzp, k_1, k_2, k_rnd, k_rating, viewed, ordered, margin, pzp, k_quantity)
            SELECT 
                  t.element_id,
                  t.k_viewed,
                  t.k_ordered,
                  t.k_margin,
                  TRUNCATE(t.k_pzp, 3) AS k_pzp,
                  -- обычный рейтинг сортировки
                  TRUNCATE((t.k_pzp + t.k_viewed), 3) AS k_1,
                  -- сортировка с включением рандомных элементов (k1 + rnd2 ^ 3 / 3)
                  TRUNCATE((t.k_pzp + t.k_viewed + POW(t.k_rnd2, 3)/3), 3) AS k_2,
                  t.k_rnd,
                  TRUNCATE((t.k_pzp + t.k_viewed + 0.5 * t.k_rnd), 3) AS k_rating,
                  t.viewed,
                  t.ordered,
                  t.margin,
                  t.pzp,
                  t.quantity as k_quantity
            FROM (
                SELECT t.*,
                     (pzp - @min_pzp) / (@max_pzp - @min_pzp) AS k_pzp, 
                     -- рандомный коэффициент для рейтинга (звезды)
                     TRUNCATE(RAND(), 3) as k_rnd,
                     -- рандомный коэффициент для супер сортировки к_2
                     TRUNCATE(RAND(), 3) as k_rnd2
                FROM (
                    SELECT 
                          t.*,
                          (margin - @min_margin) / (@max_margin - @min_margin) AS k_margin,
                          (ordered - @min_ordered) / (@max_ordered - @min_ordered) AS k_ordered,
                          (viewed - @min_viewed) / (@max_viewed - @min_viewed) AS k_viewed,
                          IFNULL(((ordered * margin) / viewed), 0) AS pzp
                    FROM (
                        SELECT 
                            sp.id AS element_id, 
                            IFNULL(viewed.views, @min_viewed) AS viewed,
                            IFNULL(orders.count_, @min_ordered) AS ordered,
                            IFNULL(margin.marge, @min_margin) AS margin,
                            IFNULL(quantity.amount, 0) as quantity
                        FROM
                            shop_product AS sp
                        INNER JOIN
                            cms_content_element AS cce ON cce.id = sp.id
                        LEFT JOIN (
                             SELECT
                                shop_product_id AS product_id,
                                COUNT(*) as views
                             FROM shop_viewed_product
                             WHERE created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY)
                             GROUP BY shop_product_id
                        ) AS viewed ON viewed.product_id = sp.id
                        LEFT JOIN (
                            SELECT
                                ce.id as product_id,
                                COUNT(*) as count_
                            FROM shop_basket AS basket
                            INNER JOIN cms_content_element as ce on ce.id = basket.main_product_id
                            WHERE basket.created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY)
                              AND basket.order_id IS NOT NULL 
                              -- отсеиваем фейковые заказы
                              AND basket.order_id NOT IN (
                                SELECT so.id 
                                FROM shop_order so 
                                WHERE so.created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY)
                                  -- условия фейкового заказа
                                  AND (
                                    so.price > 100000
                                    OR EXISTS (
                                      SELECT 1 
                                      FROM shop_basket sb 
                                      WHERE sb.order_id = so.id 
                                       AND sb.has_removed = 0 
                                       AND sb.created_at >= UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY) 
                                       AND sb.quantity >= 10
                                    )
                                    OR so.created_by IN (1000, 36574, 68471, 1267)
                                  )
                              )
                            GROUP BY ce.id
                        ) AS orders ON orders.product_id = sp.id
                        /*LEFT JOIN (
                             SELECT
                                price.product_id,
                                property.value_enum,
                                price.price,
                                IF(price.price - property.value_enum < 0, 0, price.price - property.value_enum) AS marge
                            FROM ss_shop_product_prices AS price
                            INNER JOIN cms_content_element AS ce ON ce.id = price.product_id AND ce.content_id = 2
                            INNER JOIN cms_content_element_property AS property ON property.element_id = price.product_id AND property.property_id = 
                              (SELECT id FROM cms_content_property WHERE code = 'PURCHASE_PRICE')
                        ) AS margin ON margin.product_id = sp.id*/
                        LEFT JOIN (
                             SELECT
                                price.product_id,
                                price.price as marge
                            FROM ss_shop_product_prices AS price
                        ) AS margin ON margin.product_id = sp.id
                        
                        LEFT JOIN (
                            SELECT count(ce.id) amount, card.parent_content_element_id 
                            FROM cms_content_element ce
                            INNER JOIN shop_product sp on ce.id = sp.id
                            INNER JOIN cms_content_element card on card.id = ce.parent_content_element_id
                            WHERE ce.content_id = 10
                              AND sp.quantity > 0
                            GROUP BY card.parent_content_element_id
                        ) as quantity on quantity.parent_content_element_id = cce.id
                        
                        WHERE cce.content_id = 2 
                          AND sp.quantity > 0  
                          AND cce.active = 'Y' 
                          -- AND cce.id = 220284
                    ) AS t
                ) AS t 
            ) AS t;

        -- ON duplicate key update updated_at=unix_timestamp(), price=VALUES(price)

SQL;

        $data = \Yii::$app->db->createCommand($query, [
//            ':interval' => new Expression('SELECT UNIX_TIMESTAMP(CURRENT_DATE() - INTERVAL 7 DAY)')
        ])->execute();


        $this->delay(4);

        //Запуск пересчета стоковых коэффициентов
        \Yii::$app->runAction('tools/products/update-ratio-by-segments');

        $this->delay(4);

        $this->actionInsertLog();
    }


    /**
     * Логируем получившийся список для анализа ранжирования
     * @return bool
     */
    public function actionInsertLog()
    {
        $sql = <<<SQL
    SELECT *
    FROM shop_product_statistic
    ORDER BY k_stock DESC, k_1 DESC
    LIMIT 1000;
SQL;

        $data = \Yii::$app->db->createCommand($sql)->queryAll();

        $mongoDB = \Yii::$app->mongodb->createCommand();

        $batchs = array_chunk($data, 100);

        $date = date('Y-m-d H:i');

        $sort = 0;

        foreach ($batchs as $b) {
            foreach ($b as $data) {
                $insert = [
                    'product_id' => $data['id'],
                    'k_stock' => $data['k_stock'],
                    'k_1' => $data['k_1'],
                    'date' => $date,
                    'sort' => ++$sort,
                ];
                $mongoDB->addInsert($insert);
            }

            $mongoDB->executeBatch('statistic_product_range_log');
        }
    }

    /**
     * Пересчитывает рейтинг продуктов
     */
    public function actionRating()
    {
        $query = <<<SQL
          SET @rating_id := (select id from cms_content_property where code = 'RATING' and content_id=2);
          SET @rating_def_value := 1;
          SET @total := (select count(*) from shop_product_statistic);
          SET @rownum := 0;
          
          DELETE FROM cms_content_element_property WHERE property_id = @rating_id;
          
          INSERT INTO cms_content_element_property(created_at, updated_at, property_id, element_id, value)
          SELECT created_at, updated_at, property_id, element_id, 
                  CASE 
                    WHEN rank < 0.1 THEN 3 
                    WHEN rank < 0.25 THEN 3.5
                    WHEN rank < 0.75 THEN 4
                    WHEN rank < 0.9 THEN 4.5
                    ELSE 5
                  END as value
          FROM (
              SELECT 
                unix_timestamp() AS created_at,
                unix_timestamp() AS updated_at,
                @rating_id AS property_id,
                t.id AS element_id,
                (@rownum := @rownum + 1)/@total AS rank
              FROM shop_product_statistic t
              ORDER BY t.k_rating
          ) tt
SQL;

        $data = \Yii::$app->db->createCommand($query)->execute();

        var_dump($data);
    }

    private function sendNotifyEmail($msg, $sbj = '', $emails = [])
    {

        $mailSended = false;

        try {

            \Yii::$app->mailer->htmlLayout = false;
            \Yii::$app->mailer->textLayout = false;

            if (!$emails) {
                $emails = [
                    'anisimov_da@shopandshow.ru',
                    'soskov_da@shopandshow.ru',
                    'ryabov_yn@shopandshow.ru'
                ];
            }

            $subject = $sbj ? $sbj : 'Оповещение';

            $message = \Yii::$app->mailer->compose()
                ->setFrom('no-reply@shopandshow.ru')
                ->setTo($emails)
                ->setSubject($subject)
                ->setHtmlBody($msg);

            $mailSended = $message->send();

            $this->stdout("Сообщение " . ($mailSended ? 'отправлено успешно.' : 'не удалось отправить') . "\n",
                $mailSended ? Console::FG_GREEN : Console::FG_RED
            );

        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }

        return $mailSended;
    }

    public function actionBannersStat()
    {
        $this->layout = false;

        $model = new SharesStat();
        $html = $this->render('@modules/shopandshow/views/statistics/shares/daily/banners2', ['model' => $model, 'noGridView' => true]);

        $emails = [
            'anisimov_da@shopandshow.ru',
            'soskov_da@shopandshow.ru',
            'ryabov_yn@shopandshow.ru',
            'gutorov_iv@shopandshow.ru',
            'panina_av@shopandshow.ru',
            'selyansky@shopandshow.ru'
        ];

        $this->sendNotifyEmail($html, "Отчет по баннерам", $emails);

        return;
    }

    /**
     * Получаем из битрикс инфу о кол-ве заказов клиента и кол-во выкупленных / не выкупленных
     *
     * @param $dateFrom - Дата с которой производим поиск данных для отчета
     * @param $dateTo - Дата по которую производим поиск данных для отчета
     * @param int $ordersNumLimitMin - Минимальное число заказов что бы клиент попал в отчет
     * @param float $ordersCompletePercentMin - Минимальный процент выкупа что бы клиент попал в отчет
     * @param int $countCompletePercent - Считать ли процент выкупа заказов
     * @param int $ordersNumLimitMax - Максимальное число заказов что бы клиент попал в отчет
     * @param float $ordersCompletePercentMin - Максимальное процент выкупа что бы клиент попал в отчет
     */
    public function actionGetBitrixOrdersCompleteStat(
        $dateFrom,
        $dateTo,
        $ordersCompleteNumLimitMin = 1,
        $ordersCompletePercentMin = 0.5,
        $countCompletePercent = 1,
        $ordersCompleteNumLimitMax = 999999
    )
    {

        $this->stdout("Получение статистики по выкупу заказов клиентами в разрезе телефонов" . PHP_EOL, Console::FG_GREEN);

        $this->stdout("Дата С = {$dateFrom}" . PHP_EOL);
        $this->stdout("Дата ПО = {$dateTo}" . PHP_EOL);
        $this->stdout("Минимальное кол-во выполненных заказов = {$ordersCompleteNumLimitMin}" . PHP_EOL);
        $this->stdout("Максимальное кол-во выполненных заказов = {$ordersCompleteNumLimitMax}" . PHP_EOL);
        $this->stdout("Минимальное процент выполненных заказов = {$ordersCompletePercentMin}" . PHP_EOL);
        $this->stdout("Считать ли процент выкупа заказов = " . ($countCompletePercent ? 'ДА' : 'НЕТ') . PHP_EOL);

        $sql = <<<SQL
SELECT
  order_phone.VALUE AS PHONE,
  SUM(IF(orders.STATUS_ID = 'F', 1, 0)) AS ORDERS_COMPLETE,
  SUM(IF(orders.STATUS_ID = 'F', 0, 1)) AS ORDERS_NOTCOMPLETE,
  0 AS ORDERS_COMPLETE_PERCENT
FROM front2.b_sale_order AS orders
  INNER JOIN front2.b_sale_order_props_value AS order_phone
    ON orders.ID = order_phone.ORDER_ID AND order_phone.ORDER_PROPS_ID = 3
WHERE orders.DATE_INSERT >= :dateFrom AND orders.DATE_INSERT <= :dateTo
GROUP BY PHONE
HAVING
  ORDERS_COMPLETE >= :ordersCompleteNumLimitMin
  AND ORDERS_COMPLETE <= :ordersCompleteNumLimitMax
  AND (ORDERS_COMPLETE / (ORDERS_COMPLETE + ORDERS_NOTCOMPLETE)) >= :ordersCompletePercentMin
SQL;

        $query = \Yii::$app->db->createCommand($sql, [
            ':dateFrom' => $dateFrom,
            ':dateTo' => $dateTo,
            ':ordersCompleteNumLimitMin' => (int)$ordersCompleteNumLimitMin,
            ':ordersCompletePercentMin' => (float)$ordersCompletePercentMin,
            ':ordersCompleteNumLimitMax' => (int)$ordersCompleteNumLimitMax,
        ]);

        $debugMode = false;

        if ($debugMode) {
            $bitrixOrdersUsers = [
                [
                    'PHONE' => '+74951234567',
                    'ORDERS_COMPLETE' => '6',
                    'ORDERS_NOTCOMPLETE' => '10',
                    'ORDERS_COMPLETE_PERCENT' => '0',
                ],
                [
                    'PHONE' => '8(495)765-4321',
                    'ORDERS_COMPLETE' => '15',
                    'ORDERS_NOTCOMPLETE' => '20',
                    'ORDERS_COMPLETE_PERCENT' => '0',
                ],
                [
                    'PHONE' => '+7-495-765-43-21',
                    'ORDERS_COMPLETE' => '7',
                    'ORDERS_NOTCOMPLETE' => '12',
                    'ORDERS_COMPLETE_PERCENT' => '0',
                ],
                [
                    'PHONE' => '495 123 4567',
                    'ORDERS_COMPLETE' => '12',
                    'ORDERS_NOTCOMPLETE' => '20',
                    'ORDERS_COMPLETE_PERCENT' => '0',
                ],
            ];
        } else {
            $bitrixOrdersUsers = $query->queryAll();
        }

        $count = count($bitrixOrdersUsers);
        $counterStep = $count / 100; //каждый 1 процент, сколько это в штуках

        $this->stdout("Пользователей с заказами найдено - {$count}" . PHP_EOL, Console::FG_GREEN);

        if ($bitrixOrdersUsers) {

            //* Приведение телефона к одному формату и связанная с этим дедупликация *//

            $this->stdout("Дедуплицирую телефоны клиентов" . PHP_EOL, Console::FG_GREEN);

            $bitrixOrdersUsersDeduplicated = [];

            $counterGlobal = 0;
            $counter = 0;
            Console::startProgress(0, $count);
            foreach ($bitrixOrdersUsers as $bitrixOrdersUser) {

                $counterGlobal++;
                $counter++;

                if ($counter >= $counterStep || $counterGlobal == $count) {
                    $counter = 0;
                    Console::updateProgress($counterGlobal, $count);
                }

                //Телефоном будем считать только цифры из поля и только последние 10 знаков (4957654321)
                $phoneProper = substr(Strings::onlyInt($bitrixOrdersUser['PHONE']), -10);

                if (!isset($bitrixOrdersUsersDeduplicated[$phoneProper])) {
                    $bitrixOrdersUsersDeduplicated[$phoneProper] = $bitrixOrdersUser;
                    $bitrixOrdersUsersDeduplicated[$phoneProper]['PHONE'] = $phoneProper;
                } else {
                    $bitrixOrdersUsersDeduplicated[$phoneProper]['ORDERS_COMPLETE'] += $bitrixOrdersUser['ORDERS_COMPLETE'];
                    $bitrixOrdersUsersDeduplicated[$phoneProper]['ORDERS_NOTCOMPLETE'] += $bitrixOrdersUser['ORDERS_NOTCOMPLETE'];
                }
            }

            $bitrixOrdersUsers = $bitrixOrdersUsersDeduplicated;
            unset($bitrixOrdersUsersDeduplicated);

            $count = count($bitrixOrdersUsers);
            $counterStep = $count / 100; //каждый 1 процента, сколько это в штуках

            $this->stdout("Пользователей с заказами найдено (дедуплицированных) - {$count}" . PHP_EOL, Console::FG_GREEN);

            //* /Приведение телефона к одному формату и связанная с этим дедупликация *//

            //Если запрошен подсчет процента выполненных заказов - считаем
            if ($countCompletePercent) {

                $this->stdout("Просчитываю процент выкупа" . PHP_EOL, Console::FG_CYAN);

                $counterGlobal = 0;
                $counter = 0;
                Console::startProgress(0, $count);
                $bitrixOrdersUsers = array_map(function ($row) use (&$counter, &$counterGlobal, &$counterStep, $count) {

                    $counterGlobal++;
                    $counter++;

                    if ($counter >= $counterStep || $counterGlobal == $count) {
                        $counter = 0;
                        Console::updateProgress($counterGlobal, $count);
                    }

                    $ordersAll = $row['ORDERS_COMPLETE'] + $row['ORDERS_NOTCOMPLETE'];

                    $row['ORDERS_COMPLETE_PERCENT'] = round($row['ORDERS_COMPLETE'] / $ordersAll, 2);

                    return $row;
                }, $bitrixOrdersUsers);
            }

            //Экспортируем в файл

            $folder = __DIR__ . '/files/';

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
            $filename = $folder . 'users_orders_complete_stat_' . date("Y-m-d_H-i-s") . '.csv';

            $this->stdout("Экспортирую данные в файл '{$filename}'" . PHP_EOL, Console::FG_CYAN);

            $file = fopen($filename, 'wb');

            if (!$file) {
                $this->stdout("Ошибка при создании файла (" . PHP_EOL, Console::FG_RED);
            } else {
                fputcsv($file, ['PHONE', 'COMPLETE_ORDERS', 'NOTCOMPLETE_ORDERS', 'COMPLETE_PERCENT']);

                if ($bitrixOrdersUsers) {
                    $counterGlobal = 0;
                    $counter = 0;
                    Console::startProgress(0, $count);
                    foreach ($bitrixOrdersUsers as $bitrixUserStat) {
                        $counterGlobal++;
                        $counter++;

                        if ($counter >= $counterStep || $counterGlobal == $count) {
                            $counter = 0;
                            Console::updateProgress($counterGlobal, $count);
                        }
                        fputcsv($file, $bitrixUserStat);
                    }
                }

                fclose($file);
            }

        } else {
            $this->stdout("Пользоваталей с заказами не найдено" . PHP_EOL, Console::FG_YELLOW);
        }

        $this->stdout("Done" . PHP_EOL, Console::FG_GREEN);

        return;
    }

}