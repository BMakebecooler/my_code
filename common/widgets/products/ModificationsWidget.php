<?php

namespace common\widgets\products;

use common\helpers\App;
use common\helpers\User;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\models\Product;
use common\models\ProductHelper;
use common\models\ProductProperty;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\shop\SsShopProductPrice;
use skeeks\cms\base\WidgetRenderable;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContent;

use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\searchs\SearchRelatedPropertiesModel;
use skeeks\cms\shop\models\ShopContent;
use Yii;
use yii\base\ViewNotFoundException;
use common\helpers\ArrayHelper;
use yii\db\Expression;
use yii\widgets\ActiveForm;

/**
 * @property CmsContent $cmsContent ;
 * @property ShopContent $shopContent;
 * @property []                 $childrenElementIds;
 * @property CmsContentElement $model;
 *
 * Class ModificationsWidget
 * @package  skeeks\cms\shop\cmsWidgets\filters
 */
class ModificationsWidget extends WidgetRenderable
{

    const NAMESPACE_NAME = 'ProductModificationsWidget-product2-kfss';
    const SHIK_NAMESPACE_NAME = 'ProductModificationsWidget-product-shik';

    const COLOR_CODE = 'KFSS_COLOR';
    const COLOR_CODE_BX = 'KFSS_COLOR_BX';
    const KFSS_COLOR_ID = 231;
    const COLOR_CODE_BX_ID = 284;

    /**
     * @var CmsContentElement
     */
    public $model;

    /**
     * @var ShopProduct
     */
    public $shopProduct;

    /**
     * @var ProductHelper
     */
    public $product;

    /**
     * Главная карточка товара, в независимости от того какая отображается
     * @var CmsContentElement
     */
    public $simpleProduct;

    /**
     * @var array
     */
    public static $joinMap = [self::COLOR_CODE => 'card', self::COLOR_CODE_BX => 'card'];

    /**
     * Для хранения ID свойства битриксового цвета
     * @var null
     */
    private static $kfssColorBxPropId = null;

    //Настройки
    public $content_id;
    public $realatedProperties = [];

    /**
     * @var SearchRelatedPropertiesModel
     */
    public $modificationPropertiesModel = null;

    /**
     * Доступные параметры для модификаций
     * @var array
     */
    private $parameters = [];
    private $offersParameters = [];

    private $cardsNum;
    private $colorCardsNum;

    /**
     * @var self
     */
    protected static $_instance;

    public static function getInstance()
    {
        return self::$_instance;
    }

    public static function getNameSpace()
    {
        static $nameSpaceList = [
            'shopandshow' => self::NAMESPACE_NAME,
            'shik' => self::SHIK_NAMESPACE_NAME
        ];

        if (defined('SS_SITE') && isset($nameSpaceList[SS_SITE])) {
            return $nameSpaceList[SS_SITE];
        }

        return self::NAMESPACE_NAME;
    }

    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => 'Настройка модификаций',
        ]);
    }

    public function init()
    {
        parent::init();

        //Так как список свойств из настроек component_settings тянется нестабильно
        //Будем выбирать из явного списка в хелпере
        $this->realatedProperties = \common\helpers\Property::$sizePropsForProductCard;

        $colorProp = CmsContentProperty::findOne(['code' => self::COLOR_CODE]);
        $colorPropId = $colorProp->id;

        //[Deprecated]Если нет не базовых карточек то свойство цвет показывать не требуется
        //Не активная карточка - считай что ее совсем нет
        //Забываем про базовость у карточек. Значение имеет только свойство Цвет (KFSS_COLOR) и кол-во карточек
        //Если карточек несколько (обязательно активных) то это точно цветовые карточки
        //Если карточка одна то цветная или нет определяется по значению свойства цвет (у карт без цвета == UNDEFINED)
        $cards = CmsContentElement::find()
            ->alias('content_element')
            ->addSelect(new Expression("IF(color.name IS NULL OR color.name='UNDEFINED', 0, 1) AS has_color"))
            ->where([
                'content_element.active' => Cms::BOOL_Y,
                'content_element.parent_content_element_id' => $this->model->id,
                'content_element.content_id' => CARD_CONTENT_ID,
            ])
            ->leftJoin(CmsContentElementProperty::tableName() . ' AS el_prop_color',
                "el_prop_color.element_id=content_element.id AND el_prop_color.property_id={$colorPropId}")
            ->leftJoin(CmsContentElement::tableName() . ' AS color',
                "color.id=el_prop_color.value")
            ->asArray()
            ->all();

        //Общее кол-во карточек в товаре
        $this->cardsNum = count($cards);

        //Кол-во карточек имеющих цвет
        $this->colorCardsNum = count(array_filter($cards, function ($card) {
            return $card['has_color'] > 0;
        }));

        if (User::isDebug() && isset($_GET['cmscontent'])) {
            echo "cmsContent";
            echo "<pre>";
            var_dump($this->cmsContent);
            echo "</pre>";
        }


        if ($this->cmsContent->cmsContentProperties) {
            $this->modificationPropertiesModel = new SearchRelatedPropertiesModel(); //ПОМЕНЯТЬ
            $this->modificationPropertiesModel->initCmsContent($this->cmsContent);

            if (App::isWebApplication()) {
                $this->modificationPropertiesModel->load(\Yii::$app->request->get());
            }
        }

        if ($this->modificationPropertiesModel) {
            $this->initParameters();
        }


        if (User::isDebug() && isset($_GET['relprops'])) {
            echo "relatedProperties";
            echo "<pre>";
            var_dump($this->realatedProperties);
            echo "</pre>";
        }

        if (User::isDebug() && isset($_GET['params'])) {
            echo "Params";
            echo "<pre>";
            var_dump($this->parameters);
            echo "</pre>";
        }


        if ($this->model) {

            if (!$this->simpleProduct) {
                $this->simpleProduct = $this->model->product;
            }

            $this->initOffersParameters();


            if (User::isDebug() && isset($_GET['offparams'])) {
                echo "offParams";
                echo "<pre>";
                var_dump($this->offersParameters);
                echo "</pre>";
            }


            if (!$this->shopProduct) {
                $shopCmsContentElement = new ShopContentElement($this->model);
                $this->shopProduct = ShopProduct::getInstanceByContentElement($shopCmsContentElement);
            }
        }

        self::$_instance = $this;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'content_id' => \Yii::t('skeeks/shop/app', 'Content'),
                'realatedProperties' => 'Свойства (только с типом «Привязан к элементу справочника»)',
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [['content_id'], 'integer'],
                [['realatedProperties'], 'safe'],
            ]);
    }

    /**
     * @return ShopContent
     */
    public function getShopContent()
    {
        return ShopContent::findOne(['content_id' => $this->content_id]);
    }


    public function renderConfigForm(ActiveForm $form)
    {
        return $this->render(__DIR__ . '/modifications/_form.php', [
            'form' => $form,
            'model' => $this,
        ], $this);
    }

    /**
     * Отрендерить динамическое свойство
     *
     * @param CmsContentProperty $property
     * @return string
     */
    public function renderPropertyBlock($property)
    {
        try {
            //Почему то @template не работает должным образом, не меняет значение при смене десктоп/мобила
            //Приходится идти другим путем
            $deviceTypeAlias = \Yii::$app->mobileDetect->isDescktop() ? 'site' : 'mobile';

            $viewFile = sprintf("@{$deviceTypeAlias}/modules/cms/content-element/_product/characteristics/modifications/%s", '_block_' .
                strtolower($property->code));

            $isViewFile = Yii::getAlias($viewFile);

            /**
             * Проверяем если файл с таким кодом свойства существуем берем его, если нет то файл по умолчанию
             */

            if (!file_exists($isViewFile . '.php')) {
                $viewFile = sprintf('@template/modules/cms/content-element/_product/characteristics/modifications/%s', '_block_' .
                    'default');
            }

            return $this->render(
                $viewFile, [
                'widget' => $this,
                'property' => $property,
            ]);
        } catch (ViewNotFoundException $e) {
            if (false && User::isDeveloper()) {
                return sprintf('Для свойства %s(%s) нет отображения!<br>', $property->name, $property->code);
            }
        }
    }

    /**
     * @return CmsContent
     */
    public function getCmsContent()
    {
        return CmsContent::findOne($this->content_id);
    }

    /**
     * @param $property
     *
     * @return bool
     */
    public function isShowRelatedProperty($property)
    {

        if (!$this->realatedProperties) {
            return false;
        }

        if (!in_array($property->code, $this->realatedProperties)) {
            return false;
        }

        return true;
    }

    /**
     * Инициализировать доступные для выбора параметры
     * @return array|\yii\db\ActiveRecord[]|CmsContentProperty
     */
    protected function initParameters()
    {
        $parameters = [];
        if ($properties = $this->modificationPropertiesModel->properties) {
            foreach ($properties as $property) {
                if ($this->isShowRelatedProperty($property)) {
                    $parameters[] = $property->id;
                }
            }
        }

        if ($parameters) {
            $forOrder = \common\helpers\ArrayHelper::arrayToString($parameters);
            $this->parameters = CmsContentProperty::find()->where(['id' => $parameters])
                ->orderBy([new \yii\db\Expression('FIELD (id, ' . join(',', $forOrder) . ') ASC')])->all();
        }

        if (User::isDebug() && isset($_GET['this'])) {
            echo "this";
            echo "<pre>";
            var_dump($this->toArray());
            echo "</pre>";
        }

        if (User::isDebug() && isset($_GET['cardsnum'])) {
            echo "CardsNum [all/colored]";
            echo "<pre>";
            echo($this->cardsNum . ' / ' . $this->colorCardsNum);
            echo "</pre>";
        }

        //Если карточки с параметрами цвета присутствуют - добавляем параметр цвет в список выбираемый параметров
        if ($this->colorCardsNum > 0) {
            array_unshift($this->parameters, CmsContentProperty::find()->where(['content_id' => CARD_CONTENT_ID, 'code' => self::COLOR_CODE])->one());
        }

        return $this->parameters;
    }

    /**
     * Получить параметры
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Получить значения параметров предложений
     *
     * @return array
     */
    public function getOffersParameters()
    {
        return $this->offersParameters;
    }

    /**
     * @TODO переделать эту логику, сделать загрузку этих данных в 1 цикл при инициализации параметров
     * Получить значения конкретного параметра
     *
     * @param $code
     *
     * @return array
     */
    public function getParameterData($code)
    {
        $data = [];

        foreach ($this->getOffersParameters() as $item) {
            $propertyId = $item['p_' . $code . '_id'] ?? null;
            $name = $item['p_' . $code . '_name'] ?? null;
            $image = $item['p_' . $code . '_image'] ?? null;
            $cluster = $item['p_' . $code . '_cluster'] ?? 'property_images';
            $quantity = $item['new_quantity'];
            $offerId = $item['offer_id'];
            $cardId = $item['card_id'];
            $price = $item['price'];
            $maxPrice = $item['max_price'];

            if (!isset($item[$propertyId])) {
                if ($name && $propertyId) {

                    $nameKfssColor = $code == self::COLOR_CODE ? $name : null;
                    $nameKfssColorBx = $item['p_' . self::COLOR_CODE_BX . '_name'] ?? null;

                    if ($code == self::COLOR_CODE && $nameKfssColorBx) {
                        //$name .= ', ' . $nameKfssColorBx;
                        $name = mb_strtolower($nameKfssColorBx);
                    }

                    $data[$name] = [
                        'property_id' => $propertyId,
                        'name' => $name,
                        'name_kfss_color' => $nameKfssColor,
                        'name_kfss_color_bx' => $nameKfssColorBx,
                        'price' => $price,
                        'max_price' => $maxPrice,
                        'image' => ($image) ? \Yii::$app->storage->getCluster($cluster)->publicBaseUrl . '/' . $image : null,
                        'quantity' => isset($data[$name]['quantity']) ? $data[$name]['quantity'] += $quantity : $quantity,
                        'offer_id' => $offerId,
                        'card_id' => $cardId
                    ];
                }
            }
        }

        ArrayHelper::multisort($data, 'name');

        return $data;
    }

    /**
     * Получить значения параметров всех предложений
     *
     * @return array|\yii\db\Query[]
     */
    protected function initOffersParameters()
    {
        $restProperty = CmsContentProperty::findOne(['code' => 'REST', 'content_id' => OFFERS_CONTENT_ID]);

        $cmsContentElement = (new \yii\db\Query())->from(CmsContentElement::tableName() . ' AS card')
            ->addSelect([
                'offer.id AS offer_id',
                'card.id AS card_id',
                'kfss_color_bx_value.value AS p_' . self::COLOR_CODE_BX . '_id',
                'kfss_color_bx.name AS p_' . self::COLOR_CODE_BX . '_name',
                'card.new_quantity',
                //new \yii\db\Expression('999 as quantity'),
                'prices.price',
                'prices.max_price',
                'props.value AS rest'
            ])
            ->andWhere('card.parent_content_element_id = :product_id', [':product_id' => $this->simpleProduct->id])
            ->innerJoin(['offer' => CmsContentElement::tableName()], 'card.id = offer.parent_content_element_id  ')
//            ->innerJoin(ShopProduct::tableName(), 'offer.id = shop_product.id')
            ->innerJoin(['prices' => SsShopProductPrice::tableName()], 'prices.product_id = offer.id')
            ->leftJoin(['props' => CmsContentElementProperty::tableName()], 'props.element_id = offer.id AND props.property_id = :rest_property_id')
            ->leftJoin(['kfss_color_bx_value' => CmsContentElementProperty::tableName()], 'kfss_color_bx_value.element_id = card.id AND kfss_color_bx_value.property_id = ' . self::getKfssColorBxPropId())
            ->leftJoin(['kfss_color_bx' => CmsContentElement::tableName()], 'kfss_color_bx.id = kfss_color_bx_value.value')
            ->andWhere('offer.new_quantity > 0')
            ->andWhere('prices.price > 1')
            ->andWhere('offer.active = :active_status', [':active_status' => Cms::BOOL_Y])
            ->andWhere('card.active = :active_status', [':active_status' => Cms::BOOL_Y])
            ->andWhere(['!=', 'card.hide_from_catalog_image', 1])
//            ->andWhere(['>', 'card.image_id', 0])
            ->addParams([':rest_property_id' => $restProperty->id]);

        //Если у товара нет цвета, но есть модификации - то при этом условии мы никогда их не увидим
        //Если у товара не только базовая модификация

        //Не учитываем базовость так как достаточно цветовых карточек имеющих этот признак
        //TODO Возможно доавбить условие не выбора базовой модификации
        if ($this->cardsNum > 1) {
            //$cmsContentElement->andWhere('card.is_base = :not_base_status', [':not_base_status' => Cms::BOOL_N]);
        }

        //* Уберем из списка свойства, значений для которых нет ни у одной модификации *//

        $parameters = $this->parameters;

        $offers = Product::getProductOffersCanSale($this->simpleProduct->id);

        if ($offers) {
            $offersIds = ArrayHelper::getColumn($offers, 'id');

            $propsNonEmpty = ProductProperty::getNonEmptyGrouped($offersIds);
            $propsNonEmptyIds = $propsNonEmpty ? ArrayHelper::getColumn($propsNonEmpty, 'id') : [];

            //оставляем только те что есть в пересечении
            $parameters = array_filter($this->parameters, function ($property) use ($propsNonEmptyIds) {
                //Нам подходит только Цвет, Битрикс цвет и то свойство что не пустое из модификаций
                return ($property->code == 'KFSS_COLOR' || $property->code == 'KFSS_COLOR_BX' || in_array($property->id, $propsNonEmptyIds));
            });
        }

        //* /Уберем из списка свойства, значений для которых нет у одной модификации *//

        $countJoin = 0;

//        foreach ($this->parameters as $property) { //старый вариант с полным перечнем всех свойств
        foreach ($parameters as $property) {

            //Добавлено в связи с тем что при обработке большого кол-ва свойств кол-во джойнов превышает лимит в БД (61 джойн макс)
            //С добавлением фильтрации по непустым свойствам более не должно быть актуальным
            if ($countJoin > 16) {
                break;
            }

            $joinTable = self::$joinMap[$property->code] ?? 'offer';

            $offerPropertyName = 'of_p_' . $property->code;
            $propertyName = 'p_' . $property->code;
            $image = 'p_' . $property->code . '_image';
//            $storageFiles = 'p_' . $property->code . '_image';

            $cmsContentElement
                ->leftJoin(CmsContentElementProperty::tableName() . ' AS ' . $offerPropertyName,
                    "$joinTable.id = $offerPropertyName.element_id AND $offerPropertyName.property_id = $property->id")
                ->leftJoin(CmsContentElement::tableName() . ' AS ' . $propertyName,
                    "$propertyName.id = $offerPropertyName.value")
//                ->leftJoin(CmsContentElementImage::tableName() . ' AS ' . $image,
//                    "$propertyName.id = $image.content_element_id")

                ->leftJoin(CmsStorageFile::tableName() . ' AS ' . $image,
                    ($property->code == self::COLOR_CODE ? 'card' : $propertyName) . ".image_id = $image.id")
                ->addSelect([
                    $propertyName . sprintf('.id AS %s_id', $propertyName), //Ид параметра
                    $propertyName . sprintf('.name AS %s_name', $propertyName), //Название параметра
                    $image . sprintf('.cluster_file AS %s_image', $propertyName), //Картинка параметра
                    $image . sprintf('.cluster_id AS %s_cluster', $propertyName), //кластер Картинки параметра
                ]);
            $countJoin++;
        }

        if (User::isDebug() && isset($_GET['params_query'])) {
            echo $cmsContentElement->createCommand()->getRawSql();
        }

        $this->offersParameters = $cmsContentElement->orderBy('card.new_quantity DESC')
            ->all();

        return $this->offersParameters;
    }

    /**
     * Собрать массив цен по параметрам для клиентской части
     *
     * @return array
     */
    public function getClientParameterData()
    {

        /*
        Массив который должен получится в результате
        $data = [
                    22955 => [22955 => price, 8675 => price, 8679 => price],
                    8679 => [8679 => price, 22955 => price, 22954 => price],
                ];
        */

        $data = [];
        $parameterName = [];//Названия параметров
        foreach ($this->getParameters() as $parameter) {
            $parameterName[] = 'p_' . $parameter->code . '_id';
        }

        $isProductCodeS = false;
        $discount = \modules\shopandshow\lists\Discount::getByCode('codes');
        if ($this->shopProduct && $discount && $discount->canProductApply($this->shopProduct, false)) {
            $isProductCodeS = true;
        }

        // перебираем все офферы
        foreach ($this->getOffersParameters() as $offersParameter) {
            $offerId = $offersParameter['offer_id'];
            $cardId = $offersParameter['card_id'];
            $price = (int)$offersParameter['price'];
            $maxPrice = (int)$offersParameter['max_price'];
            $quantity = (int)$offersParameter['new_quantity'];
            $rest = (int)$offersParameter['rest'];

            $keys = ArrayHelper::getArrayKeys($offersParameter, $parameterName, true);
            $keys = array_filter(ArrayHelper::arrayToInt($keys));


            /**
             * Для акции кода С
             */
            if (User::isDeveloper() && $isProductCodeS) {
                $price = $discount->getDiscountedPrice($price);
            }


            foreach ($parameterName as $name) {
                $value = $offersParameter[$name] ?? '';
                if (!$value) {
                    continue;
                }

                $values = array_combine($keys, array_fill(0, sizeof($keys), [
                    'offerId' => $offerId,
                    'cardId' => $cardId,
                    'price' => $price,
                    'maxPrice' => $maxPrice,
                    'quantity' => $quantity,
                    'rest' => $rest,
                ]));

                if (isset($data[$value][$cardId])) {
                    $data[$value][$cardId] = $values + $data[$value][$cardId];
                } else {
                    $data[$value][$cardId] = $values;
                }
            }

        }

        return $data;
    }

    /**
     * Вернуть кол-во типов модификаций в товаре
     *
     * @return int
     */
    public function getCountModification()
    {
        $count = 0;
        foreach ($this->getParameters() as $property) {
            if ($this->getParameterData($property->code)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Получение значения ID свойства битриксового цвета
     * @return mixed
     */
    public static function getKfssColorBxPropId()
    {
        return self::$kfssColorBxPropId ?: (CmsContentProperty::find()->where(['code' => self::COLOR_CODE_BX])->one())->id;
    }

    /**
     * @return array
     */
    public function getDataTableSizeBlock()
    {
        return [
            'sizeClothes' => [
                1983, //Платья и сарафаны
                1675, //Юбки

                1944, //Верхняя одежда
                1978, //Верхняя одежда - весна осень
                1951, //Верхняя одежда - зимняя одежда

                1943, //Кардиганы, жакеты, пончо
                1981, //Блузы, рубашки, джемперы
                1674, //Туники
                1673, //Брюки
                1968, //Леггинсы
                1965, //Комбинезоны
                1966, //Комплекты одежды
                1942, //Домашняя одежда
                1939, //Нижнее белье
                1702, //Купальники
            ],
            'womenShoes' => [
                1781, //Туфли
                1782, //Ботинки и полуботинки
                1993, //Кеды и кроссовки
                2000, //Сапоги и полусапоги
                1797, //Домашняя обувь
                1784, //Другая обувь
            ],
            'ringsSizes' => [
                2065, //Кольца в золоте
                1731, //Кольца в серебре
                2053, //Кольца в бижутерии
            ],
            'menShoes' => [
                1973, //Мужская обувь
            ],
            'babyShoes' => [
                1962, //Детская одежда
                1800, //Для девочек
                1801, //Для мальчиков

            ],
            'manClothes' => [
                1961, //Мужская одежда
            ],
        ];
    }

    /**
     * @return bool
     */
    public function isShowTableSizeBlock()
    {
        $data = call_user_func_array('array_merge', $this->getDataTableSizeBlock());

        return in_array($this->model->tree_id, $data);
    }

    /**
     * Отрендерить таблицу свойств
     */
    public function renderTableSizesBlock()
    {
        $data = $this->getDataTableSizeBlock();

        $sizeClothes = $data['sizeClothes'];
        $womenShoes = $data['womenShoes'];
        $ringsSizes = $data['ringsSizes'];
        $menShoes = $data['menShoes'];
        $babyShoes = $data['babyShoes'];
        $manClothes = $data['manClothes'];

        $treeId = $this->model->tree_id;

        if (in_array($treeId, $sizeClothes)) {
            return \Yii::$app->view->renderFile(__DIR__ . '/size-tables/_clothes-woman.php');
        }

        if (in_array($treeId, $womenShoes)) {
            return \Yii::$app->view->renderFile(__DIR__ . '/size-tables/_shoes-woman.php');
        }

        if (in_array($treeId, $menShoes)) {
            return \Yii::$app->view->renderFile(__DIR__ . '/size-tables/_shoes-man.php');
        }

        if (in_array($treeId, $ringsSizes)) {
            return \Yii::$app->view->renderFile(__DIR__ . '/size-tables/_rings.php');
        }

        if (in_array($treeId, $babyShoes)) {
            return \Yii::$app->view->renderFile(__DIR__ . '/size-tables/_clothes-baby.php');
        }

        if (in_array($treeId, $manClothes)) {
            return \Yii::$app->view->renderFile(__DIR__ . '/size-tables/_clothes-man.php');
        }

        return false;
    }
}