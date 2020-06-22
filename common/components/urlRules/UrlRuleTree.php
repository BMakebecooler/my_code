<?php

    namespace common\components\urlRules;

    use common\helpers\ThemeHelper;
    use common\models\Promo;
    use skeeks\cms\models\CmsTree;
    use skeeks\cms\models\Tree;
    use yii\caching\TagDependency;
    use yii\helpers\ArrayHelper;

    /**
     * Class UrlRuleTree
     * @package skeeks\cms\components\urlRules
     */
    class UrlRuleTree extends \yii\web\UrlRule
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


                if (isset($defaultParams['category'])) {
                    $url .= '/' . $defaultParams['category'];
                }

                if (isset($defaultParams['subcategory'])) {
                    $url .= '/' . $defaultParams['subcategory'];
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

            ArrayHelper::remove($params, 'category');
            ArrayHelper::remove($params, 'subcategory');

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
         * @throws \Throwable
         * @throws \yii\base\InvalidConfigException
         */
        public function parseRequest($manager, $request)
        {
            $new_theme = $request->get('new_theme');
            $old_theme = $request->get('old_theme');

            if(isset($old_theme) && $old_theme) {
                \Yii::$app->session->set('new_theme',0);
            }elseif (isset($new_theme) && $new_theme){
                \Yii::$app->session->set('new_theme',1);
            }
            $new_theme = \Yii::$app->session->get('new_theme');
            $new_theme = true;
            if($new_theme) {
                $flag = false;
                $pathInfo = $request->getPathInfo();
                if ($this->host !== null) {
                    $pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : '/' . $pathInfo);
                }
                $path = explode('/',$pathInfo);
                $specialParts = [
                    'novinki' => 'new',
                    'hityi' => 'popular',
                    'hot' => 'popular',
                    'maksimalnyie-skidki' => 'sale',
                    'maksimalnaya-skidka' => 'sale',
                    'posledniy-razmer' => 'quantity'
                ];
                $special = false;
                $originalDir = [];
                foreach ($path as $part){
                    if(!empty($part)){
                        if($part == 'catalog'){
                            $flag = true;
                        }
                        if(!in_array($part,array_keys($specialParts))) {
                            $originalDir[] = $part;
                        }else{
                            $special = $part;
                        }
                    }
                }

                if($flag) {
                    $originalDir = implode('/', $originalDir);
                    $treeNode = Tree::find()->where([
                        "dir" => $originalDir,
                        "site_code" => \Yii::$app->cms->site->code,
                    ])->one();


                    if ($treeNode) {
                        $params = [];
                        $params['tree_id'] = $treeNode->id;
//                        $params['new_theme'] = 1;
                        if($special && isset($specialParts[$special])){
                            $params['sort'] = $specialParts[$special];
                        }
                        return ['/category/view', $params];
                    } else {
                        return false;
                    }
                }
            }
            if (ThemeHelper::isEnable()) {
                return false;
            }

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

            $dependency = new TagDependency([
                'tags' =>
                    [
                        (new Tree())->getTableCacheTag(),
                    ],
            ]);

            if (!$pathInfo) {
                //главная страница
                $treeNode = Tree::getDb()->cache(function ($db) {
                    return Tree::find()->where([
                        "site_code" => \Yii::$app->cms->site->code,
                        "level" => 0,
                    ])->one();
                }, null, $dependency);


            } else {
                //второстепенная страница
                $originalDir = $pathInfo;
                if ($suffix) {
                    $originalDir = substr($pathInfo, 0, (strlen($pathInfo) - strlen($suffix)));
                }
                $originalDir = filter_var($originalDir, FILTER_SANITIZE_SPECIAL_CHARS);

                $treeNode = Tree::find()->where([
                    "dir" => $originalDir,
                    "site_code" => \Yii::$app->cms->site->code,
                ])->one();

                if (!$treeNode) {
                    $treeNode = $this->getTreeParse($originalDir, $params);
                }
            }

            if ($treeNode) {
                \Yii::$app->cms->setCurrentTree($treeNode);
                $params['id'] = $treeNode->id;
                return ['/cms/tree/view', $params];
            } else {
                return false;
            }
        }

        /**
         * @param $originalDir
         * @param $params
         * @return array|bool|null|\yii\db\ActiveRecord
         */
        private function getTreeParse($originalDir, &$params)
        {
            $dirs = explode('/', $originalDir);

            if ($dirs) {
                $parts = [];

                /**
                 * В каталоге не нужна логика "виртуальных категорий"
                 * в виду не корректно работающей 404 ошибки
                 */
                if (substr_count($originalDir, 'catalog')) {
                    $parts[] = $originalDir;
                } else {
                    foreach ($dirs as $key => $dir) {
                        $parts[] = join('/', array_slice(($dirs), 0, count($dirs) - $key));
                    }
                }

                $tree = Tree::getDb()->cache(function ($db) use ($parts) {
                    $forOrder = \common\helpers\ArrayHelper::arrayToString($parts);
                    return Tree::find()
                        ->where([
                            'site_code' => \Yii::$app->cms->site->code,
                        ])
                        ->andWhere(['or', ['dir' => $parts]])
                        ->orderBy([new \yii\db\Expression('FIELD (cms_tree.dir, ' . join(',', $forOrder) . ') ASC')])
                        ->one();
                }, HOUR_2);

                if (!$tree) {
                    return false;
                }

                $noDir = str_replace($tree->dir, '', $originalDir);

                $dirs = explode('/', $noDir);
                $dirs = array_values(array_filter($dirs));
                $countDirs = count($dirs);

                if ($countDirs == 2) {
                    $params['subcategory'] = isset($dirs[$countDirs - 1]) ? $dirs[$countDirs - 1] : null;
                    $params['category'] = isset($dirs[$countDirs - 2]) ? $dirs[$countDirs - 2] : null;
                } elseif ($countDirs = 1) {
                    $params['category'] = isset($dirs[$countDirs - 1]) ? $dirs[$countDirs - 1] : null;
                }

                return $tree;
            }

            return false;
        }
    }
