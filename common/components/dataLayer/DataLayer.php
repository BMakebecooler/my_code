<?php

    /**
     * @author Arkhipov Andrei <arhan89@gmail.com>
     * @copyright (c) K-Gorod
     * Date: 11.04.2019
     * Time: 8:28
     */

    namespace common\components\dataLayer;

    use common\helpers\ArrayHelper;
    use common\models\Product;
    use modules\shopandshow\models\shop\ShopContentElement;
    use yii\base\Component;
    use yii\base\InvalidConfigException;
    use \yii\helpers\Json;
    use \yii\web\View;
    use Yii;

    class DataLayer extends Component
    {
        const EVENT_GA = 'gaEvent';

        /**
         * position - можно вообще удалить
         * photo - заменить на dimension5
         * rating - заменить на dimension6
         * reviews - заменить на dimension7
         * size - заменить на dimension8
         */
        const ATTRIBUTE_PHOTO = 'dimension5';
        const ATTRIBUTE_RATING = 'dimension6';
        const ATTRIBUTE_REVIEWS = 'dimension7';
        const ATTRIBUTE_SIZE = 'dimension8';

        protected $data = [];
        protected $promotions = [];
        protected $options = [];
        public static $position = 1;

        public function init ()
        {
            parent::init();
            $view = Yii::$app->view;
            DataLayerAsset::register($view);
            $view->on(View::EVENT_END_PAGE, [$this, 'registerJs']);
        }

        /**
         * @param $data
         */
        public function push ($data)
        {
            $this->data[] = $data;
        }

        public function registerJs ()
        {

            Yii::$app->view->registerJs("DataLayer.init();");
        }

        public function registerPromotions()
        {
            $js = '';

            if ($this->promotions) {
                $chunks = array_chunk($this->promotions, 10);
                foreach ($chunks as $chunk) {
                    $promotions = $this->promotions = $this->make([
                        'eventData' => [
                            DataLayerAction::PROMOTION_VIEW, true
                        ],
                        'ecommerce' => [
                            "promoView" => [
                                "promotions" => $chunk,
                            ],
                        ],
                    ]);
                    $js .= 'dataLayer.push(' . Json::encode($promotions, JSON_UNESCAPED_UNICODE) . ");" . PHP_EOL;
                }
                Yii::$app->view->registerJs($js);
            }
        }

        public function registerImpressions()
        {
            $js = '';
            if ($this->data) {
                foreach ($this->data as $data) {
                    $js .= 'dataLayer.push(' . Json::encode($data, JSON_UNESCAPED_UNICODE) . ');' . PHP_EOL;
                }
                Yii::$app->view->registerJs($js);
                Yii::$app->view->registerJs("DataLayer.init();");
            }
        }


        /**
         * @param array $attributes
         *
         * @return array
         * @throws InvalidConfigException
         */
        public function normalizePromotion (array $attributes): array
        {
            if (empty($attributes['id'])) {
                throw new InvalidConfigException('Не передано обязательное значение "id" для DataLayer');
            }
            return [
                'id' => $attributes['id'],
                'name' => $attributes['name'] ?? 'banner',
                'creative' => $attributes['creative'] ?? 'Вариант 1',
                'position' => $attributes['position'] ?? 1,
            ];
        }

        /**
         * @param array $attributes
         * @param bool  $asJson
         *
         * @return array|string
         * @throws InvalidConfigException
         */
        public function normalizePromotionOnClick (array $attributes, $asJson = true)
        {

            if (empty($attributes['id'])) {
                throw new InvalidConfigException('Не передано обязательное значение "id" для DataLayer');
            }

            $data = $this->make([
                'eventData' => [
                    DataLayerAction::PROMOTION_CLICK
                ],
                'ecommerce' => [
                    "promoClick" => [
                        "promotions" => [
                            [
                                'id' => $attributes['id'],
                                'name' => $attributes['name'] ?? 'banner',
                                'creative' => $attributes['creative'] ?? 'Вариант 1',
                                'position' => $attributes['position'] ?? self::$position++,
                            ]
                        ],
                    ],
                ],
            ]);

            return $asJson ? Json::encode($data, JSON_UNESCAPED_UNICODE) : $data;
        }

        /**
         * @param $attributes
         * @param bool $asJson
         * @param string $list
         * @param int $index
         * @return array|string
         */
        public function normalizeForProduct ($attributes, $asJson = true, $list = "", $index = 1)
        {
            $data = $this->make([
                'eventData' => [
                    DataLayerAction::PRODUCT_CLICK
                ],
                'ecommerce' => [
                    "click" => [
                        'actionField' => [
                            "list" => $list,
                        ],
                        "products" => [
                            $this->normalizeProduct($attributes, $list, $index)
                        ],
                    ],
                ],
            ]);

            return $asJson ? Json::encode($data, JSON_UNESCAPED_UNICODE) : $data;
        }

        /**
         * @param array  $dataProvider
         * @param string $list
         */
        public function pushCatalogProducts(array $dataProvider, string $list = '')
        {
            $impressions = [];

            foreach ($dataProvider as $index => $item) {
                $impressions[] = $this->normalizeProductForCatalog($item, $list, ++$index);
            }

            $chunks = array_chunk($impressions, 10);

            foreach ($chunks as $impressions) {
                $data = $this->make([
                    'eventData' => [
                        DataLayerAction::PRODUCT_IMPRESSIONS, true
                    ],
                    'ecommerce' => [
                        'impressions' => $impressions,
                    ],
                ]);

                $this->push($data);
            }
        }

        /**
         * @param null $dataProvider
         *
         * @throws InvalidConfigException
         */
        public function pushPromotions (array $dataProvider)
        {
            foreach ($dataProvider as $item) {
                $this->promotions[] =  $this->normalizePromotion($item) ;
            }
        }

        /**
         * @param array  $dataProvider
         * @param string $list
         */
        public function pushImpressions (array $dataProvider, string $list = '')
        {
            $impressions = [];

            foreach ($dataProvider as $index => $item) {
                $impressions[] = $this->normalizeProduct($item, $list, ++$index);
            }

            $chunks = array_chunk($impressions, 10);

            foreach ($chunks as $impressions) {
                $data = $this->make([
                    'eventData' => [
                        DataLayerAction::PRODUCT_IMPRESSIONS, true
                    ],
                    'ecommerce' => [
                        'impressions' => $impressions,
                    ],
                ]);

                $this->push($data);
            }
        }

        /**
         * @param $item
         * @param $list
         * @param int $index
         * @return array
         */
        public function normalizeProduct ($item, $list, $index = 1)
        {
            /**
             * @var ShopContentElement $item
             */

            /*if ($item->cmsTree) {
                $parents = $item->cmsTree->parents;
                $parents[] = $item->cmsTree;
                $categories = array_slice(ArrayHelper::getColumn($parents, 'name'), 2);
            }

            $categoryName = join('/', $categories ?? []);*/

            return [
                'id' => ArrayHelper::getValue($item, 'id'), // Идентификатор/артикул товара
                'name' => ArrayHelper::getValue($item, 'name'), // название товара
                'price' => (float)ArrayHelper::getValue($item, 'price.price'), // Стоимость за единицу товара
                //'brand' => $item->relatedPropertiesModel->getAttribute('brand'), // Торговая марка
                //'category' => $categoryName, // Дерево категорий, где каждая категория товара разделяется символом слеш «/»
                'list' => $list, //Список в котором идет показ (Поиск/Каталог)
                'position' => $index, //Позиция товара в списке показа
            ];
        }



        /**
         * @param array $config
         *
         * @return array
         */
        public function make (array $config): array
        {
            $baseStructure = [
                "event" => self::EVENT_GA,
                "eventdata" => call_user_func_array([$this, 'getEventData'], $config['eventData']),
                "ecommerce" => [],
            ];

            $baseStructure['ecommerce'] = array_merge(["currencyCode" => "RUB"], ArrayHelper::getValue($config, 'ecommerce'));

            return $baseStructure;
        }

        /**
         * @param $action
         * @param $ni
         * @param $category
         *
         * @return array
         */
        public function getEventData (string $action, bool $ni = false, string $category = 'Ecommerce'): array
        {
            return [
                "category" => $category,
                "action" => $action,
                "ni" => $ni,
            ];
        }

        /**
         * @param        $product
         * @param bool   $asJson
         * @param string $list
         * @param int    $index
         *
         * @return array|string
         */
        public function onClickInCatalogOnProduct ($product, $asJson = true, $list = "", $index = 1)
        {
            $data = $this->make([
                'eventData' => [
                    DataLayerAction::PRODUCT_CLICK
                ],
                'ecommerce' => [
                    "click" => [
                        'actionField' => [
                            "list" => $list,
                        ],
                        "products" => [
                            $this->normalizeProductForCatalog($product, $list, $index)
                        ],
                    ],
                ],
            ]);

            return $asJson ? Json::encode($data, JSON_UNESCAPED_UNICODE) : $data;
        }



        //version for js

        /**
         * @param $product
         * @param $list
         * @param bool $asJson
         * @param array $additional
         * @return array|string
         */
        public function normalizeProductForCatalog ($product, $list, $asJson = true, $additional = [])
        {
            $data = [
                'id' => $product->id, // Идентификатор/артикул товара
                'price' => (float)$product->new_price, // Стоимость за единицу товара
                'list' => $list, //Список в котором идет показ (Поиск/Каталог)
                'position' => 1, //Позиция товара в списке показа
            ];

            if(!$product instanceof Product) {
                $product = Product::create($product->attributes);
            }
            $this->populateProduct($data, $product, $additional);
            return $asJson ? Json::encode($data, JSON_UNESCAPED_UNICODE) : $data;
        }

        /**
         * @param       $product
         * @param bool  $asJson
         * @param array $attributes
         *
         * @return array|string
         */
        public function productDetail ($product, $asJson = true, $attributes = [])
        {
            $lot = \common\helpers\Product::getLot($product->id);

            $data = [
                'id' => $product->id, // Идентификатор/артикул товара
                'price' => (float)$product->new_price, // Стоимость за единицу товара
                //'position' => 1, //Позиция товара в списке показа
                'variant' => '',
                'categoryId' => !empty($lot->tree_id) ? $lot->tree_id : null,
                self::ATTRIBUTE_SIZE => '',
                self::ATTRIBUTE_RATING => (float)$product->new_rating,
                self::ATTRIBUTE_REVIEWS => 0,
            ];

            /**
             * @var Product $product
             */
            if(!$product instanceof Product) {
                $product = Product::create($product->attributes);
            }
            //$attributes['name'] = $product->new_lot_name;
            $this->populateProduct($data, $product, $attributes);

            return $asJson ? Json::encode($data, JSON_UNESCAPED_UNICODE) : $data;
        }

        /**
         * @param       $result
         * @param       $product
         * @param array $attributes
         */
        public function populateProduct(array &$result, Product $product, $attributes = [])
        {
            if(!$product->isLot()) {
                $product = $product->lot;
            }

            if($product) {
                $result['name'] = $this->getProductName($product);

                if ($tree = $product->tree) {
                    $parents = $tree->parents;
                    $parents[] = $tree;
                    $categories = array_slice(ArrayHelper::getColumn($parents, 'name'), 2);
                }

                $result['brand'] = ArrayHelper::getValue($product, 'brand.name', '');
                $result['category'] = join('/', $categories ?? []);
                $result[self::ATTRIBUTE_PHOTO] = count($product->getImages($product->id));

                if($attributes) {
                    foreach ($attributes as $attribute => $value) {
                        $result[$attribute] = $value;
                    }
                }
            }
        }

        /**
         * @param Product $product
         *
         * @return string
         */
        public function getProductName(Product $product) :string
        {
            if(empty($product->new_lot_name)) {
                $name = trim( $product->name); // название товара
                preg_match('/^\[([\d\-]+)\]\s*(.+)\s*\(0?0?(\d+)\)/', $name, $match);
                if(!empty($match[2])) {
                    $name  = trim($match[2]);
                }
                return $name;
            }
            return $product->new_lot_name;
        }

        /**
         * @param $order
         * @param bool $asJson
         * @return array|string
         */
        public function shopBasket($order, $asJson = true)
        {
            $products = [];

            foreach ($order->shopBaskets as $item) {
                $modification = Product::findOne($item->product_id);

                if($modification) {
                    $card = Product::findOne($modification->parent_content_element_id);
                    $products[] = $this->productDetail($card, false, [
                        'variant' => (int)$item->product_id,
                        'price' => (float)$item->price,
                        'quantity' => (float)$item->quantity,
                    ]);
                }
            };

            $discountCoupons = ArrayHelper::getValue($order, 'discountCoupons');
            $coupons = [];
            if ($discountCoupons) {
                $coupons = ArrayHelper::getColumn($discountCoupons, 'coupon');
            }

            $result = [
                'order' => [
                    'id' => $order->order_number ?: $order->id,
                    'revenue' => (float)$order->money->getValue(),
                    'tax' => $order->moneyVat->getValue(),
                    'shipping' => 0,
                    'coupon' => $coupons ? implode(',', $coupons) : '',
                    'discount' => $order->moneyDiscount->getValue()
                ],
                'products' => $products
            ];

            return $asJson ? Json::encode($result, JSON_UNESCAPED_UNICODE) : $result;
        }
    }
