<?php
/**
 * php yii product/sync-new-not-public
 *
 * php yii product/hide-no-image-cards
 * php yii product/update-badges
 * php yii product/sync-products-top-six
 * php yii product/sync-analytics-for-badges
 * php yii product/sync-products-bestseller
 * php yii product/sync-products-hit
 * php yii product/sync-products-favorite
 *
 * php yii product/sync-products-of-day
 * php yii product/sync-products-of-week
 * php yii product/sync-products-cts
 *
 * php yii product/update-all-product-price ID //ID любой сущности связанной с товаром
 * php yii product/update-all-price LIMIT OFFSET
 * php yii product/update-base-price [LIMIT] [PRODUCT_ID]
 *
 * php yii product/export-products-to-analytics [LIMIT] [PRODUCTS_PER_QUERY]
 *
 * php yii product/update-tree-id-by-row SOURCE_TYPE //0 - классификатор / 0 - рубрикатор
 * php yii product/update-tree-id-by-node-group SOURCE_TYPE //0 - классификатор / 0 - рубрикатор
 * php yii product/requeue LIMIT OFFSET ONLYLOT
 *
 * php yii product/sync-analytics-price-types
 */

namespace console\controllers;


use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use common\helpers\ArrayHelper;
use common\helpers\Common as CommonHelper;
use common\helpers\Filter;
use common\helpers\Price;
use common\helpers\Product;
use common\helpers\Size;
use common\models\BUFEcommPriceType;
use common\models\BUFECommProducts;
use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\CmsContentProperty;
use common\models\generated\models\ProductParam;
use common\models\generated\models\ProductTreeNode;
use common\models\NewProduct;
use common\models\Product as ProductModel;
use common\models\ProductAbc;
use common\models\ProductAbcAddition;
use common\models\ProductParamType;
use common\models\Guid;
use common\models\QueueLog;
use console\jobs\UpdatePriceJob;
use modules\shopandshow\models\shop\ShopTypePrice;
use modules\shopandshow\models\shop\SsShopProductPrice;
use skeeks\cms\models\CmsContentElementProperty;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\db\Exception;

class ProductController extends Controller
{

    public function actionHideNoImageCards()
    {
        Product::hideNoImageCards();
    }

    public function actionSyncProductsOfDay()
    {
        ProductAbc::importProduct(ProductAbc::TYPE_DAY);
    }

    public function actionSyncProductsOfWeek()
    {
        ProductAbc::importProductWeek(ProductAbc::TYPE_WEEK);
    }

    public function actionSyncProductsCts()
    {
        ProductAbc::importProductCts();
    }

    public function actionSyncProductsTopSix()
    {
        ProductAbc::importProductTop6();
    }

    public function actionSyncAnalyticsForBadges()
    {
        ProductAbc::importProductBestseller();
        ProductAbc::importProductHit();
        ProductAbc::importProductFavorite();

        return true;
    }

    public function actionSyncProductsBestseller()
    {
        ProductAbc::importProductBestseller();
    }

    public function actionSyncProductsHit()
    {
        ProductAbc::importProductHit();
    }

    public function actionSyncProductsFavorite()
    {
        ProductAbc::importProductFavorite();
    }

    public function actionSyncProductAdditional()
    {
        ProductAbcAddition::import();
    }

    public function actionTestRelation()
    {
        $startTime = microtime(true);

        $lot = NewProduct::findOne(1753693);
        echo $lot->getPropertyNotPublic();
//        $curPriceTypeId = $lot->getRelatedPropertiesModel()->getAttribute('PRICE_ACTIVE');
//        echo $curPriceTypeId;
        echo PHP_EOL;
        /*stuff is going on*/

        echo "Elapsed time is: " . (microtime(true) - $startTime) . " seconds" . PHP_EOL;
    }

    public function actionUpdatePrice($id)
    {

        $this->stdout('Update Product' . PHP_EOL);

        Yii::$app->queue->push(new UpdatePriceJob([
            'id' => $id,
        ]));

//        Product::updatePrice($id);
        $this->stdout('Update Product Success' . PHP_EOL);
    }

    public function actionUpdatePriceNow($id)
    {
        try {
            \common\helpers\Product::updatePrice($id);
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    public function actionUpdatePriceCard($id)
    {
        Product::updatePriceCard($id);
    }

    public function actionUpdatePriceLot($id)
    {
        Product::updatePriceLot($id);
    }

    public function actionUpdatePriceModification($id)
    {
        $offer = NewProduct::findOne(['id' => $id, 'content_id' => OFFERS_CONTENT_ID]);

        /** @var NewProduct $card */
        $card = $offer->parentContentElement;
        $lot = $card->parentContentElement;
        Product::recalculateModification($offer, $lot);
    }


    public function actionUpdatePriceValueOne()
    {
        $query = \common\models\NewProduct::find()->onlyModification()->priceValueOne();

        foreach ($query->each() as $product) {
            /** @var NewProduct $product */
            Yii::$app->queue->push(new UpdatePriceJob([
                'id' => $product->id,
            ]));
        }
    }


    public function actionUpdateAllProductPrice($productId){
        return Product::updatePriceAll($productId);
    }

    public function actionUpdateAllPrice($limit, $offset)
    {
        $this->stdout("Start Update All Price" . PHP_EOL);
        $productsQuery = ProductModel::find()->onlyLot()->onlyActive()->hasQuantityNew()->limit($limit)->offset($offset);

        /** @var ProductModel $lot */
        foreach ($productsQuery->each() as $lot) {

            echo "Пересчет лота [{$lot->id} | {$lot->code}]" . PHP_EOL;

            $offers = ProductModel::getProductOffers($lot->id);
            if ($offers){
                echo "Модификаций для пересчета: " . count($offers) . PHP_EOL;

                $i = 0;
                foreach ($offers as $offer) {
                    $i++;
                    try {
                        Yii::$app->queue->push(new UpdatePriceJob([
                            'id' => $offer->id,
                        ]));
//                        $this->stdout("{$i}) Update {$offer->id}" . PHP_EOL);
                    } catch (\Exception $e) {
                        $this->stderr('Error ' . $e->getMessage() . PHP_EOL);
                    }
                }
            }
        }
        $this->stdout('End Update All Price' . PHP_EOL);
    }

    public function actionFillPrice($limit = 100)
    {
        $subQuery = SsShopProductPrice::find()->select('product_id');

        $query = NewProduct::find()
            ->andWhere(['content_id' => [NewProduct::LOT, NewProduct::CARD, NewProduct::MOD]])
            ->andWhere(['not in', 'id', $subQuery])
            ->limit($limit);

        foreach ($query->each() as $product) {
            Yii::$app->queue->push(new UpdatePriceJob([
                'id' => $product->id,
            ]));
        }
    }

    public function actionUpdateFromFile($path)
    {
        $reader = ReaderFactory::create(Type::CSV);

        $reader->open($path);


        $i = 0;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                if ($index == 1) {
                    continue;
                }
                $i++;
                $guidLot = $row[0];
                $guidPriceType = $row[1];
                /** @var NewProduct $lot */
                $lot = NewProduct::find()->byGuid($guidLot)->one();
                if ($lot) {
//                    $this->stdout('Find lot guid ' . $guidLot . PHP_EOL);
                    $shopTypePrice = ShopTypePrice::getShopTypePriceByGuid($guidPriceType);
                    if ($shopTypePrice) {
//                        $this->stdout('Find price type guid ' . $guidPriceType . PHP_EOL);
                        foreach ($lot->childrenContentElements as $item) {
                            foreach ($item->childrenContentElements as $childrenContentElement) {
                                $r = CmsContentElementProperty::updateAll(['value' => $shopTypePrice->id], ['element_id' => $childrenContentElement->id, 'property_id' => 174]);
//                                $this->stdout("$i) ". 'Result ' . $r . ', id ' . $childrenContentElement->id.', price '.$shopTypePrice->id . PHP_EOL);
                            }
                        }

                    } else {
                        $this->stderr('Not find price type ' . $guidPriceType . ' not found' . PHP_EOL);
                    }
                } else {
                    $this->stderr('Not find lot ' . $guidLot . ' not found' . PHP_EOL);
                }
                // do stuff with the row
                $this->stdout("Done lot #{$i}" . PHP_EOL);
            }
        }

        $reader->close();

    }


    public function actionUpdateModificationAll()
    {
        foreach (NewProduct::find()->onlyModification()->each() as $mod) {

            Yii::$app->queue->push(new UpdatePriceJob([
                'id' => $mod->id,
            ]));

        }
    }

    public function actionAddNewFieldsLot()
    {
        $products = NewProduct::find()
            ->onlyLot()
            ->onlyActive();

        foreach ($products->each() as $product) {
            /** @var NewProduct $product */
            $product->new_quantity = $product->shopProduct ? $product->shopProduct->quantity : 0;
            $product->new_price = $product->price ? $product->price->price : 0;
            $product->new_price_old = $product->price ? $product->price->max_price : 0;
            $product->new_discount_percent = $product->price ? $product->price->discount_percent : 0;
            $product->save(false);

            $this->stdout('Update Product ' . $product->id . PHP_EOL);
        }
    }

    public function actionAddNewFieldsCard()
    {
        $products = NewProduct::find()
            ->onlyCard();
//            ->where(['id' => 1035858]);

        foreach ($products->each() as $product) {


            /** @var NewProduct $product */
            $product->new_quantity = $product->shopProduct ? $product->shopProduct->quantity : 0;
            $product->new_price = $product->price ? $product->price->price : 0;
            $product->new_price_old = $product->price ? $product->price->max_price : 0;
            $product->new_discount_percent = $product->price ? $product->price->discount_percent : 0;
            $product->save(false);

            $this->stdout('Update Product ' . $product->id . PHP_EOL);
        }
    }

    public function getContentPropertyProps($value = false)
    {
        $array = [
            'new_lot_num' => 'LOT_NUM',
            'new_lot_name' => 'LOT_NAME',
            'new_characteristics' => 'HARAKTERISTIKI',
            'new_technical_details' => 'TECHNICAL_DETAILS',
            'new_product_kit' => 'KOMPLEKTACIA',
            'new_advantages' => 'PREIMUSHESTVA',
            'new_advantages_addons' => 'PREIMUSHESTVA_ADDONS',
            'new_not_public' => 'NOT_PUBLIC',
            'new_rest' => 'REST',
            'new_price_active' => 'PRICE_ACTIVE',
            'new_brand_id' => 'BRAND',
            'new_season_id' => 'KFSS_BRAND',
            'new_rating' => 'RATING',

//            'new_color_id' => 'KFSS_COLOR',
//            'new_color_bx_id' => 'KFSS_COLOR_BX',
        ];
        return $value ? $array[$value] : $array;
    }

    public function actionLastProductProps()
    {
        $date = new \DateTime(date('Y-m-d H:i:s'));
        $date->modify('-20 days');
        $date->format('U');

        $mods = NewProduct::find()
            ->select(['id', 'parent_content_element_id'])
            ->onlyModification()
//            ->andWhere(['>','created_at',$date->format('U')])
            ->asArray()
            ->all();

//        [236,246,261,267]//обувь
//        [245,266]//одежда
//        [244,253]//кольца

        foreach ($mods as $mod) {
            $props = CmsContentElementProperty::find()
                ->select(['value', 'cce.name'])
                ->leftJoin('cms_content_element as cce', 'cce.id = ' . CmsContentElementProperty::tableName() . '.value')
                ->andWhere(['=', 'element_id', $mod['id']])
//                ->andWhere(['IN','property_id',[244,253]])
//                ->andWhere(['IN','property_id',[236,246,261,267]])
                ->andWhere(['IN', 'property_id', [245, 266]])
                ->asArray()
                ->all();

            if ($props) {
                foreach ($props as $prop) {

                    $destSize = \common\helpers\Size::getDestSizeBySource((int)$prop['value']);
                    if ($destSize) {
//                        continue;
                        $param = ProductParam::find()
                            ->andWhere(['type_id' => 2])
                            ->andWhere(['name' => $destSize['name']])
                            ->one();
                        if (!$param) {
                            $param = new ProductParam();
                            $param->type_id = 2;
                            $param->name = $destSize['name'];
                            $param->save();
                        }

                        $card = NewProduct::find()->where(['id' => $mod['parent_content_element_id']])->one();
                        $this->stdout('insert ModId ' . $mod['id'] . ', CardId ' . $card->id . ', SizeId ' . $param->id, PHP_EOL);

                        if ($card) {
                            Filter::addProductParam(
                                $mod['id'],
                                $card->id,
                                $card->parent_content_element_id,
                                $param->id
                            );
                        }

                    } else {
                        continue;
                        $param = ProductParam::find()
                            ->andWhere(['type_id' => 6])
                            ->andWhere(['name' => $prop['name']])
                            ->one();
                        if (!$param) {
                            $param = new ProductParam();
                            $param->type_id = 6;
                            $param->name = $prop['name'];
                            $param->save();
                        }

                        $card = NewProduct::find()->where(['id' => $mod['parent_content_element_id']])->one();
                        $this->stdout('insert ModId ' . $mod['id'] . ', CardId ' . $card->id . ', SizeId ' . $param->id, PHP_EOL);

                        if ($card) {
                            Filter::addProductParam(
                                $mod['id'],
                                $card->id,
                                $card->parent_content_element_id,
                                $param->id
                            );
                        }

//                        echo '<pre>';
//                        print_r($param);
//                        echo '</pre>';
//                        die();
                    }
                }
            }
        }
    }

    public function actionNewPropsCatalogSize()
    {
        $sizePropsGroups = Size::getSizeProperties();

        $date = new \DateTime(date('Y-m-d H:i:s'));
        $date->modify('-10 days');
        $date->format('U');

        foreach ($sizePropsGroups as $propGroupSize) {
            $this->stdout('Check Size Group  ' . $propGroupSize['name'], PHP_EOL);
            $sizes = CmsContentElementProperty::find()
                ->andWhere(['property_id' => $propGroupSize['id']])
                ->andWhere(['!=', 'value', '']);

            $this->stdout('Get Sizes ' . $propGroupSize['name'], PHP_EOL);
            foreach ($sizes->each() as $size) {

                $destSize = \common\helpers\Size::getDestSizeBySource((int)$size->value);
                if ($destSize) {
                    $this->stdout('Fetch Dest Size ' . $destSize['name'], PHP_EOL);

                    $etalonScale = CmsContent::findOne($destSize['etalon_id']);
                    if ($etalonScale) {
                        $paramType = ProductParamType::find()->where(['code' => $etalonScale->code])->one();
                        if (!$paramType) {
                            $paramType = new ProductParamType();
                            $paramType->name = $etalonScale->name;
                            $paramType->code = $etalonScale->code;
                            $paramType->guid = $etalonScale->code;
                            $paramType->save();
                        }
                        $param = ProductParam::find()
                            ->andWhere(['type_id' => $paramType->id])
                            ->andWhere(['name' => $destSize['name']])
                            ->one();
                        if (!$param) {
                            $param = new ProductParam();
                            $param->type_id = $paramType->id;
                            $param->name = $destSize['name'];
                            $param->save();
                        }

                        $products = NewProduct::find()
                            ->onlyModification()
                            ->leftJoin('shop_product', 'shop_product.id=' . NewProduct::tableName() . '.id')
                            ->onlyActive()
                            ->andWhere(['>', 'created_at', $date->format('U')])

//                            ->onlyActiveParent()
                            ->leftJoin(CmsContentElementProperty::tableName(), CmsContentElementProperty::tableName() . '.element_id = cms_content_element.id')
                            ->andWhere(['value' => $size->value])
                            ->andWhere('shop_product.quantity > 0');


                        $q = $products->createCommand()->getRawSql();
                        $this->stdout($q, PHP_EOL);

                        foreach ($products->each() as $product) {

                            $card = NewProduct::find()->where(['id' => $product->parent_content_element_id])->one();
                            $this->stdout('insert ModId ' . $product->id . ', CardId ' . $card->id . ', SizeId ' . $param->id, PHP_EOL);
                            if ($card) {
                                Filter::addProductParam(
                                    $product->id,
                                    $card->id,
                                    $card->parent_content_element_id,
                                    $param->id
                                );
                            }
                        }
                    }
                } else {
                    continue;
                    if (in_array($size->property_id, [236, 261, 267, 246])) {
                        $sizeEl = CmsContentElement::find()->where(['id' => $size->value])->asArray()->one();

                        $etalonScale = CmsContent::find()->where(['code' => 'KFSS_RAZMER_OBUVI'])->one();
                        $paramType = ProductParamType::find()->where(['code' => 'KFSS_RAZMER_OBUVI'])->one();
                        if (!$paramType) {
                            $paramType = new ProductParamType();
                            $paramType->name = $etalonScale->name;
                            $paramType->code = $etalonScale->code;
                            $paramType->guid = $etalonScale->code;
                            $paramType->save();
                        }

                        $param = ProductParam::find()
                            ->andWhere(['type_id' => $paramType->id])
                            ->andWhere(['name' => (int)$sizeEl['name']])
                            ->one();

                        if (!$param) {
                            $param = new ProductParam();
                            $param->type_id = $paramType->id;
                            $param->name = $sizeEl['name'];
                            $param->save();
                        }

                        $products = NewProduct::find()
                            ->onlyModification()
                            ->leftJoin('shop_product', 'shop_product.id=' . NewProduct::tableName() . '.id')
                            ->onlyActive()
                            ->leftJoin(CmsContentElementProperty::tableName(), CmsContentElementProperty::tableName() . '.element_id = cms_content_element.id')
                            ->andWhere(['value' => $size->value])
                            ->andWhere(['>', 'cms_content_element.id', 8459000])
                            ->andWhere('shop_product.quantity > 0');


                        $q = $products->createCommand()->getRawSql();
                        $this->stdout($q, PHP_EOL);

                        foreach ($products->each() as $product) {

                            $card = NewProduct::find()->where(['id' => $product->parent_content_element_id])->one();
                            $this->stdout('insert ModId ' . $product->id . ', CardId ' . $card->id . ', SizeId ' . $param->id, PHP_EOL);
                            if ($card) {
                                Filter::addProductParam(
                                    $product->id,
                                    $card->id,
                                    $card->parent_content_element_id,
                                    $param->id
                                );
                            }
                        }
                    }

                    if (in_array($size->property_id, [253])) {
                        $sizeEl = CmsContentElement::find()->where(['id' => $size->value])->asArray()->one();

                        $etalonScale = CmsContent::find()->where(['code' => 'KFSS_RAZMER_KOLTSA'])->one();
                        $paramType = ProductParamType::find()->where(['code' => 'KFSS_RAZMER_KOLTSA'])->one();
                        if (!$paramType) {
                            $paramType = new ProductParamType();
                            $paramType->name = $etalonScale->name;
                            $paramType->code = $etalonScale->code;
                            $paramType->guid = $etalonScale->code;
                            $paramType->save();
                        }

                        $param = ProductParam::find()
                            ->andWhere(['type_id' => $paramType->id])
                            ->andWhere(['name' => (int)$sizeEl['name']])
                            ->one();

                        if (!$param) {
                            $param = new ProductParam();
                            $param->type_id = $paramType->id;
                            $param->name = $sizeEl['name'];
                            $param->save();
                        }

                        $products = NewProduct::find()
                            ->onlyModification()
                            ->leftJoin('shop_product', 'shop_product.id=' . NewProduct::tableName() . '.id')
                            ->onlyActive()
                            ->leftJoin(CmsContentElementProperty::tableName(), CmsContentElementProperty::tableName() . '.element_id = cms_content_element.id')
                            ->andWhere(['value' => $size->value])
                            ->andWhere(['>', 'cms_content_element.id', 8459000])
                            ->andWhere('shop_product.quantity > 0');


                        $q = $products->createCommand()->getRawSql();
                        $this->stdout($q, PHP_EOL);

                        foreach ($products->each() as $product) {

                            $card = NewProduct::find()->where(['id' => $product->parent_content_element_id])->one();
                            $this->stdout('insert ModId ' . $product->id . ', CardId ' . $card->id . ', SizeId ' . $param->id, PHP_EOL);
                            if ($card) {
                                Filter::addProductParam(
                                    $product->id,
                                    $card->id,
                                    $card->parent_content_element_id,
                                    $param->id
                                );
                            }
                        }
                    }
                    if (in_array($size->property_id, [253])) {
                        $sizeEl = CmsContentElement::find()->where(['id' => $size->value])->asArray()->one();

                        $etalonScale = CmsContent::find()->where(['code' => 'KFSS_RAZMER_KOLTSA'])->one();
                        $paramType = ProductParamType::find()->where(['code' => 'KFSS_RAZMER_KOLTSA'])->one();
                        if (!$paramType) {
                            $paramType = new ProductParamType();
                            $paramType->name = $etalonScale->name;
                            $paramType->code = $etalonScale->code;
                            $paramType->guid = $etalonScale->code;
                            $paramType->save();
                        }

                        $param = ProductParam::find()
                            ->andWhere(['type_id' => $paramType->id])
                            ->andWhere(['name' => (int)$sizeEl['name']])
                            ->one();

                        if (!$param) {
                            $param = new ProductParam();
                            $param->type_id = $paramType->id;
                            $param->name = $sizeEl['name'];
                            $param->save();
                        }

                        $products = NewProduct::find()
                            ->onlyModification()
                            ->leftJoin('shop_product', 'shop_product.id=' . NewProduct::tableName() . '.id')
                            ->onlyActive()
                            ->leftJoin(CmsContentElementProperty::tableName(), CmsContentElementProperty::tableName() . '.element_id = cms_content_element.id')
                            ->andWhere(['value' => $size->value])
                            ->andWhere('shop_product.quantity > 0');


                        $q = $products->createCommand()->getRawSql();
                        $this->stdout($q, PHP_EOL);

                        foreach ($products->each() as $product) {

                            $card = NewProduct::find()->where(['id' => $product->parent_content_element_id])->one();
                            $this->stdout('insert ModId ' . $product->id . ', CardId ' . $card->id . ', SizeId ' . $param->id, PHP_EOL);
                            if ($card) {
                                Filter::addProductParam(
                                    $product->id,
                                    $card->id,
                                    $card->parent_content_element_id,
                                    $param->id
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    public function actionNewPropsCatalogColor()
    {
        $color = CmsContent::find()->where(['code' => 'KFSS_COLOR'])->one();
        if (!$color)
            return false;

        $guid = Guid::findOne($color->guid_id);
        if (!$guid)
            return false;
        $paramType = ProductParamType::find()->where(['code' => 'KFSS_COLOR'])->one();
        if (!$paramType) {
            $paramType = new ProductParamType();
            $paramType->name = $color->name;
            $paramType->code = $color->code;
            $paramType->guid = $guid->guid;
            $paramType->save();
        }

        $filterColors = \common\helpers\Color::getFilterColors();

        if ($filterColors) {
            foreach ($filterColors as $id => $color) {

                $param = \common\models\ProductParam::find()->where(['name' => $color])->one();
                if (!$param) {
                    $param = new \common\models\ProductParam();
                    $param->name = $color;
                    $param->type_id = $paramType->id;
                    $param->created_at = time();
                    $param->save();
                }

                $ralatedColors = \common\helpers\Color::getColorsByFilterColorId($id);
                if (!$ralatedColors)
                    continue;
                $products = NewProduct::find()
                    ->onlyCard()
                    ->leftJoin(CmsContentElementProperty::tableName(), CmsContentElementProperty::tableName() . '.element_id = cms_content_element.id')
                    ->andWhere("value IN(" . implode(',', $ralatedColors) . ")");

                foreach ($products->each() as $product) {

                    $modifications = NewProduct::find()
                        ->onlyModification()
                        ->andWhere(['parent_content_element_id' => $product->id]);

                    foreach ($modifications->each() as $modification) {
                        $this->stdout('insert Product ' . $product->id . ', modification ' . $modification->id . ' Color ' . $param->id, PHP_EOL);
                        Filter::addProductParam(
                            $modification->id,
                            $product->id,
                            $product->parent_content_element_id,
                            $param->id
                        );
                    }
                }
            }
        }
    }

    public function actionNewPropsCatalogSeason()
    {
        $codes = [
            230 => 'KFSS_SEASON',
            229 => 'KFSS_BRAND',
        ];

        foreach ($codes as $prop_id => $code) {
            $season = CmsContent::find()->where(['code' => $code])->one();
            if (!$season)
                return false;
            $guid = Guid::findOne($season->guid_id);
            if (!$guid)
                return false;

            $paramType = ProductParamType::find()->where(['code' => $code])->one();
            if (!$paramType) {
                $paramType = new ProductParamType();
                $paramType->name = $season->name;
                $paramType->code = $season->code;
                $paramType->guid = $guid->guid;
                $paramType->save();
            }
            $lots = NewProduct::find()
                ->select([
                    NewProduct::tableName() . '.id AS lot_id',
                    'ccep.value AS param_id',
                    'cce.name AS param_name'
                ])
                ->onlyLot()
                ->leftJoin(CmsContentElementProperty::tableName() . ' as ccep', 'ccep.element_id = ' . NewProduct::tableName() . '.id')
                ->leftJoin(CmsContentElement::tableName() . ' as cce', 'cce.id = ccep.value')
                ->andWhere(['=', 'ccep.property_id', $prop_id])
                ->asArray();

            foreach ($lots->each() as $lot) {

                $param = \common\models\ProductParam::find()
                    ->andWhere(['=', 'name', $lot['param_name']])
                    ->andWhere(['=', 'type_id', $paramType->id])
                    ->one();

                if (!$param) {
                    $param = new \common\models\ProductParam();
                    $param->name = $lot['param_name'];
                    $param->type_id = $paramType->id;
                    $param->created_at = time();
                    $param->save();
                }

                $cards = NewProduct::find()
                    ->select(['id'])
                    ->onlyCard()
                    ->andWhere(['=', 'parent_content_element_id', $lot['lot_id']])
                    ->asArray();

                foreach ($cards->each() as $card) {

                    $mods = NewProduct::find()
                        ->select(['id'])
                        ->onlyModification()
                        ->andWhere(['=', 'parent_content_element_id', $card['id']])
                        ->asArray();
                    $k = 0;
                    foreach ($mods->each() as $mod) {

                        if ($k == 0) {
                            $this->stdout('insert Product ' . $lot['lot_id'] . ', card  ' . $card['id'] . ', modification ' . $mod['id'] . ' id_param ' . $param->id, PHP_EOL);
                            Filter::addProductParam(
                                $mod['id'],
                                $card['id'],
                                $lot['lot_id'],
                                $param->id
                            );
                        }
                        $k++;
                    }
                }
            }
        }
    }

    public function actionUpdateProp()
    {


        foreach ($this->getContentPropertyProps() as $contentPropertyProp => $value) {
            echo $contentPropertyProp . PHP_EOL;
            $code = $this->getContentPropertyProps($contentPropertyProp);
            $cmsContentProperty = CmsContentProperty::findOne(['code' => $code]);
            $r = Product::updatePropFromContentProperty($contentPropertyProp, $cmsContentProperty->id);
            echo $r . PHP_EOL;
        }


        foreach (['new_guid', 'new_quantity', 'new_price', 'new_price_old'] as $item) {
            echo $item . PHP_EOL;
            $r = Product::updatePropFromNonContentProperty($item);
            echo $r . PHP_EOL;
        }
    }

    /** Пересчет от указанного элемента и выше по древу
     *
     * @param $id
     * @return bool
     */
    public function actionUpdateQuantity($id)
    {
        return Product::updateQuantity($id);
    }

    /** Пересчет всех сущностей по товару (по факту все карточки и выше)
     *
     * @param $id - лот/карточка/модификация
     * @return bool
     */
    public function actionUpdateQuantityAll($id)
    {
        return Product::updateQuantityAll($id);
    }

    /** Синхранизация not_public=Y -> new_not_public (только установка 1, на 0 не сбрасывается)
     *
     * @return int
     */
    public function actionSyncNewNotPublic()
    {
        return Product::syncNewNotPublic();
    }

    /** Обновлене плашек 1 (эфир) и 2 (аналитика) уровней
     *
     * @return bool
     */
    public function actionUpdateBadges()
    {
        Product::updateBadge1();
        Product::updateBadge2();

        //запускаем обновление автосборок с бейджами
        \Yii::$app->runAction('segment/scheduled-generation');

        //[DEPREACTED] Так как с плашками тесно связана сортировка то запустим ее следом
        //Почему то на бою либо есть в плашках Суперскидка, но нет сортировки этого типа, либо наоборот ((
        //Приходится запускать пересчет сортировки отдельно ((
        //Product::updateSortWeight();

        return true;
    }

    /** Обновляет сортировочный вес на основе плашек
     *
     * @return bool
     */
    public function actionUpdateSortWeight()
    {
        return Product::updateSortWeight();
    }

    public function actionUpdateBasePrice($id = false)
    {
        return Product::updateBasePrice($id);
    }

    /** Выгрузка списка товаров для аналитики. Выгружает только новые (для аналитики) товары.
     *
     * @param int $limit - сколько товаров необходимо выгрузить
     * @param int $productsPerQuery - для пакетной вставки, сколько элементов должно быть в блоке
     * @return bool
     */
    public function actionExportProductsToAnalytics($limit = 10000, $productsPerQuery = 1000)
    {
        //Находим последний добавленый в аналитику товар и добавляем все последующие товары из нашей БД
        $lastProduct = BUFECommProducts::find()->orderBy(['product_id' => SORT_DESC])->limit(1)->one();

        $productsQuery = ProductModel::find()
            ->andWhere(['content_id' => ProductModel::getAllEntityTypeContentId()])
            ->orderBy(['id' => SORT_ASC]);

        if ($lastProduct){
            $productsQuery->andWhere(['>', 'id', $lastProduct->product_id]);
        }

        if ($limit){
            $productsQuery->limit($limit);
        }

        if ($productsNum = $productsQuery->count()){
            $this->stdout("Новых товаров для экспорта в аналитику (всего) - {$productsNum}" . PHP_EOL);
            if ($productsNum > $limit){
                $this->stdout(" > выгружена будет лишь часть - {$limit} элементов" . PHP_EOL);
            }

            /** @var ProductModel $product */
            foreach ($productsQuery->each() as $product) {

                switch ($product->content_id){
                    case 2: $productType = 'LOT'; break;
                    case 5: $productType = 'CARD'; break;
                    case 10: $productType = 'MOD'; break;
                }

                if ($productType){
                    $productsForExport[] = [
                        'created_at' => date("Y-m-d H:i:s", $product->created_at),
                        'type' => $productType,
                        'product_id' => $product->id,
                        'OFFCNT_ID' => $product->kfss_id
                    ];
                }else{
                    $this->stdout("Неведомая муть типа '{$product->content_id}'" . PHP_EOL);
                }
            }

            if ($productsForExport){
                //Пакетная вставка
                $exportBlocks = array_chunk($productsForExport, $productsPerQuery);

                $count = count($exportBlocks);
                $counterStep = $count / 100; //каждый 1 процента, сколько это в штуках

                $this->stdout("Blocks for export = {$count}" . PHP_EOL);

                $counterGlobal = 0;
                $counter = 0;
                Console::startProgress(0, $count);
                foreach ($exportBlocks as $exportBlock) {
                    $counterGlobal++;
                    $counter++;

                    if ($counter >= $counterStep || $counterGlobal == $count) {
                        $counter = 0;
                        Console::updateProgress($counterGlobal, $count);
                    }

                    try{
                        \Yii::$app->dbStat->createCommand()
                            ->batchInsert(
                                BUFECommProducts::tableName(),
                                [
                                    'created_at',
                                    'type',
                                    'product_id',
                                    'OFFCNT_ID'
                                ],
                                $exportBlock
                            )
                            ->execute();
                    }catch (Exception $e){
                        var_dump($e->getMessage());
                    }
                }
            }
        }else{
            $this->stdout("Новых товаров для экспорта в аналитику - НЕТ" . PHP_EOL);
        }

        return true;
    }

    //Устанавливает tree_id для товаров из указанного источника - классификатора или рубрикатора
    public function actionUpdateTreeIdByRow($sourceType, $contentId = 0)
    {
        switch ($sourceType){
            case 0:
                $treeIdSourceColumn = 'tree_id';
                break;
            case 1:
                $treeIdSourceColumn = 'node_id';
                break;
            default:
                return false;
        }

        //Выбираем только то что могло меняться, то есть то где есть оба идентификатора деревьев
        $productsTreeDataQuery = ProductTreeNode::find()
            ->select(['element_id', 'tree_id' => $treeIdSourceColumn])
            ->where(['>', 'tree_id', 0]);

        if ($sourceType != 0){
            $productsTreeDataQuery->andWhere(['>', 'node_id', 0]);
        }

        if ($contentId && in_array($contentId, [2,5,10])){
            $productsTreeDataQuery->andWhere(['content_id' => $contentId]);
        }

        $command = \Yii::$app->db->createCommand("UPDATE " . \common\models\Product::tableName() . " SET tree_id=:treeId WHERE content_id IN (2,5,10) AND id=:elementId");
        $command->bindParam(':elementId', $elementId);
        $command->bindParam(':treeId', $treeId);

        CommonHelper::startTimer('setTreeId');

        $i = 0;
        foreach ($productsTreeDataQuery->each() as $productTreeData) {
            $i++;
            $elementId = $productTreeData['element_id'];
            $treeId = $productTreeData['tree_id'];

            if (\common\helpers\App::isConsoleApplication()){
                Console::stdout("[{$i}] ElementId={$elementId}, set tree_id={$treeId}" . PHP_EOL);
            }

            if ($elementId && $treeId){
                try {
                    $affected = $command->execute();
                } catch (Exception $e) {
                    //Error
                }
            }
        }

        Console::stdout("Done. " . CommonHelper::getTimerTime('setTreeId'));

        return true;
    }

    //Устанавливает tree_id для товаров из указанного источника - классификатора или рубрикатора
    public function actionUpdateTreeIdByNodeGroup($sourceType, $contentId = 0)
    {
        $productsPerQuery = 5000;

        switch ($sourceType){
            case 0:
                $treeIdSourceColumn = 'tree_id';
                break;
            case 1:
                $treeIdSourceColumn = 'node_id';
                break;
            default:
                return false;
        }

        //Выбираем только то что могло меняться, то есть то где есть оба идентификатора деревьев
        $productsTreeDataQuery = ProductTreeNode::find()
            ->select(['element_id', 'tree_id' => $treeIdSourceColumn])
            ->where(['>', 'tree_id', 0]);

        if ($sourceType != 0){
            $productsTreeDataQuery->andWhere(['>', 'node_id', 0]);
        }

        if ($contentId && in_array($contentId, [2,5,10])){
            $productsTreeDataQuery->andWhere(['content_id' => $contentId]);
        }

        //Разложим товары по разделам что бы одним запросом обновить все товары и выставить этот общий раздел
        CommonHelper::startTimer('getProductsByTree');
        Console::stdout("Раскладываю товары по разделам" . PHP_EOL);
        $productsByTree = [];
        foreach ($productsTreeDataQuery->each() as $productTreeData) {
            $elementId = $productTreeData['element_id'];
            $treeId = $productTreeData['tree_id'];

            if (!isset($productsByTree[$treeId])){
                $productsByTree[$treeId] = [];
            }

            $productsByTree[$treeId][] = $elementId;
        }
        Console::stdout("Готово. Разделов с товарами: " . count($productsByTree) . PHP_EOL);

        if ($productsByTree){
            CommonHelper::startTimer('setTreeId');
            $i=0;
            foreach ($productsByTree AS $treeId => $products) {
                $i++;
                $productsNum = count($products);

                Console::stdout("[{$i}] treeId={$treeId}, set for productsNum={$productsNum}" . PHP_EOL);
//                if ($productsNum > 100000){
//                    Console::stdout("Слишком много товаров. Пропускаю." . PHP_EOL);
//                    continue;
//                }

                //Для некоторых разделов получается очень много товаров, что бы не иметь проблем бьем на части при обновлении
                $productsChunk = array_chunk($products, $productsPerQuery);

                foreach ($productsChunk as $productsChunkBlock) {
                    \common\models\Product::updateAll(['tree_id' => $treeId], ['content_id' => [2,5,10], 'id' => $productsChunkBlock]);
                }

            }
            Console::stdout("Done. " . CommonHelper::getTimerTime('setTreeId') . PHP_EOL);
        }

        return true;
    }

    public function actionRequeue($limit = 1000, $offset = 0, $onlyLot = 1)
    {
        $logQuery = \common\models\QueueLog::find()->select(['id']);
        $logQuery->andWhere(['queue_name' => 'site.Product']);
        if ($onlyLot){
            Console::stdout("Search only lots!" . PHP_EOL);
            $logQuery->andWhere('message LIKE \'%"LOT"%\'');
        }

        if ($limit){
            $logQuery->limit($limit);
        }

        if ($offset){
            $logQuery->offset($offset);
        }

        $logIds = $logQuery->column();

        Console::stdout("UpdateQueueStatus for items num: " . count($logIds) . PHP_EOL);
        \common\helpers\Common::startTimer('requeue');

        $affected = QueueLog::updateAll(['status' => 'DP'], [
            'id' => $logIds
        ]);

        $time = \common\helpers\Common::getTimerTime('requeue');

        Console::stdout("RowsNumAffected: " . $affected . " [{$time}]" . PHP_EOL);

        return true;
    }

    //Обновляет типы цен в соответствии с номиналами и данными из аналитики
    public function actionSyncAnalyticsPriceTypes()
    {
        $lotContentId = ProductModel::LOT;
        $cardContentId = ProductModel::CARD;
        $offerContentId = ProductModel::MOD;
        $priceTypeSite1 = \common\models\ShopTypePrice::PRICE_TYPE_SITE1_ID;
        $priceTypeSite2 = \common\models\ShopTypePrice::PRICE_TYPE_SITE2_ID;

        $offersUpdateChunkSize = 5000;

        \common\helpers\Common::startTimer('sync');
        $this->stdout(">> Синхронизирую типы цен для лотов и карточек" . PHP_EOL);

        $this->stdout("> Выставляю типы цен на основе номиналов" . PHP_EOL);

        $this->stdout("Выставляю типы цены Сайта1" . PHP_EOL);

        //Обновляем на ЦенуСайта1 если тип цены еще не Сайта1
        $conditionToSite1 = "content_id IN ({$lotContentId}, {$cardContentId}, {$offerContentId}) AND new_price_active!={$priceTypeSite1} 
        AND new_price>1 AND new_price_old>1";

        $affectedSite1 = ProductModel::updateAll(['new_price_active' => $priceTypeSite1], $conditionToSite1);
        $this->stdout("Обновлено товаров: {$affectedSite1}" . PHP_EOL);

        $this->stdout("Выставляю типы цены Сайта2" . PHP_EOL);

        //Обновляем на ЦенуСайта1 если тип цены еще не Сайта2 и Цена меньше старой цены
        $conditionToSite2 = "content_id IN ({$lotContentId}, {$cardContentId}, {$offerContentId}) AND new_price_active!={$priceTypeSite2}
        AND new_price>1 AND new_price_old>1 AND new_price<new_price_old";

        $affectedSite2 = ProductModel::updateAll(['new_price_active' => $priceTypeSite2], $conditionToSite2);
        $this->stdout("Обновлено товаров: {$affectedSite2}" . PHP_EOL);

        $this->stdout("> Выставляю типы цен на основе аналитики" . PHP_EOL);

        $productPriceTypes = BUFEcommPriceType::find()->all();

        if ($productPriceTypes){
            $this->stdout("Найдено элементов: " . count($productPriceTypes) . PHP_EOL);
            $this->stdout("Раскладываю товары по типам цен" . PHP_EOL);
            $productsByPriceType = [];

            $productsCodes = \common\helpers\ArrayHelper::getColumn($productPriceTypes, 'LotCode');

            $productsQuery = ProductModel::find()
                ->select(['id', 'code'])
                ->onlyLot()
                ->andWhere(['code' => $productsCodes])
                ->indexBy('code')
                ->asArray();

            $products = $productsQuery->all();

            foreach ($productPriceTypes as $productPriceType) {
                $priceTypeId = Price::getPriceTypeIdByKfssId($productPriceType->PRICE_TYPE_ID);
                if ($priceTypeId){
                    if (!isset($productsByPriceType[$priceTypeId])){
                        $productsByPriceType[$priceTypeId] = [];
                    }
                    $productId = !empty($products[$productPriceType->LotCode]) ? $products[$productPriceType->LotCode]['id'] : false;

                    if ($productId){
                        $productsByPriceType[$priceTypeId][] = $productId;
                    }
                }
            }

            if ($productsByPriceType){
                ksort($productsByPriceType);
                $this->stdout("Обновляю типы цен для товаров..." . PHP_EOL);
                foreach ($productsByPriceType as $priceTypeId => $productsIds) {
                    $affectedLots = \common\models\Product::updateAll(['new_price_active' => $priceTypeId], ['id' => $productsIds]);
                    $affectedCards = \common\models\Product::updateAll(['new_price_active' => $priceTypeId], ['parent_content_element_id' => $productsIds]);

                    $this->stdout("Тип цены [{$priceTypeId}] " . \common\models\ShopTypePrice::$priceTypes[$priceTypeId]['name']
                        . " установлен для элементов (лоты/карты): {$affectedLots}/{$affectedCards}" . PHP_EOL);

                    //* Обновление модификаций *//
                    //Типы цен в модифкациях тоже приходится обновлять так как JS в карточке например при выборе конкретной модифкации берет цип цены из нее
                    $cardsIds = ProductModel::find()->select(['id'])->onlyCard()->byParent($productsIds)->column();

                    if ($cardsIds){
                        $this->stdout("Обновляю модификации..." . PHP_EOL);

                        //Что бы подстраховаться от возможных проблем со слишком большим кол-вом элементов в IN обновим блочно
                        $cardsIdsChanks = array_chunk($cardsIds, $offersUpdateChunkSize);

                        $this->stdout("Блоков для обновления: " . count($cardsIdsChanks) . PHP_EOL);

                        $i=0;
                        foreach ($cardsIdsChanks as $cardsIdsChank) {
                            $i++;
                            $affectedOffers = \common\models\Product::updateAll(['new_price_active' => $priceTypeId], ['parent_content_element_id' => $cardsIdsChank]);
                            $this->stdout("Обновлено модификаций: {$affectedOffers}" . PHP_EOL);
                        }
                    }

                    //* /Обновление модификаций *//
                }
            }
        }

        $this->stdout("Done. " . \common\helpers\Common::getTimerTime('sync') . PHP_EOL);

        return true;
    }
}