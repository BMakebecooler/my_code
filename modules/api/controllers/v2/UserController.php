<?php


namespace modules\api\controllers\v2;


use common\components\cache\PageCache;
use common\helpers\CacheHelper;
use common\helpers\Strings;
use common\helpers\User;
use yii\filters\Cors;
use yii\rest\Controller;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
                ],
            ],
//            [
//                'class' => PageCache::class,
//                'duration' => CacheHelper::CACHE_TIME_USER_API,
//                'variations' => CacheHelper::getUserViaApiVariation(),
//                'enabled' => CacheHelper::isEnabled()
//            ],
        ];
    }

    public function actionIndex()
    {
        return \common\helpers\User::getAuthorizeId();
    }

    public function actionAuthCheck()
    {

        $isGuest = \Yii::$app->user->isGuest;
        $user = \Yii::$app->user->identity;

        $data = [
            'phone' => User::isAuthorize() && !empty($user->phone) ? Strings::formatPhone($user->phone) : '',
            'id' => $isGuest ? '' : $user->id,
        ];

        $message = $isGuest ? 'Не авторизован' : 'Авторизован';

        $html = $this->renderPartial('@frontend/themes/v3/layouts/parts/_cabinet', [
            'isGuest' => $isGuest,
            'user' => $user
        ]);

        return [
            'status' => !\Yii::$app->user->isGuest,
            'data' => $data,
            'html' => $html,
            'message' => $message
        ];
    }

    public function actionFavoriteCount()
    {
        return [
            'count' => \Yii::$app->shop->favoritesCount,
        ];
    }
}
