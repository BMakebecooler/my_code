<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(dirname(dirname(__DIR__))));

define("APP_DIR", dirname(dirname(dirname(__DIR__))).'/frontend/web');
define("ROOT_DIR", dirname(dirname(dirname(__DIR__))));
define("COMMON_DIR", ROOT_DIR . '/common');    //Где общая папка

require_once(YII_APP_BASE_PATH . '/vendor/autoload.php');

// Environment
require(__DIR__ . '/../../../common/env.php');

require_once(YII_APP_BASE_PATH . '/vendor/yiisoft/yii2/Yii.php');
require_once(YII_APP_BASE_PATH . '/common/config/shopandshow/bootstrap.php');

// set correct script paths
$_SERVER['COMMON_DIR'] = 'localhost';

define("ROOT_DIR", dirname(dirname(dirname(__DIR__))));

Yii::setAlias('@tests', dirname(dirname(__DIR__)));
Yii::setAlias('common', COMMON_DIR);
Yii::setAlias('frontend', ROOT_DIR . '/frontend');
Yii::setAlias('backend', ROOT_DIR . '/backend');
Yii::setAlias('console', ROOT_DIR . '/console');
Yii::setAlias('modules', ROOT_DIR . '/modules');

new yii\web\Application(require(YII_APP_BASE_PATH . '/common/config/shopandshow/env/test/main.php'));