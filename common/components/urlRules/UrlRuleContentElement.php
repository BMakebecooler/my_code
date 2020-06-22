<?php

namespace common\components\urlRules;

use common\helpers\Product;
use common\models\NewProduct;
use console\controllers\queues\jobs\dicts\PropertyItem;
use skeeks\cms\models\CmsContentElement;
use yii\helpers\ArrayHelper;
use common\helpers\Strings;

/**
 * Class UrlRuleContentElement
 * @package skeeks\cms\components\urlRules
 */
class UrlRuleContentElement extends \yii\web\UrlRule
{

    public function init()
    {
        if ($this->name === null) {
            $this->name = __CLASS__;
        }
    }

    static public $models = [];

    /**
     * @param \yii\web\UrlManager $manager
     * @param string $route
     * @param array $params
     * @return bool|string
     */
    public function createUrl($manager, $route, $params)
    {
        if ($route == 'cms/content-element/view') {
            $contentElement = $this->_getElement($params);

            if (!$contentElement) {
                return false;
            }


            $cmsTree = ArrayHelper::getValue($params, 'cmsTree');
            ArrayHelper::remove($params, 'cmsTree');

            $url = '';

            //Не используем пути к разделам для формирования ссылки на товар
            if ($contentElement->content_id != PRODUCT_CONTENT_ID) {//We need to build on what that particular section of the settings
                if (!$cmsTree) {
                    $cmsTree = $contentElement->cmsTree;
                }

                if ($cmsTree) {
                    $url = $cmsTree->dir . "/";
                }
            } else {
                $url = 'products/';
            }
            $url .= $contentElement->id . '-' . $contentElement->code;

            if (strpos($url, '//') !== false) {

                $url = preg_replace('#/+#', '/', $url);
            }

            /**
             * @see parent::createUrl()
             */
            if ($url !== '') {
                $url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
            }

            /**
             * @see parent::createUrl()
             */
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $url .= '?' . $query;
            }

            //Раздел привязан к сайту, сайт может отличаться от того на котором мы сейчас находимся
            if ($cmsTree && $cmsTree->site) {
                //TODO:: добавить проверку текущего сайта. В случае совпадения возврат локального пути
                if ($cmsTree->site->server_name) {
                    return $cmsTree->site->url . '/' . $url;
                }
            }

            return $url;
        }

        return false;
    }

    /**
     *
     * @param $params
     * @return bool|CmsContentElement
     */
    protected function _getElement(&$params)
    {
        $id = (int)ArrayHelper::getValue($params, 'id');
        $contentElement = ArrayHelper::getValue($params, 'model');

        if (!$id && !$contentElement) {
            return false;
        }

        if ($contentElement && $contentElement instanceof CmsContentElement) {
            self::$models[$contentElement->id] = $contentElement;
        } else {
            /**
             * @var $contentElement CmsContentElement
             */
            if (!$contentElement = ArrayHelper::getValue(self::$models, $id)) {
                $contentElement = CmsContentElement::findOne(['id' => $id]);
                self::$models[$id] = $contentElement;
            }
        }

        ArrayHelper::remove($params, 'id');
        ArrayHelper::remove($params, 'code');
        ArrayHelper::remove($params, 'model');

        return $contentElement;
    }


    protected function checkIsProductPath(string $path)
    {
        $badParts = ['brands', 'search', 'lookbook'];
        $parts = explode('/', $path);
        foreach ($parts as $part) {
            if (in_array($part, $badParts)) {
                return false;
            }
        }
        return true;
    }

    protected function redirect(string $url)
    {
        \Yii::$app->response->redirect($url, 301);
        \Yii::$app->end();
    }

    protected function checkProduct($contentElement)
    {
        if (!is_object($contentElement)) {
            return false;
        }

        if (!$contentElement->id) {
            return false;
        }

        if (!in_array($contentElement->content_id, [CARD_CONTENT_ID, PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID])) {
            return false;
        }

        if($contentElement->active !=='Y'){
            return false;
        }

        return true;
    }

    /**
     * @param \yii\web\UrlManager $manager
     * @param \yii\web\Request $request
     * @return array|bool
     */
    public function parseRequest($manager, $request)
    {
        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (!empty($this->verb) && !in_array($request->getMethod(), $this->verb, true)) {
            return false;
        }

        $pathInfo = $request->getPathInfo();
        if ($this->host !== null) {
            $pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : '/' . $pathInfo);
        }


        $params = $request->getQueryParams();
        $suffix = (string)($this->suffix === null ? $manager->suffix : $this->suffix);
        $treeNode = null;

        if (!$pathInfo) {
            return false;
        }

        if (!preg_match('/\/(?<id>\d+)\-(?<code>\S+)$/i', "/" . $pathInfo, $matches)) {
            return false;
        }


        // иногда прилетают ид модификаций от РР
        $id = $matches['id'];
        $code = trim($matches['code'], " \t\n\r\0\x0B\/");


        $contentElement = \common\lists\Contents::getContentElementById($id);
        if ($contentElement && $contentElement->content_id == OFFERS_CONTENT_ID) {
            $id = $contentElement->parent_content_element_id;
        }

//        $pattern =  '/(products)\/(?<id>\d+)\-(?<code>\S+)\/$/i';
        $pattern = '/(products)\/(?<id>\d+)\-([0-9]{3})\-([0-9]{3})\-([0-9]{3})\/$/i';
        if (preg_match($pattern, $pathInfo)) {
            return [
                'cms/content-element/view',
                [
                    'id' => $id,
                    'code' => $code
                ]
            ];
        }

        $check = $this->checkIsProductPath($pathInfo);

        $flag = null;
        if (0 === strpos($pathInfo, 'products/')) {
            $flag = 'products';
        }
        if (0 === strpos($pathInfo, 'catalog/')) {
            $flag = 'catalog';
        }


        if ($check) {

            $params = $request->getQueryParams();
            $lotNum = Strings::getLotNumFromStr($pathInfo, $flag);

            if ($lotNum) {
                if(0 === strpos($pathInfo,'products/')) {
                    return ['cms/content-element/view', [
                        'id' => $id,
                        'code' => $matches['code']
                    ]];
                }
                //Пытаемся найти в базе корректный id по номеру лота из url
                $elementId = Product::getElementByLotNum($lotNum);
                if ($elementId) {
                    $contentElement = NewProduct::findOne($elementId);
                    if ($this->checkProduct($contentElement)) {
                        $url = $contentElement->getPublicUrl(true, $params, $lotNum);
                        if ($url != $request->getAbsoluteUrl()) {
                            $this->redirect($url);
                        }
                    } else {
                        return false;
                    }
                    //Иначе пытаемся найти в базе номер лота по id из url, в случае если удалось найти товар по id
                } else {

                    $contentElement = NewProduct::findOne($id);
                    if ($this->checkProduct($contentElement)) {
                        $lotNumUrl = $lotNum;
                        $lotNum = Product::getLotNumByElement($id);
                        if (!$lotNum) {
                            $lotNum = $lotNumUrl;
                        }
                        $url = $contentElement->getPublicUrl(true, $params, $lotNum);
                        if ($url != $request->getAbsoluteUrl()) {
                            $this->redirect($url);
                        }
                    } else {
                        return false;
                    }

                }
            } else {
                if ($flag == 'catalog') {
                    return false;
                }
            }
        }
        return [
            'cms/content-element/view',
            [
                'id' => $id,
                'code' => $code
            ]
        ];
    }
}
