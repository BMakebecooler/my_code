<?php

namespace common\components;

use common\lists\Contents;
use common\models\Tree;
use modules\shopandshow\models\tools\Redirect;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsContentProperty;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\helpers\Url;
use yii\web\Application;

/**
 * Редирректы со старой версии сайта
 * Class SaSRedirect
 * @package common\components
 */
class SaSRedirect extends Component implements BootstrapInterface
{

    /**
     * Текущий урл
     * @var
     */
    private $url;

    public function bootstrap($application)
    {
        if ($application instanceof Application) {

            $this->url = \Yii::$app->request->url;

            \Yii::$app->on(Application::EVENT_BEFORE_ACTION, function ($e) {
                // сео редиректы только для гет реквестов
                if (\Yii::$app->request->getIsGet()) {
                    $this->seoRedirect();
                }

                $this->imagingErrorRedirect();
                $this->productRedirect();
                $this->catalogRedirect();
                $this->promoRedirects();
                $this->ssCatalogRedirects();
            });
        }
    }

    public function seoRedirect()
    {

        if (preg_match('/\/[^\~].*index\.(php|html?)$/i', $this->url)) {
            // редирект /url/index.php -> /url/
            $clearUrl = preg_replace('/index\.(php|html?)$/i', '', $this->url);
            \Yii::$app->getResponse()->redirect($clearUrl, 301);
        }

        // не  работает ссылка вида  https://shopandshow.ru/promo/999/?page=12
//        if (strpos($this->url, 'page=1')) {
//            // редирект ?page=1 на себя же без параметра page
//            $url = \Yii::$app->request->url;
//            $clearUrl = trim(preg_replace('/page\=1&?/', '', $url), '?&');
//            \Yii::$app->getResponse()->redirect($clearUrl, 301);
//        }

        if (strpos($this->url, '//') !== FALSE) {
            // редирект двойных+ слешей на нормальные
            $clearUrl = preg_replace('/^(.*?)\/\/+(.*?)$/i', '$1/$2', $this->url);
            \Yii::$app->getResponse()->redirect($clearUrl, 301);
        }

    }

    public function imagingErrorRedirect()
    {
        // постоянно кто-то запрашивает картинки с таким кодом, из-за чего летит гора ошибок
        if (substr_count($this->url, '/1KBgfRujYdktKMXc5nk8ipCXin9fdoqEV/')) {
            \Yii::$app->response->redirect('/');

        }
    }

    public function productRedirect()
    {

        $lotNum = null;

        $isImages = substr_count($this->url, '/images/');

        if (!$isImages && preg_match_all('/\/(\d{3}-\d{3}-\d{3})/', $this->url, $matches)) {
            if (isset($matches[1][0])) {
                $lotNum = $matches[1][0];
            }
        }

        if (!$lotNum && !$isImages && preg_match_all('/\/(\d{3}-\d{3})/', $this->url, $matches)) {
            if (isset($matches[1][0])) {
                $lotNum = $matches[1][0];
            }
        }

        if ($lotNum) {
            $query = CmsContentElementProperty::find()
                ->leftJoin(['prop' => CmsContentProperty::tableName()], 'prop.id = cms_content_element_property.property_id')
                ->where(['prop.code' => 'LOT_NUM'])
                ->andWhere(['cms_content_element_property.value' => $lotNum]);

            /**
             * @var $cmsElementProperty CmsContentElementProperty
             */
            if ($cmsElementProperty = $query->one()) {
                \Yii::$app->response->redirect($cmsElementProperty->element->url);
            }
        }
    }

    public function catalogRedirect()
    {
        if (preg_match_all('/ss\/catalog\/([a-zA-Z-]+)(\/)/i', $this->url, $matches)) {
            if (isset($matches[1][0])) {
                $treeCode = $matches[1][0];

                $query = Tree::find()
                    ->where(['cms_tree.code' => $treeCode])
                    ->andWhere(['cms_tree.tree_type_id' => CATALOG_TREE_TYPE_ID]);

                /**
                 * @var $tree Tree
                 */
                if ($tree = $query->one()) {
                    \Yii::$app->response->redirect($tree->url);
                }
            }
        }

        /**
         * Перенаправляем старые промо урлы на главную
         */
        if (substr_count($this->url, '/promo/') && isset($_GET['id']) && isset($_GET['code'])
            && !substr_count($this->url, 'landings')
            && !substr_count($this->url, 'stock')
        ) {

            $code = str_replace('/', '', $_GET['code']);
            $contentElement = Contents::getContentElementByCode($code, PROMO_CONTENT_ID);

            if (!$contentElement) {
                \Yii::$app->response->redirect('/promo/sales/');
            }
        }
    }

    public function promoRedirects()
    {
        //Сегодня в эфире /ss/promo/onair/ +
        //Распродажа /ss/promo/sales/
        //Расписание эфиров /ss/schedule/

        if (substr_count($this->url, '/ss/promo/onair')) {
            $url = Url::to('/onair');
            \Yii::$app->response->redirect($url);
        }

        if (substr_count($this->url, '/ss/promo/sales')) {
            echo '<pre>';
            debug_print_backtrace();
            echo '</pre>';
            die();
            $url = Url::to('/promo/sales/');
            \Yii::$app->response->redirect($url);
        }

        if (substr_count($this->url, '/ss/schedule')) {
            $url = Url::to('/onair');
            \Yii::$app->response->redirect($url);
        }

        if (substr_count($this->url, '/ss/lookbook')) {
            $url = Url::to('/lookbook');
            \Yii::$app->response->redirect($url);
        }

        if (substr_count($this->url, '/to-cts')) {

            $productCts = Contents::getCtsProduct();

            /**
             * @var $productCts \modules\shopandshow\models\shop\ShopContentElement
             */
            if ($productCts) {

                $this->redirect($productCts->absoluteUrl, 302);

//                \Yii::$app->response->redirect($productCts->absoluteUrl);
            }
        }

        //Для исправления косячных урлов
        if (substr_count($this->url, '/promo/1000599-02_04_18_ukrasheniya/?bid=5341&utm_source=email&utm_medium=email_cts&utm_campaign=20180402')) {
            $url = Url::to('/promo/rasprodaja-ukrasheniy_02_04_18/?bid=5319&utm_source=email&utm_medium=email_cts&utm_campaign=20180402');
            \Yii::$app->response->redirect($url);
        }
    }

    /**
     * До тех пор пока не разберемся почему перестали работать YII-шные редиректы
     * @param $url
     * @param int $code
     */
    private function redirect($url, $code = 301)
    {
        if ($code == 301) {
            header("HTTP/1.1 301 Moved Permanently");
        } elseif ($code == 302) {
            header("HTTP/1.1 302 Moved Temporarily");
        }

        header("Location: {$url}");
        die;
    }

    /**
     * Редиректы из инструмента "редиректов" в админ панели
     * @return bool
     */
    private function ssCatalogRedirects()
    {
        $request = \Yii::$app->request;
//        $url = '/' . $request->getPathInfo();
        $url = '/' . preg_replace("/\/$/", '', $request->getPathInfo());

        /**
         * Убрать это условие если понадобиться чтобы инструмент редиректов работал глобально (а не только в каталоге)
         */
        if (!substr_count($url, 'catalog')) {
            return false;
        }

        /**
         * @var $redirect Redirect
         */
        $db = \Yii::$app->db;
        $redirect = $db->cache(function ($db) use ($url) {
            return Redirect::find()->andWhere(['from' => $url])->one();
        }, HOUR_8);

        if ($redirect) {
            $this->redirect($redirect->to);
        }

        return true;
    }
}
