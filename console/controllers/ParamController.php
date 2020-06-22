<?php

/**
 * php yii param/season-and-brand-props
 * php yii param/last-product-props
 * php yii param/new-props-catalog-color
 * php yii param/set-sizes-count-can-sale
 */

namespace console\controllers;

use common\helpers\Filter;
use common\helpers\SizeProfile;
use common\helpers\Strings;
use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\Guid;
use common\models\ProductParam;
use common\models\Product;
use common\models\ProductParamType;
use yii\console\Controller;
use common\models\CmsContentElementProperty;

class ParamController extends Controller
{
    /**
     * метод подсчитать количество доступных модификаций для параметров
     *
     */
    public function actionSetSizesCountCanSale()
    {
        foreach (SizeProfile::$sizeCodes as $code) {
            $data = ProductParam::find()
                ->leftJoin(ProductParamType::tableName(), ProductParamType::tableName() . '.id=' . ProductParam::tableName() . '.type_id')
                ->andWhere(['code' => $code])
                ->orderBy('name')
                ->all();

            foreach ($data as $model) {

                $count = $model->getModificationsCanSaleCount();
                $this->stdout('обновляем code ' . $code . ' Id: ' . $model->id . ' Название ' . $model->name . ' qty ' . $count . PHP_EOL);

                $model->count_can_sale = $count;
                $model->save();
            }
        }
    }

    /**
     * метод подтянуть из старых таблиц в новые цвета товаров
     *
     */
    public function actionNewPropsCatalogColor($days = null, $color_id = null)
    {
        if (!$days) {
            $days = 20;
        }
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

                if ($color_id && $id != $color_id) {
                    continue;
                }

                $this->stdout('Добавление фильтрового цвета ' . $color . ' ' . PHP_EOL);
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
                if ($days) {
                    $date = new \DateTime(date('Y-m-d H:i:s'));
                    $date->modify('-' . $days . ' days');

                    $products = Product::find()
                        ->onlyCard()
                        ->leftJoin(CmsContentElementProperty::tableName(), CmsContentElementProperty::tableName() . '.element_id = cms_content_element.id')
                        ->andWhere(['>', Product::tableName() . '.created_at', $date->format('U')])
                        ->andWhere("value IN(" . implode(',', $ralatedColors) . ")");

                } else {
                    $products = Product::find()
                        ->onlyCard()
                        ->leftJoin(CmsContentElementProperty::tableName(), CmsContentElementProperty::tableName() . '.element_id = cms_content_element.id')
                        ->andWhere("value IN(" . implode(',', $ralatedColors) . ")");
                }
                foreach ($products->each() as $product) {

                    $modifications = Product::find()
                        ->onlyModification()
                        ->andWhere(['parent_content_element_id' => $product->id]);
                    $k = 0;
                    foreach ($modifications->each() as $modification) {
                        if ($k == 0) {
                            $this->stdout('Insert Lot ' . $product->parent_content_element_id . '  Card ' . $product->id . ', Modification ' . $modification->id . ' Color ' . $param->id, PHP_EOL);
                            Filter::addProductParam(
                                $modification->id,
                                $product->id,
                                $product->parent_content_element_id,
                                $param->id
                            );
                        }
                        $k++;
                    }
                }
            }
        }
    }

    /**
     * Универсальный метод подтянуть из старых таблиц в новые параметры товаров
     *
     */
    public function actionLastProductProps($days = 20)
    {

        $relations = [
            'KFSS_ETALON___ODEJDA' => [
                'ids' => [
                    234,
                    235,
//                    243,
                    245,
                    254,
                    256,
//                    262,
                    265,
                    266
                ],
                'type' => 'size',
                'name' => 'размер одежды'
            ],
            'KFSS_RAZMER_OBUVI' => [
                'ids' => [246, 236, 261, 267],
                'type' => 'size',
                'name' => 'Размер обуви'
            ],
//            'KFSS_ETALON___NOSKI' => [
//                'ids' => [],
//                'type' => 'size',
//            ],
//            'KFSS_ETALON___SHAPKI' => [
//                'ids' => [],
//                'type' => 'size',
//            ],
            'KFSS_RAZMER_KOLTSA' => [
                'ids' => [253, 244],
                'type' => 'size',
                'name' => 'Размер кольца'
            ],
//            'KFSS_RAZMER_BYUSTGALTERA' => [
//                'ids' => [247,257,291],
//                'type' => 'size',
//                'name' => 'Размер бюстгалтера'
//            ],
//            'KFSS_RASCHMER_KOROBA' => [
//                'ids' => [288],
//                'type' => 'size',
//                'name' => 'Размер короба'
//            ],
//            'KFSS_RAZMER_PODUSHKI' => [
//                'ids' => [263,238],
//                'type' => 'size',
//                'name' => 'Размер подушки'
//            ],
//            'KFSS_RAZMER_POSTELNOGO_BELYA' => [
//                'ids' => [237,252],
//                'type' => 'size',
//                'name' => 'Размер постельного белья'
//            ],
//
//            'KFSS_RAZMER_TEKSTILYA' => [
//                'ids' => [259,242],
//                'type' => 'size',
//                'name' => 'Размер текстиля'
//            ],
//
//            'KFSS_SEASON' => [
//                'ids' => [230],
//                'name' => 'Сезон',
//                'type' => 'simple',
//            ],
//            'KFSS_BRAND' => [
//                'ids' => [218],
//                'name' => 'Бренд',
//                'type' => 'simple',
//            ],
        ];

        $date = new \DateTime(date('Y-m-d H:i:s'));
        $date->modify('-' . $days . ' days');

        $mods = Product::find()
            ->select(['id', 'parent_content_element_id'])
            ->onlyModification()
            ->andWhere(['>', 'created_at', $date->format('U')])
            ->orderBy('created_at DESC')
            ->asArray()
            ->all();

        foreach ($mods as $mod) {
            $card = Product::find()
                ->select(['id', 'parent_content_element_id'])
                ->where(['id' => $mod['parent_content_element_id']])
                ->asArray()
                ->one();
            if ($card) {

                $props = CmsContentElementProperty::find()
                    ->select(['value', 'cce.name', 'property_id'])
                    ->leftJoin('cms_content_element as cce', 'cce.id = ' . CmsContentElementProperty::tableName() . '.value')
                    ->andWhere(['=', 'element_id', $mod['id']])
                    ->asArray()
                    ->all();

                foreach ($props as $prop) {
                    foreach ($relations as $code => $relationData) {
                        if (in_array($prop['property_id'], $relationData['ids'])) {
                            $paramType = $paramType = ProductParamType::find()->where(['code' => $code])->one();
                            if (!$paramType) {
                                $paramType = new ProductParamType();
                                $paramType->name = isset($relationData['name']) ? $relationData['name'] : $code;
                                $paramType->code = $code;
                                $paramType->guid = $code;
                                $paramType->save();
                            }
                            if (isset($prop['name']) && !empty($prop['name'])) {

                                if ($relationData['type'] == 'size') {
                                    $prop['name'] = Strings::clearParamName($prop['name']);
                                }

                                $param = ProductParam::find()
                                    ->andWhere(['type_id' => $paramType->id])
                                    ->andWhere(['name' => $prop['name']])
                                    ->one();
                                if (!$param) {
                                    $param = new ProductParam();
                                    $param->type_id = $paramType->id;
                                    $param->name = $prop['name'];
                                    $param->save();
                                }
                                if ($relationData['type'] == 'size') {
                                    $this->stdout('insert ModId ' . $mod['id'] . ', CardId ' . $card['id'] . ', LotId ' . $card['parent_content_element_id'] . ', ParamId ' . $param->id, PHP_EOL);
                                    Filter::addProductParam(
                                        $mod['id'],
                                        $card['id'],
                                        $card['parent_content_element_id'],
                                        $param->id
                                    );
                                } elseif ($relationData['type'] == 'color') {

                                } else {
                                    $this->stdout('insert ModId ' . $mod['id'] . ', CardId ' . $card['id'] . ', LotId ' . $card['parent_content_element_id'] . ', ParamId ' . $param->id, PHP_EOL);
                                    Filter::addProductParamModCard(
                                        $mod['id'],
                                        $card['id'],
                                        $card['parent_content_element_id'],
                                        $param->id
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * подтянуть из таблицы товара в таблицы параметров фильтра бренд и сезон
     * @param int $days
     *
     */

    public function actionSeasonAndBrandProps($days = 20)
    {

        $date = new \DateTime(date('Y-m-d H:i:s'));
        $date->modify('-' . $days . ' days');

        $paramsOldToNew = [];

        $props = [
            'KFSS_SEASON' => [
                'param_type_id' => 7,
                'content_id' => 194,
                'name' => 'Сезон',
                'product_property_field' => 'new_season_id'
            ],
            'KFSS_BRAND' => [
                'param_type_id' => 8,
                'content_id' => 193,
                'name' => 'Бренд',
                'product_property_field' => 'new_brand_id'
            ],
        ];

        foreach ($props as $code => $prop) {

            $lotsQuery = Product::find()
                ->select($prop['product_property_field'])
                ->onlyLot()
                ->andWhere(['>', 'created_at', $date->format('U')])
                ->orderBy('created_at DESC');

            //получаем старый массив параметров из скексовсой таблицы
            $propertiesOld = CmsContentElement::find()
                ->select(['id', 'name'])
                ->andWhere(['content_id' => $prop['content_id']])
                ->andWhere(['in', 'id', $lotsQuery])
                ->asArray()
                ->all();

            //мапим старые параметры к параметрам из новых таблиц
            foreach ($propertiesOld as $propOld) {
                $param = ProductParam::find()
                    ->andWhere(['type_id' => $prop['param_type_id']])
                    ->andWhere(['name' => $propOld['name']])
                    ->one();

                if (!$param) {
                    $param = new ProductParam();
                    $param->type_id = $prop['param_type_id'];
                    $param->name = $propOld['name'];
                    $param->save();
                }

                if ($param) {
                    $paramsOldToNew[$propOld['id']] = $param->id;
                }
            }
        }

        $lots = Product::find()
            ->onlyLot()
            ->andWhere(['>', 'created_at', $date->format('U')])
            ->orderBy('created_at DESC');
//            ->andWhere(['like', 'new_lot_num', '%009-%', false]);

        $countInsertParams = 0;
        $countIgnoreParams = 0;

        foreach ($lots->each() as $lot) {

            $cards = $lot->getCards();

            foreach ($props as $propData) {
                $productPropertyField = $propData['product_property_field'];
                if ($lot->$productPropertyField) {

//                    $paramId = $paramsOldToNew[$lot->$productPropertyField] ?? null;
//                    if($paramId){

                    if (isset($paramsOldToNew[$lot->$productPropertyField])) {

                        $paramId = $paramsOldToNew[$lot->$productPropertyField];

                        foreach ($cards->each() as $card) {
                            $modification = Product::find()
                                ->onlyModification()
                                ->andWhere(['parent_content_element_id' => $card->id])
                                ->addOrderBy(['id' => SORT_DESC])
                                ->one();

                            if ($modification) {

                                $flag = Filter::addProductParam(
                                    $modification->id,
                                    $card->id,
                                    $card->parent_content_element_id,
                                    $paramId
                                );
                                if ($flag) {
                                    $countInsertParams++;
                                } else {
                                    $countIgnoreParams++;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->stdout('insert params ' . $countInsertParams, PHP_EOL);
        $this->stdout('ignore params ' . $countIgnoreParams, PHP_EOL);
    }

}