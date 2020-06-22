<?php

namespace common\helpers;

use common\helpers\Product as ProductHelper;
use common\interfaces\Export;
use common\models\NewProduct;
use common\models\Product;
use phpDocumentor\Reflection\Types\Integer;

class Url
{
    const BASE_URL = 'https://shopandshow.ru';

    const URL_PRODUCT_FORMAT = "/products/%s-%s/";

    public static $saleSlug = 'rasprodaja';

    public static $catalogSlug = 'catalog';

    /**
     * Признак главной страницы
     * @return bool
     */
    public static function isMainPageCurrent()
    {
        $tree = \Yii::$app->cms->currentTree;

        return ($tree && $tree->level === 0);
    }

    /**
     * Проверяет что элемент дерева является каталогом
     * @return boolean
     */
    public static function isCatalogTree()
    {
        $tree = \Yii::$app->cms->currentTree;

        return ($tree && $tree->tree_type_id === CATALOG_TREE_TYPE_ID);
    }

    /**
     * Признак закрытого раздела
     * @return bool
     */
    public static function isLockPromoCurrent()
    {
        $tree = \Yii::$app->cms->currentTree;

        return ($tree && $tree->code == 'zakryityiy-razdel');
    }

    /**
     *
     * @return bool
     */
    public static function getRequestUrl()
    {
        return \Yii::$app->urlManager->baseUrl . $_SERVER['REQUEST_URI'];
    }

    /**
     *
     * @return bool
     */
    public static function getBaseUrl()
    {
        return \Yii::$app->urlManager->baseUrl;
    }

    /**
     * @return bool
     */
    public static function isBot()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|curl|dataprovider|search|get|spider|find|java|majesticsEO|google|yahoo|teoma|contaxe|yandex|libwww-perl|facebookexternalhit/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }

        return false;
    }

    public static function getCdnUrl()
    {
        return 'https://static.shopandshow.ru';
    }

    public static function getUploadsImagesPath()
    {
        return '/uploads/images/element/';
    }

    /**
     * @param $url
     * @param null $cdnUrl
     * @return string
     */
    public static function withCdnPrefix($url, $cdnUrl = null)
    {
        if ($cdnUrl === null) {
            $cdnUrl = self::getCdnUrl();
        }

        if (stristr($url, $cdnUrl) === FALSE) {
            $url = ltrim($url, '/');
            return $cdnUrl . '/' . $url;
        }
        return $url;
    }


    public static function createUrlForFeed(NewProduct $product, Export $export)
    {
        $params = [];
        // utm_source=priceru&utm_medium=cpc&utm_campaign=1772908-006-195-434
        if ($export->getNameForCPC() == 'blizko' || $export->getNameForCPC() == 'priceru') {
            $params = [
                'utm_source' => $export->getNameForCPC(),
                'utm_medium' => 'cpc',
                'utm_campaign' => $product->getLotNumber()
            ];
        }

        return $product->getPublicUrl(true, $params);

    }

    public static function createForPagination($perPage)
    {
        $url = \Yii::$app->request->pathInfo;

        $url = '/' . $url . '?' . http_build_query(['per-page' => $perPage]);
        return $url;
    }

    public static function getPaginationUrl(int $pageCurrent, int $pageMax, $mod = 'current')
    {
        $url = \Yii::$app->request->pathInfo;

        $page = 0;

        if ($mod == 'prev') {
            if ($pageCurrent > 1) {
                $page = $pageCurrent - 1;
            }
        } elseif ($mod == 'next') {
            if ($pageCurrent < $pageMax) {
                $page = $pageCurrent + 1;
            }
        } else {
            $page = $pageCurrent;
        }
        if ($page) {
            if ($_GET) {
                $get = $_GET;
                \common\helpers\ArrayHelper::remove($get, 'id');
                \common\helpers\ArrayHelper::remove($get, 'new_theme');
                \common\helpers\ArrayHelper::remove($get, 'page');

                $url = '/' . $url . '?' . http_build_query(array_merge($get, ['page' => $page]));
            } else {
                $url = '/' . $url . '?' . http_build_query(['per-page' => $page]);
            }
            return $url;
        } else {
            return null;
        }
    }

    public static function getPerPageUrl($pp)
    {
        $url = \Yii::$app->request->pathInfo;

        if ($_GET) {
            $get = $_GET;
            \common\helpers\ArrayHelper::remove($get, 'id');
            \common\helpers\ArrayHelper::remove($get, 'new_theme');

            $url = '/' . $url . '?' . http_build_query(array_merge($get, ['per-page' => $pp]));
        } else {
            $url = '/' . $url . '?' . http_build_query(['per-page' => $pp]);
        }

        return $url;
    }

    public static function getSortUrl(string $sort)
    {
        $url = \Yii::$app->request->pathInfo;

        if ($_GET) {
            $get = $_GET;
            \common\helpers\ArrayHelper::remove($get, 'id');
            \common\helpers\ArrayHelper::remove($get, 'new_theme');

            $url = '/' . $url . '?' . http_build_query(array_merge($get, ['sort' => $sort]));
        } else {
            $url = '/' . $url . '?' . http_build_query(['sort' => $sort]);
        }

        return $url;
    }

    public static function prepareSlug($slug)
    {
        $bad = ['=', '&', '?'];
        $return = [];
        $data = explode('/', $slug);
        foreach ($data as $part) {
            $flag = true;
            foreach ($bad as $sign) {
                $pos = strripos($part, $sign);
                if ($pos !== false) {
                    $flag = false;
                }
                if (!preg_match('@[A-z]@u', $part)) {
                    $flag = false;
                }
            }
            if ($flag) {
                $return[] = $part;
            }
        }
        return count($return) ? implode('/', $return) : '';
    }


    public static function preparePromoCategoryUrl($url)
    {
        $search = '/' . \common\helpers\Url::$catalogSlug . '/';
        $url = str_replace($search, '', $url);

        return $url;
    }

    /**
     * Упрощенный метод получения ссылки на карточку товара при генерации фида, чтобы отвязаться от
     * использования лишней модели, экономя ресурсы
     *
     * @param array $product
     * @param string $lotNum
     * @return mixed
     */
    public static function getUrlCardForFeed(array $product, $lotNum)
    {
        //мы знаем, что это передается именно карточка товара
        if ($product['content_id'] == Product::CARD) {
            return self::BASE_URL . sprintf(self::URL_PRODUCT_FORMAT, $product['id'], $lotNum);
        }
        return false;
    }

    /**
     * Упрощенный метод получения ссылки на картинку карточки товара при генерации фида, чтобы отвязаться от
     * использования лишней модели, экономя ресурсы
     *
     * @param array $product
     * @return mixed
     */
    public static function getUrlImageForFeed(array $product)
    {
        //мы знаем, что это передается именно карточка товара
        if ($product['content_id'] == Product::CARD) {
            if ($product['image']) {
                return self::getCdnUrl() . self::getUploadsImagesPath() . $product['image'];
            }
            if ($product['parent_image']) {
                return self::getCdnUrl() . self::getUploadsImagesPath() . $product['parent_image'];
            }

            return \common\helpers\Image::getPhotoDefault();
        }
        return false;
    }

    public static function getProductUrl(Product $model)
    {
        $productId = $model->id;

        if ($model->isLot()) {
            $lotNum = $model->code;

            //ссылки на лот у нас больше быть не должно, так что заменяем ее на ссылку на самую дешевую карточку
            $cardQuery = Product::getProductCardsCanSaleQuery($model->id);
            $cardQuery->orderBy('new_price');

            $card = $cardQuery->one();

            if ($card) {
                $productId = $card->id;
            }
        } else {
            $lot = ProductHelper::getLot($model->id);
            $lotNum = $lot ? $lot->code : $model->code;

            //Если у нас модификация - то в ссылке используем идентификатор родителя (карточки)
            if ($model->isOffer()) {
                $productId = $model->parent_content_element_id;
            }
        }

        return sprintf(self::URL_PRODUCT_FORMAT, $productId, $lotNum);
    }
}