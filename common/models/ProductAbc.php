<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-04-19
 * Time: 16:21
 */

namespace common\models;


use common\helpers\App;
use common\helpers\Promo;
use yii\db\Exception;

class ProductAbc extends \common\models\generated\models\BuhECommAbc
{

    public static function tableName()
    {
        return 'buh_e_comm_abc';
    }

    const TYPE_DAY = 1;
    const TYPE_WEEK = 2;
    const TYPE_CTS = 3;
    const TYPE_TOP6 = 4;
    const TYPE_BESTSELLER = 5;
    const TYPE_HIT = 7;
    const TYPE_FAVORITE = 8;

    public function beforeValidate()
    {
        //Параметр addition заведен прежде всего из-за необходимости хранить инфу о том с каким ЦТС-ом связан товар
        //Только для этой цели поле заводить имхо жирно, так что заведено общее поле для чего то дополнительного

        //Строковой тип поля имеет строгую валидацию типа, например числовые значения считаются ошибочными
        //https://github.com/yiisoft/yii2/issues/13327
        //Приходится хитрить
        if ($this->addition) {
            $this->addition = (string)$this->addition;
        }
        return parent::beforeValidate();
    }

    public static function getTypes($type = false)
    {
        $array = [
            self::TYPE_DAY => 'Товар дня',
            self::TYPE_WEEK => 'Товар недели',
            self::TYPE_CTS => 'Товар для цтс',
        ];
        return $type ? $array[$type] : $array;
    }

    public static function findDay()
    {
        $subQuery = static::find()->select('product_id')->andWhere(['type_id' => self::TYPE_DAY])->orderBy('order');
        $query = \common\models\Product::find()
            ->onlyLot()
            ->andWhere(['cms_content_element.id' => $subQuery])
            ->limit(20);

        if (false && Promo::is999()){
            $query->andWhere(['new_price' => 999]);
        }

        return $query->all();
    }

    public static function findWeek()
    {
        $subQuery = static::find()->select('product_id')->andWhere(['type_id' => self::TYPE_WEEK])->orderBy('order');
        $query = \common\models\Product::find()
            ->onlyLot()
            ->andWhere(['cms_content_element.id' => $subQuery])
            ->limit(20);

        if (false && Promo::is999()){
            $query->andWhere(['new_price' => 999]);
        }

        return $query->all();
    }

    public static function findCts()
    {
        $subQuery = static::find()->select('product_id')->andWhere(['type_id' => self::TYPE_CTS])->orderBy('order');
        return \common\models\Product::find()
            ->onlyLot()
            ->andWhere(['id' => $subQuery])
            ->limit(20)
            ->all();
    }

    public static function getBestseller()
    {
        $subQuery = static::find()->select('product_id')->andWhere(['type_id' => self::TYPE_BESTSELLER])->orderBy('order');
        return \common\models\Product::find()
            ->canSale()
            ->onlyLot()
            ->andWhere(['cms_content_element.id' => $subQuery])
            ->limit(Product::BADGE_BESTSELLER_LIMIT)
            ->all();
    }

    public static function getHit()
    {
        $subQuery = static::find()->select('product_id')->andWhere(['type_id' => self::TYPE_HIT])->orderBy('order');
        return \common\models\Product::find()
            ->canSale()
            ->onlyLot()
            ->andWhere(['cms_content_element.id' => $subQuery])
            ->all();
    }

    public static function getFavorite()
    {
        $subQuery = static::find()->select('product_id')->andWhere(['type_id' => self::TYPE_FAVORITE])->orderBy('order');
        return \common\models\Product::find()
            ->canSale()
            ->onlyLot()
            ->andWhere(['cms_content_element.id' => $subQuery])
            ->all();
    }

    public static function importProduct($typeId)
    {
        ProductAbc::deleteAll(['type_id' => $typeId]);
        foreach (BUFECommABC::find()->andWhere(['LotABC' => 'A', 'LotOrder' => 1, 'period' => 1])->orderBy('LotQty DESC')->each() as $index => $each) {
            /** @var BUFECommABCw $each */
            $model = new ProductAbc();
            $model->guid = $each->LotGUIDtext;
            $model->code = $each->LotCode;
            $model->type_id = $typeId;

            $productQuery = Product::find()
                ->byCode($each->LotCode)
                ->onlyLot()
                ->onlyActive()
                ->onlyPublic()
                ->hasQuantityNew()
                ->imageIdNotNull()
                ->priceMoreThanZero();

            if (false && Promo::is999()) {
                $productQuery->onlyIs999();
            }
            $product = $productQuery
                ->one();

            if ($product) {
                $model->product_id = $product->id;
                $model->order = $index + 1;
                if (!$model->save()) {
                    throw new Exception('Error import product abc');
                }
            }

        }
    }

    public static function importProductWeek($typeId)
    {
        ProductAbc::deleteAll(['type_id' => $typeId]);
        foreach (BUFECommABC::find()->andWhere(['LotABC' => 'A', 'LotOrder' => 1, 'period' => 7])->orderBy('LotQty DESC')->each() as $index => $each) {
            /** @var BUFECommABCw $each */
            $model = new ProductAbc();
            $model->guid = $each->LotGUIDtext;
            $model->code = $each->LotCode;
            $model->type_id = $typeId;

            $productQuery = Product::find()
                ->byCode($each->LotCode)
                ->onlyLot()
                ->onlyActive()
                ->onlyPublicNew()
                ->hasQuantityNew()
                ->imageIdNotNull()
                ->priceMoreThanZeroNew();

            if (false && Promo::is999()) {
                $productQuery->onlyIs999();
            }

            $product = $productQuery->one();

            if ($product) {
                $model->product_id = $product->id;
                $model->order = $index + 1;
                if (!$model->save()) {
                    throw new Exception('Error import product abc');
                }
            }

        }
    }

    public static function importProductCts()
    {
        if (App::isConsoleApplication()){
            echo ">>> Add lots related with CTS " . PHP_EOL;
            echo ">> [ ORDER  |  RESULT ] LotName [RELATED CTS INFO]" . PHP_EOL;
        }

        $typeId = ProductAbc::TYPE_CTS;

        ProductAbc::deleteAll(['type_id' => $typeId]);
        $productCtsPairs = BUFECommPairCTS::find()->orderBy('LotOrder')->all();
        foreach ($productCtsPairs as $index => $each) {
            /** @var BUFECommPairCTS $each */
            $model = new ProductAbc();
            $model->code = $each->LotCode;
            $model->type_id = $typeId;

            $productQuery = Product::find()
                ->byCode($each->LotCode)
                ->onlyLot()
                ->onlyActive()
                ->onlyPublicNew()
                ->hasQuantityNew()
                ->imageIdNotNull()
                ->priceMoreThanZeroNew();

            if (false && Promo::is999()) {
                $productQuery->onlyIs999();
            }

            $product = $productQuery->one();

            //* Связь с конкретным ЦТС (например при мультиЦТСности) *//

            $productCtsInfo = '---';

            if ($each->LotCodeCTS){
                //В данном случае без разницы на условия для продажи, главное что бы товар нашелся
                $productCtsQuery = Product::find()
                    ->byCode($each->LotCodeCTS)
                    ->onlyLot()
                    ->onlyActive();

                $productCtsInfo = 'RelTo: ' . $each->LotCodeCTS;

                $productCts = $productCtsQuery->one();
                if ($productCts){
                    //Для допродаж к ЦТС в доп поле будем писать идентификатор того ЦТС к которому оно относится (лот)
                    $model->addition = $productCts->id;
//                    $productCtsInfo .= " - {$productCts->name} // {$productCts->id}";
                    $productCtsInfo .= " - {$productCts->id}";
                }else{
                    $productCtsInfo .= " - PRODUCT NOT FOUND";
                }
            }

            //* /Связь с конкретным ЦТС (например при мультиЦТСности) *//

            if (App::isConsoleApplication()){
                $syncResult = ($product ? 'ADDED' : 'SKIPPED');
                echo "[{$each->LotOrder} | {$syncResult}] {$each->LotName} [{$productCtsInfo}]"  . PHP_EOL;
            }

            if ($product) {
                $model->product_id = $product->id;
                $model->order = $index + 1;
                if (!$model->save()) {
                    throw new Exception('Error import product abc. Errs: ' . var_export($model->getErrors(), true));
                }
            }

        }
    }

    /**
     * Импорт востребованных и хордовых товаров (стока?)
     *
     * @return bool
     * @throws Exception
     */
    public static function importProductTop6()
    {
        $typeId = ProductAbc::TYPE_TOP6;

        ProductAbc::deleteAll(['type_id' => $typeId]);

        //Сортируем по продажам что бы шло от самого к менее востребованного
        foreach (BUFECommTop6Lots::find()->orderBy('sum_n1n4Lot DESC')->each() as $index => $each) {
            /** @var BUFECommTop6Lots $each */
            $productQuery = Product::find()
                ->byCode($each->LotCode)
//                ->canSale() //Пожалуй бедем фильтровать на выводе
                ->onlyLot();

            if (false && Promo::is999()) {
                $productQuery->onlyIs999();
            }

            $product = $productQuery->one();

            if ($product) {
                $model = new ProductAbc();
                $model->guid = $product->new_guid;
                $model->code = $each->LotCode;
                $model->type_id = $typeId;
                $model->product_id = $product->id;
                $model->order = $index + 1;

                if (!$model->save()) {
                    throw new Exception('Error import product abc');
                }
            }
        }

        return true;
    }

    /**
     * Импорт бестселлеров
     *
     * @return bool
     * @throws Exception
     */
    public static function importProductBestseller()
    {
        if (App::isConsoleApplication()) {
            echo "Импортирую бестселлеры" . PHP_EOL;
        }

        $typeId = ProductAbc::TYPE_BESTSELLER;

        ProductAbc::deleteAll(['type_id' => $typeId]);

        //Сортируем по продажам что бы шло от самого к менее востребованного
        foreach (BUFECommTop6Lots::find()->orderBy('sum_n1n4Lot DESC')->each() as $index => $each) {
            /** @var BUFECommTop6Lots $each */
            $productQuery = Product::find()
                ->byCode($each->LotCode)
                ->canSale()
                ->onlyLot();

            if (false && Promo::is999()) {
                $productQuery->onlyIs999();
            }

            $product = $productQuery->one();

            if ($product) {
                $model = new ProductAbc();
                $model->guid = $product->new_guid;
                $model->code = $each->LotCode;
                $model->type_id = $typeId;
                $model->product_id = $product->id;
                $model->order = $index + 1;

                if (!$model->save()) {
                    throw new Exception('Error import product bestseller');
                }
            }
        }

        return true;
    }

    /**
     * Импорт хитов
     *
     * @return bool
     * @throws Exception
     */
    public static function importProductHit()
    {
        if (App::isConsoleApplication()) {
            echo "Импортирую хиты" . PHP_EOL;
        }

        $typeId = ProductAbc::TYPE_HIT;

        ProductAbc::deleteAll(['type_id' => $typeId]);

        //Раскладываем товары по их корневым разделам каталога
        //Выбираем необхоимый срез по рубрикам и добавляем в нашу табличку
        
        //Что бы не проверять одни и теже разделы
        $treeIndex = [];
        $productsByCategory = [];

        //Сортируем по продажам что бы шло от самого к менее востребованного
        foreach (BUFECommTop6Lots::find()->orderBy('sum_n1n4Lot DESC')->each() as $index => $each) {

            if (App::isConsoleApplication()){
                //echo "[{$index}] {$each->LotName}" . PHP_EOL;
            }

            /** @var BUFECommTop6Lots $each */
            $productQuery = Product::find()
                ->byCode($each->LotCode)
                ->canSale()
                ->onlyLot();

            if (false && Promo::is999()) {
                $productQuery->onlyIs999();
            }

            $product = $productQuery->one();

            if ($product) {
                $tree = null;

                //Для товара необходим корневой раздел каталога. Проверяем и, если необходимо, находим
                $treeProduct = \skeeks\cms\models\Tree::findOne($product->tree_id);

                if ($treeProduct->level != 2){
                    if (isset($treeIndex[$treeProduct->id])){
                        $tree = $treeIndex[$treeProduct->id];
                    }else{
                        $treeParents = $treeProduct->parents;
                        $treesTop = array_filter($treeParents, function ($tree){
                            return $tree->level == 2;
                        });
                        if ($treesTop){
                            $tree = current($treesTop);
                            $treeIndex[$treeProduct->id] = $tree;
                        }
                    }
                }else{
                    $tree = $treeProduct;
                }

                if ($tree){
                    if ($tree->level == 2){
                        if (!isset($productsByCategory[$tree->id])){
                            $productsByCategory[$tree->id] = [];
                        }

                        $productsByCategory[$tree->id][] = $product;
                    }
                }else{
                    if (App::isConsoleApplication()){
                        echo "Не могу найти дерево для treeId='{$product->tree_id}' (лот [{$product->id}] {$product->name})" . PHP_EOL;
                    }
                }
            }
        }
        
        if ($productsByCategory){
            $k = 0;
            foreach ($productsByCategory as $treeId => $products) {
                $i=0;
                foreach ($products as $product) {
                    $i++;

                    $model = new ProductAbc();
                    $model->guid = $product->new_guid;
                    $model->code = $product->code;
                    $model->type_id = $typeId;
                    $model->product_id = $product->id;
                    $model->order = ++$k;

                    if (!$model->save()) {
                        throw new Exception('Error import product hit');
                    }

                    if ($i == Product::BADGE_HIT_LIMIT){
                        break;
                    }
                }
            }
        }

        return true;
    }

    public static function importProductFavorite()
    {
        if (App::isConsoleApplication()) {
            echo "Импортирую любимые товары (Коэффициен = ".Product::BADGE_FAVORITE_RATIO.")" . PHP_EOL;
        }

        $typeId = ProductAbc::TYPE_FAVORITE;

        ProductAbc::deleteAll(['type_id' => $typeId]);

        $products = $productsQuery = BUFSiteTv::find()
            ->where(['>=', 'SumSiDiv', Product::BADGE_FAVORITE_RATIO])
            ->andWhere(['dt' => date('Y-m-d')])
            ->orderBy('SumSiDiv DESC')
            ->all();

        if (App::isConsoleApplication()) {
            echo "любимых товаров найдено - " . count($products)  . PHP_EOL;
        }

        if ($products) {
            foreach ($products as $index => $favProduct) {
                $product = Product::find()
                    ->byCode($favProduct->LOT_CODE)
                    ->onlyLot()
                    ->one();

                if ($product){
                    $model = new ProductAbc();
                    $model->guid = $product->new_guid;
                    $model->code = $product->code;
                    $model->type_id = $typeId;
                    $model->product_id = $product->id;
                    $model->order = $index+1;

                    if (!$model->save()) {
                        throw new Exception('Error import product favorite');
                    }
                }
            }
        }

        return true;
    }
}