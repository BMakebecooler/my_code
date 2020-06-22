<?php

/**
 * php ./yii test/common/get-env
 * php ./yii test/common/get-db
 * php ./yii test/common/get-site
 * php ./yii test/common/generate-guids
 * php ./yii test/common/bitrix-auth
 * php ./yii test/common/lot-json
 * php ./yii test/common/generate-tree-guid
 * php ./yii test/common/normalize-tree
 */
namespace console\controllers\test;

use common\models\Tree;
use console\controllers\export\ExportController;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use skeeks\cms\components\Cms;
use yii\helpers\Console;


/**
 * Class CommonController
 *
 * @package console\controllers
 */
class CommonController extends ExportController
{

    public function actionGetEnv()
    {
        var_dump(YII_ENV);
    }

    public function actionGetSite()
    {
        var_dump(SS_SITE);
    }

    public function actionGetDb()
    {
        var_dump(\Yii::$app->db);
    }

    /**
     * Генерация гуидов
     */
    public function actionGenerateGuids()
    {
        $orders = ShopOrder::find()
            ->joinWith(['user'])
            ->andWhere('shop_order.status_code != :status', [
                ':status' => ShopOrderStatus::STATUS_SUCCESS
            ])
            ->andWhere('cms_user.guid_id IS NULL')
            ->all();


        if (!$orders) {
            $this->stdout("No new orders\n", Console::FG_YELLOW);
            return true;
        }

        /**
         * @var $order ShopOrder
         */
        foreach ($orders as $order) {

            if (!$order->guid_id) {
                $this->stdout("order {$order->id} set guid \n", Console::FG_GREEN);
                $order->guid->generateGuid();
            }

            if (!$order->user->guid_id) {
                $this->stdout("user {$order->user->id} set guid \n", Console::FG_GREEN);
                $order->user->guid->generateGuid();
            }
        }
    }


    public function actionBitrixAuth()
    {
        $bitrixUserId = \Yii::$app->front->getBitrixUserApiAuth([
            'login' => 'konovalov_vv@shopandshow.ru',
            'password' => '3!5AfGJ77',
        ]);

        var_dump($bitrixUserId);
    }

    public function actionGenerateTreeGuid()
    {


        $tree = Tree::findOne(1830);

        var_dump($tree->guid->getGuid());
        return;

        Tree::deleteAll("dir = 'about/vacancies'");


        $vacancySection = new Tree();
        $vacancySection->name = 'Вакансии';
        $vacancySection->code = 'test-guid';
        $vacancySection->dir = 'about/vacancies';
        $vacancySection->tree_type_id = 2;
        $vacancySection->view_file = '@template/modules/cms/content-element/about/vacancies';
        $vacancySection->pid = 1812;
        $vacancySection->pids = '1/1812';
        $vacancySection->level = 2;

        $vacancySection->guid->setGuid('5555555555');

        if (!$vacancySection->save()) {
            var_dump($vacancySection->getErrors());
        }

        var_dump($vacancySection->id);
        var_dump($vacancySection->guid->getGuid());

    }


    public function actionNormalizeTree()
    {
        $tree = Tree::find()->andWhere('id >= 3444')->orderBy('level ASC')->all();

        $getPids = function (Tree $tree) {

            $expl = explode(Tree::PIDS_DELIMETR, $tree->dir);

            unset($expl[count($expl) - 1]);

            $pids[] = 1;

            foreach ($expl as $item) {

                $find = Tree::find()->andWhere('active = :active AND code =:code', [
                    ':active' => Cms::BOOL_Y,
                    ':code' => $item,
                ])->one();

                if ($find) {
                    $pids[] = $find->id;
                }
            }

            return join(Tree::PIDS_DELIMETR, $pids);
        };

        foreach ($tree as $t) {
            if ($pids = $getPids($t)) {
                $t->pids = $pids;
                if (!$t->save()) {
                    var_dump($t->getErrors());
                };
            }
        }
    }
}



