<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 26/12/2018
 * Time: 20:03
 */

namespace modules\api\resource;


use common\lists\TreeList;
use common\models\Brand;
use common\models\Tree;
use skeeks\cms\models\CmsTree;
use common\helpers\Url;


class Category extends Tree
{

    public $extendedMenuItem = false;

    public function init()
    {
        //* Бренды, популярные по разделам *//
        //TODO Так себе вариант, использован в качестве быстрого варианта
        if (!Brand::$popularByTree) {
            Brand::$popularByTree = \common\models\Brand::getPopularByTree(['only_can_sale' => true]);
        }

        parent::init();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['extendedMenuItem'], 'integer'],
        ]);
    }

    public function fields()
    {

        return [


            'id',
            'name',
            'slug' => function () {
                $code = $this->code;
                if ($this->redirect_tree_id && ($sourceTree = self::findOne($this->redirect_tree_id))) {
                    $code = $sourceTree->code;
                }
                return $code;
            },
            'parent' => function () {
                return $this->pid == TreeList::CATALOG_ID ? 0 : $this->pid;
            },
            'description' => function () {
                return htmlentities($this->description_short);
            },
            'extendedMenuItem' => function () {
                $result = '';

                if ($this->extendedMenuItem) {

                    $popularBrands = !empty(Brand::$popularByTree[$this->id]) ?
                        array_slice(Brand::$popularByTree[$this->id], 0, Brand::MAIN_MENU_LIMIT) : [];

                    $popularBrandsHtml = '';

                    if ($popularBrands) {

                        $popularBrandsItemsHtml = '';
                        /** @var \common\models\Brand $popularBrand */
                        foreach ($popularBrands as $popularBrand) {
                            $brandUrl = $popularBrand->getUrl();
                            $popularBrandsItemsHtml .= <<<HTML
                        <a href="{$brandUrl}" class="btn btn-outline-primary m-1">{$popularBrand->name}</a>
HTML;
                        }

                        $popularBrandsHtml .= <<<HTML
                                        <div class="col-12 navigation_brands_filters mb-3 d-flex flex-wrap justify-content-start">
                                            <a href="{$this->url}" class="btn btn-torch-red m-1">Смотреть все</a>
                                            
                                            {$popularBrandsItemsHtml}
                    </div>
HTML;

                        $result .= $popularBrandsHtml;
                    }

                    $node = $this;

                    $relatedProductsHtml = \Yii::$app->cache->getOrSet(
                        "tree_{$this->id}_mobile_nav_related_products",
                        function () use ($node){
                            $relatedProducts = \common\models\CmsTree::getRelatedProductsForTopNav($node->id, 2);

                            $relatedProductsHtml = '';
                            if ($relatedProducts && count($relatedProducts) >= 2){
                                foreach ($relatedProducts as $index => $product) {
                                    $relatedProductsHtml .= \Yii::$app->controller->renderPartial('@theme_common/parts/_item', [
                                        'model' => $product,
                                        'ctsRelated' => false,
                                        'index' => ++$index,
                                        'productItemWrapperClass' => 'main-nav_product-item',
                                    ]);
                                }
                            }

                            return $relatedProductsHtml;
                        },
                        MIN_15
                    );

                    if ($relatedProductsHtml) {
                        $result = <<<HTML
                                        {$popularBrandsHtml}

                                        <div class="col-12 d-flex justify-content-start">

                                            {$relatedProductsHtml}

                                        </div>
                                        <a href="{$this->url}" class="btn btn-outline-primary col mb-3">
                                            Cмотреть все
                                            <svg class="svg-icon small d-inline ml-2 h4 mb-0">
                                                <use xlink:href="#l-arrow-right"></use>
                                            </svg>
                                        </a>
HTML;

                    }

                }

                return $result;
            },
            'display' => function () {
                return 'default';
            },
            'menu_order' => function () {
                return $this->priority;
            },
            'count' => function () {
                return $this->count_content_element;
            },
            'image' => function () {
                $catalogLogo = [];
                //Очень ресурсоемко, приводит к ошибкам
//                if ($catalogLogoSrc = $this->relatedPropertiesModel->getAttribute('catalogLogo')) {
//                    $catalogLogo = ['src' => Url::getCdnUrl() . $catalogLogoSrc];
//                }

                return $catalogLogo;
            },
            '_links' => function () {
                $dir = "/{$this->dir}/";

                if ($this->redirect_tree_id && ($sourceTree = self::findOne($this->redirect_tree_id))) {
                    $dir = "/{$sourceTree->dir}/";
                } elseif ($this->redirect) {
                    $dir = $this->redirect;
                }

                return [
                    'self' => [
                        'href' => $dir
                    ]
                ];
            },
        ];
    }
}