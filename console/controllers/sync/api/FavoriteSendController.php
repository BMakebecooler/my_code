<?php

/**
 * php ./yii sync/api/shares
 */

namespace console\controllers\sync\api;

use common\components\email\services\RetailRocket;
use modules\shopandshow\models\shop\ShopFuserFavorite;
use \yii\httpclient\Client as HTTPClient;
use yii\db\Expression;

/**
 * Class FavoriteSendController
 * @package console\controllers
 */
class FavoriteSendController extends \yii\console\Controller
{

  public function init()
  {

    parent::init();

  }
  function _preformat(&$items) {
    $arUsers = [];
    foreach ($items as $favorite) {

      $email = $favorite->shopFuser->user->email;
      $arUsers[$email][] = $favorite->shop_product_id;
    }
    return $arUsers;
  }


  function actionIndex() {

    $arItems = $this->getNewFavorites();
    $arUsers = $this->_preformat($arItems);
    if (count($arUsers) > 0) {
      foreach ($arUsers as $email => $newFavoritesIDs) {
        if ( !count($newFavoritesIDs) ) continue;
        \Yii::$app->retailRocketService->sendEmailWithTemplate($email, 'add_favorite', [
          'item_ids' => implode(',', $newFavoritesIDs)
        ]);
      }
    }
  }

  function getNewFavorites() {
    // агент будет запускаться каждые 30 минут
    $interval = MIN_30;

    return ShopFuserFavorite::find()
      ->andWhere(['>=', 'created_at', new Expression("UNIX_TIMESTAMP(NOW() - INTERVAL {$interval} SECOND)")])
      ->all();
  }

}