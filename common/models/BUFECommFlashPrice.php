<?php


namespace common\models;


use Yii;

class BUFECommFlashPrice extends \common\models\generated\models\BUFECommFlashPrice
{
    public static function getDb()
    {
        return Yii::$app->dbStat;
    }

    //На основе аналитики отбирает карточки для которых необходимо установить плашку Выгоды на час
    public static function getCardsIds()
    {
        $cards = [];

        $productsAllQuery = self::find()->select(['OFFCNT_ID'])->where(['>', 'OFFCNT_ID', 0]);

        if ($productsIds = $productsAllQuery->column()){
            $products = Product::find()
                ->select(['id', 'content_id', 'parent_content_element_id'])
                ->where(['kfss_id' => $productsIds])
                ->all();
            
            //В товарах непонятно что за сущности будут
            //Подстаруемся на все случаи
            if ($products){
                foreach ($products AS $product) {
                    switch ($product->content_id){
                        case Product::MOD:
                            $cards[$product->parent_content_element_id] = $product->parent_content_element_id;
                            break;
                        case Product::CARD:
                            $cards[$product->id] = $product->id;
                            break;
                        case Product::LOT:
                            $cardsIds = Product::find()->select(['id'])->onlyCard()->andWhere(['parent_content_element_id' => $product->id])->column();
                            if ($cardsIds){
                                foreach ($cardsIds as $cardId) {
                                    $cards[$cardId] = $cardId;
                                }
                            }
                            break;
                    }
                }
            }
        }

        return $cards;
    }

    //На основе аналитики отбирает лоты для которых необходимо установить плашку Выгоды на час
    public static function getLotIds()
    {
        $lots = [];

        $productsAllQuery = self::find()->select(['OFFCNT_ID'])->where(['>', 'OFFCNT_ID', 0]);

        if ($productsIds = $productsAllQuery->column()){
            $products = Product::find()
                ->select(['id', 'content_id', 'parent_content_element_id'])
                ->where(['kfss_id' => $productsIds])
                ->all();

            //В товарах непонятно что за сущности будут
            //Подстаруемся на все случаи
            if ($products){
                /** @var Product $product */
                foreach ($products AS $product) {
                    switch ($product->content_id){
                        case Product::MOD:
                            if ($lot = $product->getLot()){
                                $lots[$lot->id] = $lot->id;
                            }
                            break;
                        case Product::CARD:
                            $lots[$product->parent_content_element_id] = $product->parent_content_element_id;
                            break;
                        case Product::LOT:
                            $lots[$product->id] = $product->id;
                            break;
                    }
                }
            }
        }

        return $lots;
    }
}