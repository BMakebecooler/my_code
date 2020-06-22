<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 24.05.2015
 */

namespace common\components;

use skeeks\cms\models\CmsTree;
use skeeks\cms\models\Tree;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * Class UrlRuleTree
 * @package skeeks\cms\components\urlRules
 */
class UrlRuleTree
    extends \skeeks\cms\components\urlRules\UrlRuleTree
{
    /**
     * @param \yii\web\UrlManager $manager
     * @param string $route
     * @param array $params
     * @return bool|string
     */
    public function createUrl($manager, $route, $params)
    {
        if ($route == 'cms/tree/view') {
            $defaultParams = $params;

            //Из параметров получаем модель дерева, если модель не найдена просто остановка
            $tree = $this->_getCreateUrlTree($params);
            if (!$tree) {
                return false;
            }

            //Для раздела задан редиррект
            if ($tree->redirect) {
                if (strpos($tree->redirect, '://') !== false) {
                    return $tree->redirect;
                } else {
                    $url = trim($tree->redirect, '/');

                    if ($tree->site) {
                        if ($tree->site->server_name) {
                            return $tree->site->url . '/' . $url;
                        } else {
                            return $url;
                        }
                    } else {
                        return $url;
                    }
                }
            }

            //Указан редиррект на другой раздел
            if ($tree->redirect_tree_id) {
                if ($tree->redirectTree->id != $tree->id) {
                    $paramsNew = ArrayHelper::merge($defaultParams, ['model' => $tree->redirectTree]);
                    $url = $this->createUrl($manager, $route, $paramsNew);
                    return $url;
                }
            }

            //Стандартно берем dir раздела
            if ($tree->dir) {
                $url = $tree->dir;
            } else {
                $url = "";
            }

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
            if ($tree->site) {
                //TODO:: добавить проверку текущего сайта. В случае совпадения возврат локального пути
                if ($tree->site->server_name) {
                    return $tree->site->url . '/' . $url;
                }
            }

            return $url;
        }

        return false;
    }

    /**
     * Поиск раздела по параметрам + удаление лишних
     *
     * @param $params
     * @return null|Tree
     */
    protected function _getCreateUrlTree(&$params)
    {
        $id = (int)ArrayHelper::getValue($params, 'id');
        $treeModel = ArrayHelper::getValue($params, 'model');

        $dir = ArrayHelper::getValue($params, 'dir');
        $site_code = ArrayHelper::getValue($params, 'site_code');

        ArrayHelper::remove($params, 'id');
        ArrayHelper::remove($params, 'model');

        ArrayHelper::remove($params, 'dir');
        ArrayHelper::remove($params, 'site_code');


        if ($treeModel && $treeModel instanceof Tree) {
            $tree = $treeModel;
            self::$models[$treeModel->id] = $treeModel;

            return $tree;
        }

        if ($id) {
            $tree = ArrayHelper::getValue(self::$models, $id);

            if ($tree) {
                return $tree;
            } else {
                $tree = CmsTree::findOne(['id' => $id]);
                self::$models[$id] = $tree;
                return $tree;
            }
        }


        if ($dir) {
            if (!$site_code && \Yii::$app->cms && \Yii::$app->cms->site) {
                $site_code = \Yii::$app->cms->site->code;
            }

            $tree = CmsTree::findOne([
                'dir' => $dir,
                'site_code' => $site_code
            ]);

            if ($tree) {
                self::$models[$id] = $tree;
                return $tree;
            }
        }


        return null;
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

        $originalDir = $pathInfo;
        if ($suffix) {
            $originalDir = substr($pathInfo, 0, (strlen($pathInfo) - strlen($suffix)));
        }

        $dependency = new TagDependency([
            'tags' =>
                [
                    (new Tree())->getTableCacheTag(),
                ],
        ]);


        if (!$pathInfo) //главная страница
        {
            $treeNode = Tree::getDb()->cache(function ($db) {
                return Tree::find()->where([
                    "site_code" => \Yii::$app->cms->site->code,
                    "level" => 0,
                ])->one();
            }, null, $dependency);

        } else //второстепенная страница
        {

            $treeNode = Tree::find()->where([
                "dir" => $originalDir,
                "site_code" => \Yii::$app->cms->site->code,
            ])->one();
        }

        if ($treeNode) {
            \Yii::$app->cms->setCurrentTree($treeNode);

            $params['id'] = $treeNode->id;
            return ['cms/tree/view', $params];
        } else {
            list($category, $originalDir) = array_map('strrev', explode('/', strrev($originalDir), 2));

            /** @var Tree $treeNode */
            $treeNode = Tree::find()->where([
                "dir" => $originalDir,
                "site_code" => \Yii::$app->cms->site->code,
            ])->one();

            if ($treeNode) {
                \Yii::$app->cms->setCurrentTree($treeNode);

                $params['id'] = $treeNode->id;
                $params['category'] = $category;

                return ['cms/tree/view', $params];
            }
            return false;
        }
    }
}
