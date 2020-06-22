<?php

namespace modules\shopandshow\controllers;

use skeeks\cms\base\Controller;
use yii\db\Exception;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\web\Response;

/**
 * Интеграция с сервисом Admitad
 * Class AdmitadController
 * @package modules\shopandshow\controllers
 */
class AdmitadController extends Controller {

  static $TARIFFS = [
    'NEW_CLIENT' => 1,
    'OLD_CLIENT' => 2
  ];
  static $ACTIONS = [
    'ORDER_CONFIRM' => 1
  ];

  static $KEYS = [
    'CAMPAIGN_CODE' => 'b77fa81450',
    'SECRET' => '04A22B59CCAb5Ac836150a0bc2b1a148'
  ];

  private $endpoint = 'https://ad.admitad.com/r';

  private function generateParams($data) {
    $data = Json::decode($data);
    $tariff = self::$TARIFFS[ $data['is_first_buy'] ? 'NEW_CLIENT' : 'OLD_CLIENT'];
    return array_merge($data, [
      'postback' => 1,
      'postback_key' => self::$KEYS['SECRET'],
      'campaign_code' => self::$KEYS['CAMPAIGN_CODE'],
      'action_code' => self::$ACTIONS['ORDER_CONFIRM'],
      'tariff_code' => $tariff,
      'payment_type' => 'sale',
      'currency_code' => 'RUB',
    ]);
  }

  function actionTrack() {
//    header('Content-Type: application/json');
//    \Yii::$app->response->format = Response::FORMAT_JSON;
//
//    if ( \Yii::$app->request->isAjax ) {
//
//      $client = new Client();
//
//      $params = $this->generateParams(\Yii::$app->request->getRawBody());
//      /** @var \yii\httpclient\Response $response */
//      try {
//        $client->createRequest()
//          ->setMethod('POST')
//          ->setUrl($this->endpoint . '?' . http_build_query($params) )
//          ->send();
//
//      } catch (Exception $e) {
//        return Json::encode([
//          'status' => false,
//          'message' => $e->getMessage()
//        ]);
//      }
//
//      echo Json::encode([
////        'data' => $params, // for debug
//        'status' => true,
//      ]);
//    }
  }

}