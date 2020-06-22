<?php

namespace common\components\queue\handler\protocolkfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;
use common\models\Product;
use console\jobs\UpdatePriceJob;
use console\jobs\UpdateQuantityJob;
use Yii;
use yii\db\Exception;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 17:30
 */
class ReserveMod extends AbstractHandler implements HandlerInterface
{

    public function execute()
    {
        $guid = $this->data['Guid'];
        $canSell = $this->data['CanSell'];

        /** @var Product $product */
        $product = parent::getProductByGuid($guid);

        $product->new_quantity = max(0, $canSell);

        $affected = $product->updateAttributes(['new_quantity']);

        if (!empty($product->errors)) {
            throw new Exception('Error update new_quantity errors' . print_r($product->errors, true));
        }

        if ($product->parent_content_element_id){
            \Yii::$app->queue->push(new UpdateQuantityJob([
                'id' => $product->parent_content_element_id,
            ]));
        }else{
            \Yii::error("Recalc push offer hasn't parent! offerId='" . var_export($product->id, true) . "'", __METHOD__);
        }


        //Пересчет цен в связи с изменившимися остатками
        if ($product->isOffer()){
            //Временно отключим добавление пересчета из-за лавинообразного добавления и неуспеванием пересчетов
            //TODO Убрать когда будет не актуально
            if(time() > strtotime('2019-11-29 08:20:59')) {
                Yii::$app->queue->push(new UpdatePriceJob([
                    'id' => $product->id,
                ]));
            }
        }else{
            \Yii::error("After reserves price update for not offer! [productId={$product->id} | contentId={$product->content_id}} | {$product->name}]", __METHOD__);
        }

        return true;

        $product->new_quantity = max(0, $canSell); //Отрицапельные остатки нам не подходят, это идентично отсутствиж товара
        // todo wht ? because meta_title is not empty why?
        if (!$product->save(false)) {
            throw new Exception('Error save new_quantity errors' . print_r($product->errors, true));
        } else {
            //Пересчет остатков в плоской таблице, актуален пересчет только родителей
            if ($product->parent_content_element_id) {
                if (\common\helpers\App::isConsoleApplication()) {
                    echo "UpdateQuantityJob Push for id={$product->parent_content_element_id}" . PHP_EOL;
                }

                try {
                    \Yii::$app->queueProduct->push(new UpdateQuantityJob([
                        'id' => $product->parent_content_element_id,
                    ]));
                } catch (Exception $e) {
                    \Yii::error(var_export($e->getMessage(), true), __METHOD__);
                }
            }
        }

        return true;
    }
}