<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 18.09.2015
 */

/**return
    [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=192.168.0.200;dbname=front2;port=3306;',
        'username' => 'shopandshow',
        'password' => 'KooPh3mee9ei1isein',
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,
    ];
**/

return
    [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=192.168.0.58;dbname=front2;port=3306;',
        'username' => 'new',
        'password' => '',
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        }
    ];
