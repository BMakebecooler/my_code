<?php

namespace modules\shopandshow\models\newEntities\shop;

use common\helpers\Msg;
use console\controllers\queues\jobs\Job;
use console\jobs\UpdatePriceJob;
use console\jobs\UpdateQuantityJob;
use modules\shopandshow\lists\Guids;
use Yii;

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 10.11.17
 * Time: 12:57
 */
class ShopProduct extends \modules\shopandshow\models\shop\ShopProduct
{

    public $guid;
    public $quantity;
    public $quantity_reserved;

    public function addData()
    {
        if ($this->guid) {

            /** @var \common\models\cmsContent\CmsContentElement $product */
            if (!$product = Guids::getEntityByGuid($this->guid)) {
                Job::dump('Нет товара по этому гуиду, не смогу проставить кол-во');

                return false;
            }

            $query = "UPDATE shop_product SET quantity = :quantity, quantity_reserved = :quantity_reserved WHERE id = :id";

            $affected = \Yii::$app->db->createCommand($query,
                [':quantity' => $this->quantity, ':quantity_reserved' => $this->quantity_reserved, ':id' => $product->id]
            )->execute();

            Job::dump("affected ".$affected);

            if ($affected) {
                $this->recalcQuantity($product);
            }
            else {
                Job::dump($query);
                Job::dump("quantity={$this->quantity}, id={$product->id}");
            }

            //[DEPRECATED дубль! Этот же пересчет добавляется в common/components/queue/handler/protocolkfss/v20/ReserveMod.php]
            //Пересчет цен в связи с изменившимися остатками
            if(false) {
                Yii::$app->queue->push(new UpdatePriceJob([
                    'id' => $product->id,
                ]));
            }

            return true;
        }

        Job::dump('guid empty');
        return false;
    }

    /**
     * @param \common\models\cmsContent\CmsContentElement|\skeeks\cms\models\CmsContentElement $product
     *
     * см.так же console/controllers/kfss/ItemsController.php :: updateShopProductQuantity(..)
     *
     * @throws \yii\db\Exception
     */
    protected function recalcQuantity($product)
    {
        if ($product->parent_content_element_id) {
            //ДЛЯ ПОДСЧЕТА В ТОВАР->КАРТОЧКА
            //Если родитель не база - суммируем все (это будут тоже не базы).
            //Если родитель база - то проверяем кол-во элементов, если ли только один (база) - берем число отсюда, если еще что есть - беремчисло из простых товаров
            //
            //ДЛЯ ПОДСЧЕТА В КАРТОЧКА->ЛОТ
            //Проверям кол-во карточек, если больше одной - берем обычное кол-во, если одна - берем базу.
            $queryProducts
                = "UPDATE shop_product t1
                    INNER JOIN (
                            sum(CASE WHEN ce.is_base!='Y' AND t.quantity > 0 THEN t.quantity ELSE 0 END) AS sum_quantity,
                            sum(CASE WHEN ce.is_base='Y' AND t.quantity > 0 THEN t.quantity ELSE 0 END) AS sum_quantity_base,
                            ce.parent_content_element_id AS element_id,
                            ce.content_id,
                            parent_element.is_base       AS parent_is_base,
                            COUNT(1) AS num
                        FROM cms_content_element ce, shop_product t, cms_content_element AS parent_element
                        WHERE
                            ce.content_id = :content_id
                            AND ce.tree_id IS NOT NULL
                            AND ce.active = 'Y'
                            AND ce.parent_content_element_id = :id
                            AND parent_element.id = ce.parent_content_element_id
                            AND t.id = ce.id
                            GROUP BY ce.parent_content_element_id
                    ) t2 ON t2.element_id=t1.id
                    SET t1.quantity = IF(
    t2.content_id = 10,
    (
      IF(
          t2.parent_is_base = 'Y' AND t2.num = 1,
          t2.sum_quantity_base,
          t2.sum_quantity
      )
    ),
    (
      IF(
          t2.num > 1,
          t2.sum_quantity,
          t2.sum_quantity_base
      )
    )
)";
            $affected = \Yii::$app->db->createCommand($queryProducts, [':id' => $product->parent_content_element_id, ':content_id' => $product->content_id])->execute();
            Job::dump("affected parent ".$affected." [parent_content_element_id = {$product->parent_content_element_id}, content_id = {$product->content_id}]");

            if ($affected) {
                $this->recalcQuantity($product->parentContentElement);
            }
            else {
                Job::dump($queryProducts);
            }


            //* Проверка на 0 в остатках лота *//
            /*
            //Сохранение данных лота - если пересчитываются карточки товара
            if ($product->content_id == CARD_CONTENT_ID){
                //Получим текущие остатки в лоте
                $lotId = $product->parent_content_element_id;
                $productForCheck = ShopProduct::findOne(['id' => $lotId]);
                if($productForCheck){
                    if (!$productForCheck->quantity){
                        //Остатков нет - собираем инфу
                        \Yii::error(date("Y-m-d H:i:s [U]") . " Лот {$product->parentContentElement->name} [{$lotId}], остатки = '{$productForCheck->quantity}'. //SP");
                    }
                }else{
                    \Yii::error(date("Y-m-d H:i:s [U]") . " Лот {$product->parentContentElement->name} [{$lotId}], НЕТ ShopProduct!. //SP");
                }
            }
            */
            //* /Проверка на 0 в остатках лота *//
        }
    }
}