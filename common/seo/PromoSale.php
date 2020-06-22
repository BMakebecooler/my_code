<?php

namespace common\seo;

use common\lists\TreeList;
use common\models\Tree;

class PromoSale
{
    /**
     * основной раздел
     * @var Tree $promoTreeModel
     */
    protected $promoTreeModel;

    /**
     * выбранный подраздел
     * @var Tree $treeModel
     */
    protected $treeModel;

    /**
     * PromoSale constructor.
     */
    public function __construct()
    {
        $this->promoTreeModel = \Yii::$app->cms->currentTree;

        $category = \Yii::$app->request->get('category');
        $subcategory = \Yii::$app->request->get('subcategory');

        if ($subcategory) {
            $this->treeModel = TreeList::getTreeByCode($subcategory);
        } elseif ($category) {
            $this->treeModel = TreeList::getTreeByCode($category);
        }

        if (!$this->treeModel) {
            $this->treeModel = $this->promoTreeModel;
        }

    }

    /**
     * @return bool
     */
    protected function isSales()
    {
        return $this->treeModel && $this->promoTreeModel->code === 'sales';
    }

    /**
     * @return bool
     */
    protected function isSubCategory()
    {
        return ($this->promoTreeModel->id && $this->treeModel) && $this->treeModel->id != $this->promoTreeModel->id;
    }

    /**
     * Заголовок в строке браузера
     * @return string
     */
    public function getTitle()
    {
        if ($this->isSales()) {
            return $this->treeModel->meta_title;
        }

        return sprintf('Распродажа товаров %s в телемагазине Shop & Show',
            ($tree = $this->treeModel) ? mb_strtolower($tree->name) : ''
        );
    }

    /**
     * Заголовок H1
     * @return string
     */
    public function getKeywords()
    {
        if ($this->isSales()) {
            return $this->treeModel->meta_keywords;
        }

        return sprintf('распродажа %s 
            продажа интернет магазин цены доставка видео смотреть',
            ($tree = $this->treeModel) ? mb_strtolower($tree->name) : ''
        );
    }

    /**
     * Заголовок H1
     * @return string
     */
    public function getDescription()
    {
        if ($this->isSales()) {
            return $this->treeModel->meta_description;
        }

        return sprintf('Вы можете выбрать и приобрести %s на распродаже телемагазина Shop & Show. Самые низкие цены. Доставка почтой России',
            ($tree = $this->treeModel) ? mb_strtolower($tree->name) : ''
        );
    }

    /**
     * @return string
     */
    public function getH1()
    {
        if ($this->isSales()) {
            if ($this->isSubCategory()) {
                return sprintf('%s по распродаже',
                    ($tree = $this->treeModel) ? ($tree->name_hidden ?: $tree->name) : ''
                );
            } else {
                return $this->treeModel->name;
            }
        }

        if ($this->isSubCategory()) {
            return sprintf('%s %s',
                ($tree = $this->promoTreeModel) ? ($tree->name_hidden ?: $tree->name) : '',
                ($tree = $this->treeModel) ? mb_strtolower($tree->name_hidden ?: $tree->name) : ''
            );
        }

        return $this->treeModel->name;
    }

    /**
     * @param \common\models\filtered\products\Catalog $filters
     * @return string
     */
    public function getTitleFromFilter(\common\models\filtered\products\Catalog $filters)
    {
        $title = $this->promoTreeModel->name_hidden ?: $this->promoTreeModel->name;

        if ($filters->sort == 'new') {
            $title .= '. Новинки';
        } elseif ($filters->sort == 'popular') {
            $title .= '. Хиты';
        }

        return $title;
    }

}