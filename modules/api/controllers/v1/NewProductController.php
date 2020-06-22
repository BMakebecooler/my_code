<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 27/12/2018
 * Time: 11:21
 */

namespace modules\api\controllers\v1;

use common\models\search\ProductSearch;
use modules\api\controllers\ActiveController;
use modules\api\resource\NewProduct;
use modules\api\resource\Product;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\lists\Products;
use modules\shopandshow\lists\Shares;
use modules\shopandshow\models\shares\SsShare;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class NewProductController extends ActiveController
{

    public $modelClass = NewProduct::class;

//    public function actions()
//    {
//        return [];
//        return [
//            'index' => [
//                'class' => 'yii\rest\IndexAction',
//                'modelClass' => $this->modelClass,
//                'prepareDataProvider' => [$this, 'prepareDataProvider']
//            ]
//        ];
//    }


public function actionView($id){
    return '{
  "id": 8445878,
  "name": "Смартфон Irbis SP402",
  "image": "https://static.shopandshow.ru/uploads/images/element/8a/37/40/8a37408d1ba48ee05a0ff7f32ee1ec2b/sx-filter__common-thumbnails-Thumbnail/175e21d390625df9c6fb834cab2774a4/007-063-715.jpg?w=208&h=208",
  "url": "/products/8445878-007-063-715/",
  "thumbnails": [
    "https://static.shopandshow.ru/uploads/images/element/2e/9e/21/2e9e212e9c2d2a398be097eb0743fc49/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/7f/62/c6/7f62c6e7cb77c8fcd591da29656db2f4/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/86/15/d9/8615d9cf6dafd0be273bdc705f0e7ab6/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/b4/c5/fc/b4c5fc4fad0489f0de8eb91b7c949acb/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/bb/e4/8c/bbe48c98fc73d88dd30771d1a5c85f7d/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/b0/7e/bd/b07ebd042c0fe4ce392124f118d3dee3/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/5a/cb/cd/5acbcd9b569009a968a591540eea7aa0/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/59/18/6a/59186a3c8a9db6510d629d4323928e05/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/8c/98/6b/8c986bd5fb796ed634a3a9d5c7ebd817/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/85/b4/6c/85b46c98c4ccc7dd7ae57e2474f703fc/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/e2/b6/90/e2b6900d0fc1b0e912b7776bd7a9b8e7/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54",
    "https://static.shopandshow.ru/uploads/images/element/e5/bf/f1/e5bff100d419092dfc1d0bb007c95e75/sx-filter__common-thumbnails-Thumbnail/80202e4ca5b85b8e87a9bfa31aa609da/sx-file.jpg?w=54&h=54"
  ],
  "price": {
    "current": "2&nbsp;999",
    "old": "3&nbsp;500"
  },
  "discount": 41,
  "rating": {
    "value": 2.5,
    "max": 5,
    "step": 1
  },
  "badge": 2,
  "attributes": [
    {
      "id": 0,
      "name": "Размер",
      "options": [
        "36",
        "38",
        "39",
        "40"
      ]
    }
  ]
}
';
}

}