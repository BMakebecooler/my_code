<?php

namespace common\widgets\services;

use modules\shopandshow\models\shop\ShopOrder;
use skeeks\cms\shop\models\ShopBuyer;
use yii\base\Widget;
use common\components\sale\SaleFactory;

class AdmitadWidget extends Widget
{
  public static $_data = [];
  public static $cacheName = '_admitad_uid';

  /**
   * определяет, первый это заказ пользователя или нет
   * @param ShopBuyer $buyer
   * @return string
   */
  public static function isFirstBuy(ShopBuyer $buyer)
  {
    // TODO detect first buy
    return $buyer->getShopOrders()->count() == 1;
  }

  /**
   * Сбор данных после успешного заказа
   * @param ShopOrder $order
   * @param array $basketProducts
   */
  public static function trackCheckout($order, $basketProducts)
  {
      SaleFactory::trackCheckout($order);
//    $campaignID = self::getCampaignID();
//    if ( $campaignID !== null) {
//      $cntProducts = count($basketProducts);
//      foreach ($basketProducts as $key => $product) {
//        /** собираем данные о товарах */
//        self::$_data[] = [
//          'order_id' => $order['id'],
//          'is_first_buy' => self::isFirstBuy($order->buyer),
//          'uid' => $campaignID,
//
//          /** информация о купленном продукте */
//          'price' => $product['price'],
//          'position_id' => $key+1,
//          'position_count' => $cntProducts,
//          'quantity' => intval($product['quantity'])
//        ];
//      }
//    }
  }


  public static function getCampaignID()
  {
    $id = \Yii::$app->session->get(self::$cacheName);
    return $id ? $id : null;
  }

  public static function checkCampaignID()
  {
    $admitad_uid = \Yii::$app->request->get('admitad_uid');
    if ($admitad_uid) {
      \Yii::$app->session->set(self::$cacheName, $admitad_uid);
    }
  }


  public function run()
  {
      SaleFactory::setLabelsData();
    // проверка наличия метки
//    self::checkCampaignID();
//
//    // трекаем собранные данные
//    foreach (self::$_data as $data) {
//      $jsonData = json_encode($data);
//      $this->view->registerJs("sx.Admitad.track($jsonData);");
//    }
  }
}