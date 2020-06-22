<?php

use skeeks\cms\shop\models\ShopDelivery;
use yii\db\Migration;

class m180614_120417_alter_table_shop_fuser_add_field_delivery_service_id extends Migration
{
    private $tableName = 'delivery_services';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {


/*        SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `delivery_services`;
CREATE TABLE `delivery_services` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Название',
  `code` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Кодовое обозначение',
  `isActive` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Активно',
  `fixedCost` int(11) DEFAULT NULL COMMENT 'Фиксированная стоимость',
  `info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Пояснительная информация',
  `terms` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Сроки доставки',
  `serviceShownFor` int(10) NOT NULL COMMENT 'Доставка показывается для (регионов, города отправки, везде)',
  `dateDb` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  `delivery_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-profile-name` (`name`),
  KEY `idx-profile-code` (`code`),
  KEY `idx-profile-created_at` (`created_at`),
  KEY `idx-profile-updated_at` (`updated_at`),
  KEY `fk_delivery_id` (`delivery_id`),
  CONSTRAINT `fk_delivery_id` FOREIGN KEY (`delivery_id`) REFERENCES `shop_delivery` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `delivery_services` (`id`, `name`, `code`, `isActive`, `fixedCost`, `info`, `terms`, `serviceShownFor`, `dateDb`, `created_at`, `updated_at`, `delivery_id`) VALUES
    (1,	'Курьер',	'courier',	1,	200,	NULL,	'1 день',	2,	NULL,	1519663462,	1519663462,	NULL),
(2,	'Почта России',	'post',	1,	NULL,	NULL,	NULL,	3,	NULL,	1519663462,	1519663462,	NULL),
(3,	'Почта России наложенным',	'post_naloj',	1,	NULL,	NULL,	NULL,	3,	'2018-07-04 14:30:29',	1519663462,	1519663462,	6),
(4,	'Самовывоз',	'pickup',	1,	0,	'Самостоятельно забираете в офисе.',	NULL,	1,	NULL,	1519663462,	1519663462,	NULL),
(5,	'СДЭК-склад',	'cdek_store',	1,	NULL,	'Тариф \"Доставка до склада\" - Вам нужно будет забирать посылку в отделении СДЭК',	NULL,	3,	NULL,	1519663462,	1519663462,	NULL),
(6,	'СДЭК-дверь',	'cdek_door',	1,	NULL,	'Тариф \"Доставка до двери\" - Вам принесут посылку до указанного адреса',	NULL,	3,	NULL,	1519663462,	1519663462,	NULL),
(7,	'Энергия',	'energy',	1,	NULL,	'',	NULL,	3,	NULL,	1519663462,	1519663462,	NULL),
(8,	'Доставка на выбор',	'on_choice',	1,	NULL,	'В примечании к заказу укажите предпочтительный способ доставки',	NULL,	2,	NULL,	1519663462,	1519663462,	NULL),
(10,	'Курьер',	'ss_courier',	1,	NULL,	'Наш абстрактный курьер',	NULL,	3,	NULL,	1530703829,	1530703829,	5);*/

        return true;

        $this->addColumn($this->tableName, 'delivery_id', $this->integer());

        $this->addForeignKey('fk_delivery_id', $this->tableName, 'delivery_id', 'shop_delivery', 'id', 'RESTRICT', 'RESTRICT');

        $deliveryCourier = ShopDelivery::find()->where(['name' => 'Курьер'])->one();
        if (!$deliveryCourier) {
            $deliveryCourier = new ShopDelivery([
                'name' => 'Курьер',
                'site_id' => \Yii::$app->currentSite->site->id,
                'active' => \skeeks\cms\components\Cms::BOOL_Y,
                'price' => 0,
                'currency_code' => \Yii::$app->money->currencyCode,
                'priority' => 200,
            ]);

            $deliveryCourier->save();
        }

        $this->insert($this->tableName, [
            'name' => 'Курьер',
            'code' => 'ss_courier',
            'isActive' => 1,
            'fixedCost' => null,
            'info' => 'Наш абстрактный курьер',
            'terms' => null,
            'serviceShownFor' => 3,
            'dateDb' => null,
            'created_at' => time(),
            'updated_at' => time(),
            'delivery_id' => $deliveryCourier->id
        ]);

        $deliveryPost = ShopDelivery::find()->where(['name' => 'Почта России'])->one();
        if (!$deliveryPost) {
            $deliveryPost = new ShopDelivery([
                'name' => 'Почта России',
                'site_id' => \Yii::$app->currentSite->site->id,
                'active' => \skeeks\cms\components\Cms::BOOL_Y,
                'price' => 0,
                'currency_code' => \Yii::$app->money->currencyCode,
                'priority' => 100,
            ]);

            $deliveryPost->save();
        }

        $this->update($this->tableName, ['delivery_id' => $deliveryPost->id], ['code' => 'post_naloj']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('delivery_services', ['code' => 'ss_courier']);

        $this->dropForeignKey('fk_delivery_id', $this->tableName);

        $this->dropColumn($this->tableName, 'delivery_id');
    }
}
