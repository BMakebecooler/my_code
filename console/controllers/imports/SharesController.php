<?php

/**
 * php ./yii imports/shares/banners
 */
namespace console\controllers\imports;

use yii\helpers\Console;

/**
 * Class SharesController
 *
 * @package console\controllers
 */
class SharesController extends \yii\console\Controller
{

    protected $agentStartTime;

    public function beforeAction($action)
    {
        $this->stdout("\nBegin: " . $action->getUniqueId() . "\n\n", Console::FG_YELLOW);

        $this->agentStartTime = time();

        return true;
    }

    public function afterAction($action, $result)
    {
        $this->stdout("\n\nElapsed: " . (time() - $this->agentStartTime) . "sec.\n", Console::FG_YELLOW);

        return parent::afterAction($action, $result);
    }

    public function actionBanners()
    {

/*        \Yii::$app->shares->getAdvBanners();

        \Yii::$app->shares->getActualBanners();

        \Yii::$app->shares->getMainSmallEfirsBanners();*/

        $this->stdout('Deprecated', Console::FG_PURPLE);
        
//        \Yii::$app->shares->getAdvBannersInfoBlock();


        \Yii::$app->shares->removeThumbs();
//
//        \Yii::$app->shares->createCmsContentElement();
//
//        \Yii::$app->shares->bannersProducts();
//
//        \Yii::$app->shares->bannersProductsOriginal();

    }

}