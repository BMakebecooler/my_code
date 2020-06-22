<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 16.08.2017
 * Time: 15:00
 */

namespace console\models\sync\promo;


use common\helpers\Msg;
use console\controllers\sync\SyncController;
use modules\shopandshow\models\shop\ShopDiscount;
use yii\helpers\Console;

class PromoManager
{
    const API_URL = 'https://bo.shopandshow.ru/sands_promo/main/';

    public $controller;
    /** @var Promo[] */
    public $promoActions = [];

    public $dt = null;
    public $df = null;

    /**
     * PromoManager constructor.
     *
     * @param SyncController $controller
     */
    public function __construct(SyncController $controller)
    {
        $this->controller = $controller;
        $now = time();
        $this->dt = date('Y-m-d', $now);
        $this->df = date('Y-m-d', $now - DAYS_7);
    }

    /**
     * Создает промоакции из полученного массива и запускает их обсчет
     */
    public function processPromoactions()
    {
        $data = $this->getPromoData();

        foreach ($data as $row) {
            $this->addPromo(new Promo($row));
        }
        //$this->clearDeleted();
        $this->calculate();
    }

    /**
     * Получает акции из BO
     * @return mixed
     */
    public function getPromoData()
    {
        $attempt = 0;
        do {
            $attempt++;

            try {
                $client = new \yii\httpclient\Client();

                $query_params = [];
                /*if ($this->dt) {
                    $query_params['to'] = $this->dt;
                    $query_params['active_to'] = $this->dt;
                }*/
                if ($this->df) {
                    $query_params['from'] = $this->df;
                    $query_params['active_from'] = $this->df;
                }

                /** @var \yii\httpclient\Response $res */
                $res = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl(self::API_URL)
                    ->setData($query_params)
                    ->setOptions([
                        'timeout' => 30,
                    ])
                    ->send();

                $data = $res->getData();
            } catch (\yii\httpclient\Exception $e) {
                $data = [];
                sleep(30);
            }

            if ($attempt == 3) {
                \common\helpers\Developers::reportProblem('Ошибка при подключении к bo: '.$e->getMessage().PHP_EOL.$e->getTraceAsString());
                \Yii::error('Промо не затянулся с 3 попыток!');
                break;
            }
        } while(empty($data));

        return $data;
    }

    /**
     * добавляет промоакцию
     * @param Promo $promo
     */
    public function addPromo(Promo $promo)
    {
        $promo->manager = $this;
        $this->promoActions[$promo->id] = $promo;
    }

    /**
     * Удаляет из БД те, которые удалили в bo
     */
    public function clearDeleted()
    {
        $shopDiscountDeleteList = ShopDiscount::find()
            ->andWhere(['>=', 'active_to', strtotime($this->dt)])
            ->andWhere('bo_id is not null')
            ->andWhere(['not in', 'bo_id', array_keys($this->promoActions)])
            ->all();

        /** @var ShopDiscount $shopDiscount */
        foreach ($shopDiscountDeleteList as $shopDiscount) {
            $shopDiscount->active = \skeeks\cms\components\Cms::BOOL_N;
            $shopDiscount->save();
        }
    }

    /**
     * обсчитывает все промоакции
     */
    public function calculate()
    {
        $this->fetchLadderPromo();
        foreach($this->promoActions as $promoaction) {
            $this->controller->stdout("Sync promo [{$promoaction->id}] {$promoaction->name}: \n", Console::FG_YELLOW);

            try {
                $shopDiscount = $promoaction->getShopDiscount();

                if(!$shopDiscount->save()) {
                    $this->controller->stdout(" Cant save ShopDiscount: ".print_r($shopDiscount->getErrors(), true)."\n", Console::FG_RED);
                    continue;
                }

                $promoaction->calculateConditions();
            }
            catch(\Exception $e) {
                $this->controller->stdout(" Error: {$e->getMessage()}\n", Console::FG_RED);
                continue;
            }

            $this->controller->stdout(" OK \n", Console::FG_YELLOW);
        }

    }

    /**
     * составляет список из нескольких промоакций "лестница скидок" и отдает его на пересчет
     */
    protected function fetchLadderPromo()
    {
        $promoGroup = [];
        foreach($this->promoActions as $promoAction) {
            if(!$promoAction->isLadder()) continue;

            // все акции лестницы скидок группируются по дням
            $promoDate = $promoAction->period['from'];

            // группа пустая, или уже есть такая акция => добавляемся
            if(empty($promoGroup) || array_key_exists($promoDate, $promoGroup)) {
                $promoGroup[$promoDate][] = $promoAction;
            }
            else {
                $this->rebuildLadderPromo($promoGroup);
                $promoGroup = [];
                $promoGroup[$promoDate][] = $promoAction;
            }
        }
        if($promoGroup) $this->rebuildLadderPromo($promoGroup);
    }

    /**
     * пересчитывает список акций лестница скидок, оставляя только одну
     * @param Promo[] $promoGroup
     */
    protected function rebuildLadderPromo(array $promoGroup)
    {
        $promoGroup = end($promoGroup);
        $mainPromo = $this->getladderMainPromoAction($promoGroup);
        $this->controller->stdout("Rebuilding ladder promo: {$mainPromo->id} from ".sizeof($promoGroup)." elements\n", Console::FG_CYAN);
        foreach ($promoGroup as $promoAction) {
            if($promoAction->id == $mainPromo->id) continue;

            $mainPromo->addRelatedPromo($promoAction);

            // удаляем промоакцию из общего списка
            unset($this->promoActions[$promoAction->id]);
        }
    }

    /**
     * Находит основную акцию в группе
     * @param Promo[] $promoGroup
     * @return Promo $mainPromo
     */
    protected function getladderMainPromoAction(array $promoGroup)
    {
        // основная акция в группе
        $mainPromo = null;

        foreach ($promoGroup as $promoAction) {
            if($promoAction->haveCondition('\SandsPromo\ForSum')) {
                $mainPromo = $promoAction;
                break;
            }
        }
        // если ни у одной акции нет такого условия, значит ищем с макс скидкой
        if(empty($mainPromo)) {
            $mainPromo = array_reduce($promoGroup, function(Promo $result, Promo $promoAction) {
                if($promoAction->getForSumValue() > $result->getForSumValue()) return $promoAction;
                return $result;
            }, reset($promoGroup));
        }

        return $mainPromo;
    }
}