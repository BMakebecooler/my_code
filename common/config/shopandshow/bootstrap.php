<?php
/**
 * bootstrap
 */

//TODO: Я думаю стоит это потом отсюда убрать
use yii\base\Event;
use yii\db\ActiveRecord;

Yii::setAlias('common', COMMON_DIR);
Yii::setAlias('frontend', ROOT_DIR . '/frontend');
Yii::setAlias('console', ROOT_DIR . '/console');
Yii::setAlias('modules', ROOT_DIR . '/modules');

/**  */
Yii::setAlias('templates', ROOT_DIR . '/frontend/templates/v2');

Yii::setAlias('template_common', ROOT_DIR . '/frontend/templates/v2/common');
Yii::setAlias('site', ROOT_DIR . '/frontend/templates/v2/site');
Yii::setAlias('mobile', ROOT_DIR . '/frontend/templates/v2/mobile');
Yii::setAlias('mail', ROOT_DIR . '/frontend/templates/v2/mail');

Yii::setAlias('@web_common', '/v2/common');

Yii::setAlias('@images', 'https://images.shopandshow.ru');

Yii::setAlias('@theme_common', '@frontend/themes/v3/common');

/**
 * @todo Перенести в инициализватор сред
 */
Yii::setAlias('@static', '/');
if (YII_ENV === 'production') {
//    Yii::setAlias('@static', 'https://static.shopandshow.ru'); НЕ ЗАБЫТЬ РАСКОМЕНТИТЬ!!!!
}

define('CONST_SITE_SHIK', 'shik');
define('CONST_SITE_SS', 'shopandshow');

define('YES_INT', 1);
define('NO_INT', 0);

// Лот
define('PRODUCT_CONTENT_ID', 2);
// Карточка товара
define('CARD_CONTENT_ID', 5);
// Модификация
define('OFFERS_CONTENT_ID', 10);
// Для хранения свойств 3-х предыдущих типов
define('KFSS_PRODUCT_CONTENT_ID', 11);

define('CATALOG_TREE_TYPE_ID', 5);
define('RUBRICATOR_TREE_TYPE_ID', 8);

define('TREE_CATEGORY_ID_ROOT', 1);
define('TREE_CATEGORY_ID_CATALOG', 9);
define('TREE_CATEGORY_ID_RUBRICATOR', 2262);

define('LOOKBOOK_SECTION_CONTENT_ID', 165); //разделы с темами для лукбуков
define('LOOKBOOK_CONTENT_ID', 167); //Лукбуки

define('LOOKBOOK_CLIENTS_CONTENT_ID', 171); //Лукбуки клиентов
define('PROMO_CONTENT_ID', 127); //Промо акции
define('BRAND_CONTENT_ID', 190); //Бренды

define('MOBILE_SEARCH_PER_PAGE_DEFAULT', 30);

define('COOKIE_NAME_IS_SHOW_DEBUG_PANEL', 'test_cookie_name');

/**
 * Константы времени для удобства
 */
define('MIN_1', 60);
define('MIN_5', 60 * 5);
define('MIN_10', 60 * 10);
define('MIN_15', 60 * 15);
define('MIN_25', 60 * 25);
define('MIN_30', 60 * 30);
define('HOUR_1', 60 * 60);
define('HOUR_2', 60 * 60 * 2);
define('HOUR_3', 60 * 60 * 3);
define('HOUR_4', 60 * 60 * 4);
define('HOUR_5', 60 * 60 * 5);
define('HOUR_6', 60 * 60 * 6);
define('HOUR_8', 60 * 60 * 8);

defined('DAYS_1') or define('DAYS_1', 60 * 60 * 24);
defined('DAYS_2') or define('DAYS_2', 60 * 60 * 24 * 2);
defined('DAYS_5') or define('DAYS_5', 60 * 60 * 24 * 5);
defined('DAYS_7') or define('DAYS_7', 60 * 60 * 24 * 7);
defined('DAYS_14') or define('DAYS_14', 60 * 60 * 24 * 14);
defined('DAYS_30') or define('DAYS_30', 60 * 60 * 24 * 30);
defined('YEAR') or define('YEAR', 60 * 60 * 24 * 365);


// events


Event::on(\modules\shopandshow\models\shop\ShopOrder::className(), ActiveRecord::EVENT_AFTER_UPDATE, function ($event) {
    Yii::$app->queue->push(new \console\jobs\ExportOrderAnalyticsJob([
        'id' => $event->sender->id,
    ]));
});