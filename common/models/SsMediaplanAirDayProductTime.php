<?php

namespace common\models;

use common\helpers\ArrayHelper;
use common\helpers\Dates;
use common\models\query\SsMediaplanAirDayProductTimeQuery;
use Yii;

class SsMediaplanAirDayProductTime extends \common\models\generated\models\SsMediaplanAirDayProductTime
{
    public $dayId = 0;
    public $categoryId = 0;
    public $hourId = 0;
    public $hourTime = 0;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['dayId', 'categoryId', 'hourId', 'hourTime'], 'integer'],
            [['dayId'], 'required'],
        ]);
    }

    public static function find()
    {
        return new SsMediaplanAirDayProductTimeQuery(get_called_class());
    }

    public function getProductsForSale()
    {
        $data = [];

        $airProducts = $this->getAirProducts();

        if ($airProducts) {
            $data = Product::find()
                ->canSale()
                ->andWhere(['cms_content_element.id' => ArrayHelper::getColumn($airProducts, 'lot_id')])
                ->all();
        }

        return $data;
    }

    //Товары которые есть в расписании. Без какой либо фильтрации и проверок
    public function getAirProducts()
    {
        return $this->getAirProductsQuery()->all();
    }

    public function getAirProductsQuery()
    {
        $airProductsQuery = SsMediaplanAirDayProductTime::find()
            ->byDay(Dates::getDaytimeFromId($this->dayId));

        if ($this->categoryId) {
            $airProductsQuery->byCategoryId($this->categoryId);
        }

        if ($this->hourId) {
            $airProductsQuery->byAirBlock($this->hourId);
        }

        if ($this->hourTime) {
            $airProductsQuery->byBeginDatetimePeriod(Dates::getHourBegin($this->hourTime), Dates::getHourEnd($this->hourTime));
        }

        return $airProductsQuery;
    }

    public function getAirProductsLastWeek()
    {
        return $this->getAirProductsLastWeekQuery()->all();
    }

    public function getAirProductsLastWeekQuery()
    {
        return SsMediaplanAirDayProductTime::find()
            ->byBeginDatetimePeriod(
                Dates::beginOfDate(strtotime('-8 DAYS')),
                Dates::beginOfDate()
            );
    }

    /**
     * @param $product Product
     */
    public static function isProductOnAir($product)
    {
        $lot = $product->isLot() ? $product : \common\helpers\Product::getLot($product->id);

        if ($lot){
            $airProduct = (new self())->getAirProductsQuery()->andWhere(['lot_id' => $lot->id])->one();
        }

        return (bool)!empty($airProduct);
    }

    //возвращает список ИД карточек которые сегодня в эфире
    public static function getTodayAirProductsCardsIds($useCache = false)
    {
        $cacheKey = 'today_air_products_cards_ids_' . date("Ymd");

        if ($useCache){
            $fromCache = \Yii::$app->cache->get($cacheKey);
        }

        //Если используем кеш, а в нем пусто или если не используем кеш
        if ( ($useCache && empty($fromCache)) || !$useCache ){
            $airProductsModel = new self();
            if ($airProductsLotsIds = $airProductsModel->getAirProductsQuery()->select(['lot_id'])->distinct()->column()){
                //Лоты нам не особо интересны, переходим к карточкам
                $airProductsCardsIds = Product::find()
                    ->select(['id'])
                    ->onlyCard()
                    ->byParent($airProductsLotsIds)
                    ->orderBy(['id' => SORT_ASC])
                    ->indexBy('id') //Для дальнейшего поиска, так как isset гораздо быстрее in_array
                    ->column();
            }
        }

        if ($useCache && !empty($airProductsCardsIds)){
            \Yii::$app->cache->set($cacheKey, $airProductsCardsIds, MIN_15);
        }

        return $airProductsCardsIds ?? [];
    }
}
