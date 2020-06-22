<?php
/**
 * Gift2019Controller
 *
 */
namespace modules\shopandshow\controllers;

use common\widgets\content\ShowMoreWidget;
use common\widgets\content\ContentElementWidget;
use modules\shopandshow\models\shop\ShopContentElement;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\base\Controller;
use yii\db\Query;

/**
 * Class Gift2019Controller
 *
 * подсчет подарков на новый год 2019
 */
class Gift2019Controller extends Controller
{

  const PRODUCTS_PER_PAGE = 40;
  private $valid = [
    'age' => ['age_lt_14', 'age_lt_30', 'age_eq_30', 'age_lt_50', 'age_gt_50'],
    'choose-sex' => ['man', 'woman']
  ];

  function parseFilters($fields) {

    $filter = [];
    $interest = ['or'];
    foreach ($fields['choose-interest'] as $name) {
      $interest[] = ["interest_$name" => true];
    }
    switch ($fields['choose-sex']) {
      case "man":
        $filter['is_man'] = true;
        break;
      case "woman":
        $filter['is_woman'] = true;
        break;
    }
    $filter[$fields['age']] = true;
    return [
      'interest' => $interest,
      'filter' => $filter
    ];
  }

  function validate($fields) {
    $errors = [];
    if (!$fields['price']) {
      $errors[] = 'price';
    }
    if ( !is_array($fields['choose-interest']) ) {
      $errors[] = 'interest';
    }
    if ( !in_array($fields['choose-sex'], $this->valid['choose-sex']) ) {
      $errors[] = 'choose-sex';
    }
    if ( !in_array($fields['age'], $this->valid['age']) ) {
      $errors[] = 'age';
    }
    return count($errors) > 0 ? $errors : false;
  }

  public function actionList() {

    $result = [];
    $arRequest = \Yii::$app->request->get();

    /** валидатор ошибок */
    $errors = $this->validate($arRequest);
    if ( $errors !== false ) {
      return $this->json(['error' => $errors], 400);
    }

    /** фильтры из реквеста */
    $giftFilters = $this->parseFilters($arRequest);

    /** Получения списка категорий для фильтрации */
    $categoryIDs = (new Query())
      ->select(['cms_tree_id'])
      ->from('{{%ss_gift_2019}}')
      ->where($giftFilters['filter'])
      ->andWhere($giftFilters['interest'])
      ->all();

    $arCmsTreeIDs = array_map(function($c) {return $c['cms_tree_id'];}, $categoryIDs);

    $isMob = \Yii::$app->mobileDetect->isMobile() || \Yii::$app->mobileDetect->isTablet();
    $productList = new ContentElementWidget([
      'contentElementClass' => ShopContentElement::className(),
      'namespace' => 'ContentElementsCmsWidget-catalog-products-v1'.($isMob ? '-mobile' : ''),
      'viewFile' => '@template/widgets/ContentElementsCms/products/catalog-infinite',
      'active' => Cms::BOOL_Y,
      'enabledRunCache' => Cms::BOOL_Y,
      'enabledActiveTime' => Cms::BOOL_Y,
      'runCacheDuration' => ContentElementWidget::getRunCacheDuration(),
//        'runCacheDuration' => MIN_15,
      'pageSize' => self::PRODUCTS_PER_PAGE,
      'enabledCurrentTree' => false,
      'content_ids' => [PRODUCT_CONTENT_ID],
      'groupBy' => false,
      'orderBy' => false,
      'dataProviderCallback' => function (\yii\data\ActiveDataProvider $activeDataProvider) use ($arCmsTreeIDs, $arRequest, &$result) {

        $query = $activeDataProvider->query;

        $query->andWhere(['cms_content_element.tree_id' => $arCmsTreeIDs]);
        $query->andWhere(['<', 'price', $arRequest['price']]);
        $result['count'] = $query->count();
      }
    ]);
    $result['template'] = $productList->run();

    return $this->json($result);

  }


  private function json($data, $code = 200) {
    header('Content-Type: application/json');
    \Yii::$app->response->format = \Yii\web\Response::FORMAT_JSON;
    echo json_encode([
      'code' => $code,
      'status' => $code === 200,
      'data' => $data
    ],JSON_PRETTY_PRINT);
  }
}