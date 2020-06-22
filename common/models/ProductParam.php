<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-27
 * Time: 16:35
 */

namespace common\models;


class ProductParam extends \common\models\generated\models\ProductParam
{
    public static function getParamModificationByCode(int $modificationId, array $codes)
    {
        return ProductParam::find()
            ->leftJoin(ProductParamProduct::tableName(),
                ProductParamProduct::tableName() . '.product_param_id = ' . ProductParam::tableName() . '.id')
            ->leftJoin(ProductParamType::tableName(),
                ProductParam::tableName() . '.type_id = ' . ProductParamType::tableName() . '.id')
            ->andWhere([ProductParamProduct::tableName() . '.product_id' => $modificationId])
            ->andWhere([ProductParamType::tableName() . '.code' => $codes])
            ->one();
    }


    public static function getProductsFiltersQuery()
    {
        $query = self::find()
            ->select([
                ProductParamType::tableName() . '.code',
                self::tableName() . '.id',
                self::tableName() . '.name',
                self::tableName() . '.type_id',
                ProductParamType::tableName() . '.name as type_name',
//                ProductParamType::tableName().'.sort'
            ]);
        $query->leftJoin(\common\models\ProductParamProduct::tableName(), 'product_param_product.product_param_id = product_param.id');
        $query->leftJoin(\common\models\ProductParamType::tableName(), 'product_param.type_id = product_param_type.id');
        $query->leftJoin(Product::tableName() . ' AS mod', 'mod.id = product_param_product.product_id');
        $query->leftJoin(Product::tableName() . ' AS card', 'product_param_product.card_id = card.id');
        $query->leftJoin(Product::tableName() . ' AS lot', 'product_param_product.lot_id = lot.id');
//        $query->leftJoin('cms_content_element_property' . ' AS not_public_value',
//            "not_public_value.element_id = lot.id AND not_public_value.property_id = 83");

        if (\Yii::$app->appComponent->isSiteSS()) {
//            $query->andWhere("not_public_value.value IS NULL OR not_public_value.value = ''");
            $query->andWhere(['OR', ['lot.new_not_public' => null], ['!=', 'lot.new_not_public', 1]]);
        }
        //Не показываем товары без фото
//        $query->andWhere(['!=', 'card.hide_from_catalog_image', 1]); //уже не актуален
        $query->andWhere(['>', 'card.image_id', 0]);
        $query->andWhere(['mod.active' => 'Y']);
        $query->andWhere(['lot.active' => 'Y']);
        $query->andWhere(['card.active' => 'Y']);
        $query->andWhere(['>', 'card.new_quantity', 0]);
        $query->andWhere(['>', 'card.new_price', 2]);
        $query->andWhere(['>', 'mod.new_quantity', 0]);

        //todo неиспользуемое поле
//        $query->andWhere([ProductParamType::tableName() . '.active' => 1]);

        $query->groupBy('product_param_product.product_param_id');
//        $query->addOrderBy([ProductParamType::tableName().'.sort' => SORT_DESC]);
        $query->addOrderBy([self::tableName() . '.type_id' => SORT_ASC]);
        $query->addOrderBy([self::tableName() . '.name' => SORT_ASC]);

        return $query;

    }

    public function getModificationsCanSaleCount()
    {
        return $this->getProducts()
            ->canSale()
            ->count();
    }


    public static function createFromCmsContentElementProperty($cmsContentElement, ProductParamType $productParamType)
    {
        $name = $cmsContentElement->name;
        $model = self::find()
            ->andWhere(['name' => $name])
            ->andWhere(['type_id' => $productParamType->id])
            ->one();
        if (empty($model)) {

            $model = new self();
            $model->name = $name;
            $model->type_id = $productParamType->id;
            if (!$model->save()) {
                echo 'Error save productParam value ' . $name . ', type_id ' . $cmsContentElement->id . PHP_EOL;
                echo print_r($model->errors, true) . PHP_EOL;
//                throw new Exception('Error save productParam value ' . $name . ', type_id ' . $cmsContentElementProperty->id);
            }
        }

        return $model;
    }
}