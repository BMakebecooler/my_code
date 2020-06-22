<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 22/01/2019
 * Time: 10:18
 */

namespace common\helpers;


use common\components\rbac\CmsManager;
use common\models\Promo;
use common\models\Setting;
use common\helpers\Segment as SegmentHelper;
use Yii;

class CacheHelper
{
    public static $intParams = [
        'page',
        'per-page',
        'tree_id'
    ];

    public static function isEnabled()
    {
        return true;
        return !YII_DEBUG;
    }

    const CACHE_TIME = 60 * 3;
    const CACHE_TIME_CONTENT_ELEMENT = 60 * 3 + 15;
    const CACHE_TIME_INDEX = 60 * 3 + 30;
    const CACHE_TIME_ON_AIR = 60 * 3 + 45;
    const CACHE_TIME_PRODUCT = 60 * 3 + 60;
    const CACHE_TIME_SEARCH = 60 * 3 + 80;
    const CACHE_TIME_TREE = 60 * 3 + 100;
    const CACHE_TIME_PRODUCT_VIEW = 60 * 30 + 170;
    const CACHE_TIME_ONAIR_API = 60 * 10;
    const CACHE_TIME_USER_API = 60 * 15 + 60;
    const CACHE_TIME_PROMO_API = 60 * 1;
    const CACHE_TIME_BRANDS_API = 60 * 30;
    const CACHE_TIME_SLIDER_API = 60 * 15;

    public static function getRandSec()
    {
        if (Setting::getIs999()) {
            return rand(500, 800) * 60;
//            return rand(30, 60) * 60;
        }
        return rand(1, 100);
    }

    //todo  Пока не используется
    public static function getSizeProfileVariation()
    {
        $extendParam = [1235];

        $params = [
            'size_profile_id',
            'id',
            'category',
            'tree_id',
            'ProductSearchProfile',
            'page',
            'per-page',
            'sort',
            'per-page',
            'utm_medium',
            'utm_source',
            'utm_campaign'
        ];

        $getParams = static::getParams($params);

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getTreeViewVariation()
    {
        $extendParam = [12345678901235];
        if (isset(Yii::$app->view->theme->pathMap['@app/views']) && isset(Yii::$app->view->theme->pathMap['@app/views'][0])) {
            $extendParam[] = Yii::$app->view->theme->pathMap['@app/views'][0];
        }

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

//        $getParams = Yii::$app->request->getQueryParams();
        //Для розыгрыша Охота на миллион - доступ только из группы тест
        $userId = User::isAuthorize();
        $extendParam[] = isset($_GET['rate']) && $userId && User::hasRole($userId, CmsManager::ROLE_TEST) ? 'mil_rate_yes' : 'mil_rate_no';


        $params = [
            'per-page',
            'page',
            'sort',
            'id',
            'Search',

            // onair
            'category',
            'date',
            'all',
            'block',
            'time',
            'subcategory',
            'category',

        ];
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $getParams[] = $request->getQueryParam($param);
            }
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getIndexVariation()
    {
        $extendParam = [12345678901235132];
        if (isset(Yii::$app->view->theme->pathMap['@app/views']) && isset(Yii::$app->view->theme->pathMap['@app/views'][0])) {
            $extendParam[] = Yii::$app->view->theme->pathMap['@app/views'][0];
        }

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

//        $getParams = Yii::$app->request->getQueryParams();


        $params = [
//            '',
        ];
        $getParams = [];
        $request = Yii::$app->request;
//        foreach ($params as $param) {
//            if ($request->getQueryParam($param)) {
//                $getParams[] = $request->getQueryParam($param);
//            }
//        }

        return $extendParam;
        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getPomoVariation()
    {
        $extendParam = [123456789012352];
        if (isset(Yii::$app->view->theme->pathMap['@app/views']) && isset(Yii::$app->view->theme->pathMap['@app/views'][0])) {
            $extendParam[] = Yii::$app->view->theme->pathMap['@app/views'][0];
        }

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

//        $getParams = Yii::$app->request->getQueryParams();


        $params = [
            'tree-id',
        ];
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $getParams[] = $request->getQueryParam($param);
            }
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getProductGalleryVariation()
    {
        $extendParam = [123456789012352];
        if (isset(Yii::$app->view->theme->pathMap['@app/views']) && isset(Yii::$app->view->theme->pathMap['@app/views'][0])) {
            $extendParam[] = Yii::$app->view->theme->pathMap['@app/views'][0];
        }

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

//        $getParams = Yii::$app->request->getQueryParams();


        $params = [
            'productId',
            'sizeH',
            'sizeW',
            'productGuid',
            'newTheme'
        ];
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $getParams[] = $request->getQueryParam($param);
            }
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }

    private static function getParams(array $params = [])
    {
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $value = $request->getQueryParam($param);
                if (in_array($param, self::$intParams)) {
                    $value = Strings::onlyInt($value);
                    $value = (int)$value;
                }
                $getParams[] = $value;
            }
        }

        if (SizeProfile::$sizeProfileEnabled) {
            $size_profile_id = \Yii::$app->request->cookies->getValue(SizeProfile::$paramName);
            if ($size_profile_id) {
                $getParams[] = 'size-profile_' . $size_profile_id;
            } else {
                $getParams[] = 'size-profile_empty';
            }

            $size_profile_timestamp = \Yii::$app->request->cookies->getValue(SizeProfile::$paramAdditionalName);
            if ($size_profile_timestamp) {
                $getParams[] = 'size-profile-time_' . $size_profile_timestamp;
            } else {
                $getParams[] = 'size-profile-time_empty';
            }
        }

        return $getParams;
    }

    public static function getCategoryVariation()
    {
        $extendParam = [1234567890123512151];

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

//        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
//        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
//        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

//        $getParams = Yii::$app->request->getQueryParams();


        $params = [
            'size_profile_id',
            'sort',
            'tree_id',
            'slug',
            'ProductSearch',
            'page',
            'per-page',
            'utm_medium',
            'utm_source',
            'utm_campaign'
        ];

        $getParams = static::getParams($params);

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getSlidersViaApiVariation()
    {
        $extendParam = [123];

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $params = [
            'id',
            'identityId'
        ];
        $getParams = static::getParams($params);

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getBrandsViaApiVariation()
    {
        $extendParam = [1234567];

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $params = [
            'category',
            'count'
        ];
        $getParams = static::getParams($params);

        return ArrayHelper::merge($extendParam, $getParams);
    }


    public static function getCategoryViaApiVariation()
    {
        $extendParam = [123456];

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

//        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
//        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
//        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

//        $getParams = Yii::$app->request->getQueryParams();


        $params = [
            'size_profile_id',
            'sort',
            'tree_id',
            'slug',
            'ProductSearch',
            'page',
            'per-page',
            'perPage',
            'order',
            'hideEmpty',
            'branch',
        ];
        $getParams = static::getParams($params);

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getContentElementVariation()
    {
        $extendParam = [12345678901235611];
        if (isset(Yii::$app->view->theme->pathMap['@app/views']) && isset(Yii::$app->view->theme->pathMap['@app/views'][0])) {
            $extendParam[] = Yii::$app->view->theme->pathMap['@app/views'][0];
        }

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

        $params = [
            'id',
            'code',
            'slug'
        ];
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $getParams[] = $request->getQueryParam($param);
            }
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getOnAirVariation()
    {
        $extendParam = [1234567890123551];
        if (isset(Yii::$app->view->theme->pathMap['@app/views']) && isset(Yii::$app->view->theme->pathMap['@app/views'][0])) {
            $extendParam[] = Yii::$app->view->theme->pathMap['@app/views'][0];
        }

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

        $params = [
            'category',
            'date',
            'all',
            'block',
            'time',
        ];
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $getParams[] = $request->getQueryParam($param);
            }
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getBrandsVariation()
    {
        $extendParam = [12345];

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

//        $getParams = Yii::$app->request->getQueryParams();

//        $getParams[] = Yii::$app->request->getQueryParam('q');
//        $getParams[] = Yii::$app->request->getQueryParam('page');

        $params = [
            'code',
            'size_profile_id',
            'category',
            'ProductSearchBrand',
            'page',
            'per-page',
            'sort',
            'per-page',
            'utm_medium',
            'utm_source',
            'utm_campaign'
        ];


        $getParams = static::getParams($params);

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getSearchVariation()
    {
        $extendParam = [12345678901];
        if (isset(Yii::$app->view->theme->pathMap['@app/views']) && isset(Yii::$app->view->theme->pathMap['@app/views'][0])) {
            $extendParam[] = Yii::$app->view->theme->pathMap['@app/views'][0];
        }

        $extendParam[] = \Yii::$app->controller->id;
        $extendParam[] = \Yii::$app->controller->action->id;

        $extendParam[] = Yii::$app->mobileDetect->isDescktop() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isTablet() ? 'yes' : 'no';
        $extendParam[] = Yii::$app->mobileDetect->isMobile() ? 'yes' : 'no';

//        $getParams = Yii::$app->request->getQueryParams();

//        $getParams[] = Yii::$app->request->getQueryParam('q');
//        $getParams[] = Yii::$app->request->getQueryParam('page');

        $params = [
            'size_profile_id',
            'q',
            'ProductSearchSearch',
            'page',
            'per-page',
            'sort',
            'per-page',
            'utm_medium',
            'utm_source',
            'utm_campaign'
        ];

        $getParams = static::getParams($params);

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getImageVariation()
    {
        $extendParam = [1234567890123551123121];

        $params = [
            'image',
            'type'
        ];

        $getParams = static::getParams($params);

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getPromoVariation()
    {
        $extendParam = [123456789012355112312127];

        $params = [
            'size_profile_id',
            'link',
            'slug',
            'category',
            'promo_id',
            'tree_id',
            'ProductSearch',
            'ProductSearchPromo',
            'page',
            'per-page',
            'sort',
            'per-page',
            'utm_medium',
            'utm_source',
            'utm_campaign'
        ];

        $getParams = static::getParams($params);
        $segmentTimestamp = null;

        $slug = \Yii::$app->request->get('slug');
        $promo = Promo::find()->onlyActive()->andWhere(['link' => $slug])->one();

        if ($promo && !$promo->url_link) {
            $segment = $promo->getSegment();
            if ($segment) {
                $segmentTimestamp = $segment->updated_at ? $segment->updated_at : $segment->created_at;
            }
        }

        if ($segmentTimestamp) {
            $getParams[] = 'segment-time_' . $segmentTimestamp;
        } else {
            $getParams[] = 'segment-time_empty';
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getOnAirApiVariation()
    {
        $extendParam = [12345678902];

        $params = [
            'dayId',
            'categoryId',
            'hourId',
        ];
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $getParams[] = $request->getQueryParam($param);
            }
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getProductViaApiVariation()
    {
        $extendParam = [123];
        $getParams = \Yii::$app->request->getQueryParam('id') ? [\Yii::$app->request->getQueryParam('id')] : [];

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getUserViaApiVariation()
    {
        $extendParam = [1231];
        $getParams = \Yii::$app->request->getQueryParam('id') ? [\Yii::$app->request->getQueryParam('id')] : [];

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getPromoViaApiVariation()
    {
        $extendParam = [12341];
        $params = [
            'page',
            'per-page',
        ];
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $getParams[] = $request->getQueryParam($param);
            }
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }

    public static function getPromoBannersViaApiVariation()
    {
        $extendParam = [123411];
        $params = [
            'id',
            'source'
        ];
        $getParams = [];
        $request = Yii::$app->request;
        foreach ($params as $param) {
            if ($request->getQueryParam($param)) {
                $getParams[] = $request->getQueryParam($param);
            }
        }

        return ArrayHelper::merge($extendParam, $getParams);
    }
}