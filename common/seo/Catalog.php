<?php

namespace common\seo;

use skeeks\cms\models\CmsTree;

class Catalog
{

    /**
     * @var CmsTree
     */
    protected $treeModel;

    public function __construct(CmsTree $tree)
    {
        $this->treeModel = $tree;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($metaTitle = $this->treeModel->meta_title) {
            return $metaTitle;
        }

        $page = (int)\Yii::$app->request->get('page');

        if (!$page || $page == 1) {
            return sprintf('%s – купить недорого %s по цене от 200 рублей в телемагазине Shop & Show',
                $this->treeModel->name, mb_strtolower($this->treeModel->name)
            );
        }

        return sprintf('%s – страница №%d – официальный сайт телемагазина Shop&Show',
            $this->treeModel->name, $page
        );
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        if ($metaKeyword = $this->treeModel->meta_keywords) {
            return $metaKeyword;
        }

        return sprintf('%s продажа интернет магазин цены доставка видео смотреть',
            mb_strtolower($this->treeModel->name)
        );
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        if ($metaDescription = $this->treeModel->meta_description) {
            return $metaDescription;
        }

        return sprintf('Вы можете выбрать и приобрести %s  в каталогах телемагазина Shop & Show. Видео ролики для каждого товара. Доставка почтой по России.',
            mb_strtolower($this->treeModel->name)
        );
    }

    /**
     * @param \common\models\filtered\products\Catalog $filters
     * @return string
     */
    public function getTitlefromFilter(\common\models\filtered\products\Catalog $filters)
    {
        $title = $this->treeModel->name_hidden ?: $this->treeModel->name;

        if ($filters->sort == 'new') {
            $title .= '. Новинки';
        } elseif ($filters->sort == 'popular') {
            $title .= '. Хиты';
        }

        $page = (int)\Yii::$app->request->get('page');

        if (!$page || $page == 1) {
            return $title;
        }

        return sprintf('%s – страница №%d',$title, $page);
    }

//    public function getPageTitle($page = null,$sort = null)
//    {
//        $title = $this->treeModel->name_hidden ?: $this->treeModel->name;
//
//        if ($sort == 'new') {
//            $title .= '. Новинки';
//        } elseif ($sort == 'popular') {
//            $title .= '. Хиты';
//        }
//
//        if(!$page)
//            $page = (int)\Yii::$app->request->get('page');
//
//        if (!$page || $page == 1) {
//            return $title;
//        }
//
//        return sprintf('%s – страница №%d',$title, $page);
//    }

    public function getPageTitle($page = null)
    {
        $title = $this->treeModel->name_hidden ?: $this->treeModel->name;

        if(!$page)
            $page = (int)\Yii::$app->request->get('page');

        if (!$page || $page == 1) {
            return $title;
        }

        return sprintf('%s – страница №%d',$title, $page);
    }

}