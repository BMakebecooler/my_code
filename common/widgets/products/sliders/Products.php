<?php

namespace common\widgets\products\sliders;

use common\lists\TreeList;
use common\models\Tree;
use common\widgets\content\ContentElementWidget;
use modules\shopandshow\models\shop\ShopContentElement;
use skeeks\cms\base\Widget;
use skeeks\cms\components\Cms;

/**
 * Class Products
 * @package common\widgets\products\sliders
 */
class Products extends Widget
{

    public $viewFile = '@template/widgets/ContentElementsCms/sliders/products';

    public $treeDir = null;
    public $limitProduct = 10;
    public $title = null;

    public $activeQueryCallback = null;

    /**
     * @var Tree
     */
    public $tree = null;

    public function init()
    {
        parent::init();

        if ($this->treeDir) {
            $this->tree = TreeList::getTreeByDir($this->treeDir);
            $this->title = ($this->tree) ? $this->tree->name : '';
        }
    }

    public function run()
    {
        return $this->render($this->viewFile);
    }


    public function getProducts()
    {
        if ($this->tree) {
            return $this->getProductsByTree();
        }

        return [];
    }

    /**
     * @return array|ContentElementWidget|\yii\db\ActiveRecord[]
     */
    protected function getProductsByTree()
    {
        return new ContentElementWidget([
            'namespace' => 'ContentElementsCmsWidget-shik-products-0.5-' . $this->treeDir,
            'viewFile' => '@template/widgets/ContentElementsCms/products/products-items',
            'contentElementClass' => ShopContentElement::className(),
            'active' => Cms::BOOL_Y,

            'enabledRunCache' => Cms::BOOL_Y,
            'runCacheDuration' => HOUR_2,
            'groupBy' => false,
            'enabledCurrentTree' => false,
            'enabledPjaxPagination' => Cms::BOOL_N,
            'enabledActiveTime' => Cms::BOOL_N,
            'pageSize' => $this->limitProduct,
            'content_ids' => [PRODUCT_CONTENT_ID],
            'pageSizeLimitMax' => $this->limitProduct,
            'dataProviderCallback' => function (\yii\data\ActiveDataProvider $activeDataProvider) {
                $query = $activeDataProvider->query;

                $query->andWhere(['and',
                    ['tree_id' => TreeList::getDescendants($this->tree)]
                ]);

                if ($this->activeQueryCallback && is_callable($this->activeQueryCallback)) {
                    /** @var callable $callback */
                    $callback = $this->activeQueryCallback;
                    $callback($query);
                }
            },
        ]);
    }


}