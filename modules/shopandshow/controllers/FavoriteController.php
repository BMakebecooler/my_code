<?php

namespace modules\shopandshow\controllers;

use modules\shopandshow\lists\Favorite;
use modules\shopandshow\models\shop\ShopFuserFavorite;
use skeeks\cms\base\Controller;
use skeeks\cms\helpers\RequestResponse;
use Yii;

/**
 * Class FavoriteController
 * @package modules\shopandshow\controllers
 */
class FavoriteController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        return $this->render($this->action->id);
    }

    public function actionChange()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost() && $productId = Yii::$app->request->post('product_id')) {
            ShopFuserFavorite::changeFavorite($productId);
            $rr->success = true;
        } else {
            $rr->success = false;
        }

        return $rr;

    }

    /**
     * @return RequestResponse
     */
    public function actionGetMyFavorite()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $favorites = Favorite::getMyFavoritesFind()->asArray()->all();

            $result = [];

            foreach ($favorites as $favorite) {
                $result[] = $favorite['id'];
            }

            $rr->success = true;
            $rr->data = $result;
        } else {
            $rr->success = false;
        }

        return $rr;
    }

}