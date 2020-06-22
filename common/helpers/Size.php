<?php
/**
 * Хэлпер по размерам
 */

namespace common\helpers;

use common\components\cache\PageCache;
use common\models\CmsContentProperty;
use common\models\generated\models\ProductParam;
use common\models\generated\models\ProductParamProduct;
use common\models\ProductParamType;
use common\models\Product;


class Size
{
    public static $kfssSizeCodesMain = [
        'KFSS_ETALON___ODEJDA',
        'KFSS_RAZMER_OBUVI',
        'KFSS_RAZMER_KOLTSA'
    ];

    public static $dataTableSizes = [
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

    public static $etalons = [
        'etalon_clothing_size' => 'KFSS_ETALON___ODEJDA',
        'etalon_shoe_size' => 'KFSS_RAZMER_OBUVI',
        'etalon_sock_size' => 'KFSS_ETALON___NOSKI',
        'etalon_cap_size' => 'KFSS_ETALON___SHAPKI',
        'etalon_jewelry_size' => '',
        'etalon_textile_size' => '',
        'etalon_pillow_size' => '',
        'etalon_bed_linen_size' => '',
        'etalon_bra_size' => '',
    ];

    public static function getSizeDestScale($scaleId)
    {
        $sizesRelation = [
            234 => [
                'source' => [235,243,245,254,256,262,265,266],
                'name' => 'Размер одежды'
            ],
            246 => [
                'source' => [236,261,267],
                'name' => 'Размер обуви'
            ],
            247 => [
                'source' => [257],
                'name' => 'Размер бюстгальтера'
            ],
            237 => [
                'source' => [252],
                'name' => 'Размер постельного белья'
            ],
            238 => [
                'source' => [263],
                'name' => 'Размер подушки'
            ],
            242 => [
                'source' => [259],
                'name' => 'Размер текстиля'
            ],
            244 => [
                'source' => [253],
                'name' => 'Размер кольца'
            ],
            264 => [
                'source' => [],
                'name' => 'Размер трусов'
            ]
        ];
        foreach ($sizesRelation as $destId => $data){
            if($scaleId == $destId) {
                return [
                    'id' => $destId,
                    'name' => $sizesRelation[$destId]['name']
                ];
            }
            if(in_array($scaleId,$sizesRelation[$destId]['source'])) {
                return [
                    'id' => $destId,
                    'name' => $sizesRelation[$destId]['name']
                ];
            }
        }
        return $scaleId;
    }

    public static function getSizeProperties()
    {
        $return =  \Yii::$app->cache->get('size_properties');
        if ($return === false) {
            $return = [];
            $data = CmsContentProperty::find()->where(['like', 'code', '%RAZMER%', false])->all();
            foreach ($data as $part) {
                $return[] = $part->toArray();
            }
            \Yii::$app->cache->set('size_properties',$return,PageCache::CACHE_DURATION);
        }
        return $return;
    }

    public static function getSizesByCode(string $code)
    {
        $return =  \Yii::$app->cache->get('size-by-code-'.$code);
        if ($return === false) {
            $return = [];
            $sql = "select
               ccep.element_id,
               ccep.value,
               cce.name AS name_mod,
               ccp.id,
               cce_size.name AS name_size
                from
                cms_content_property ccp
                left join cms_content_element_property ccep on ccp.id = ccep.property_id
                left join cms_content_element cce on ccep.element_id = cce.id
                left join cms_content_element cce_size on ccep.value = cce_size.id
                where ccp.code = :code order by name_size";

            $rows = \Yii::$app->db->createCommand($sql, [
                'code' => $code
            ])->queryAll();

            foreach ($rows as $row) {
                $return[$row['value']] = $row['name_size'];
            }

            \Yii::$app->cache->set('size-by-code-'.$code, $return,PageCache::CACHE_DURATION);

        }

        return $return;

    }

    public static function getSourceSizesByDest(int $elementId)
    {
        $return =  \Yii::$app->cache->get('source-size-by-dest-'.$elementId);
        if ($return === false) {
            $sql = 'select ccer.content_element_id ,cce.name
            from cms_content_element_relation  ccer 
            left join cms_content_element cce on ccer.content_element_id=cce.id
            where ccer.related_content_element_id=:elementId';

            $return = \Yii::$app->db->createCommand($sql, [
                ':elementId' => $elementId
            ])->queryall();

            \Yii::$app->cache->set('source-size-by-dest-'.$elementId, $return,PageCache::CACHE_DURATION);
        }

        return $return;
    }


    public static function getDestSizeBySource(int $elementId)
    {
        $return =  \Yii::$app->cache->get('dest-size-by-source-'.$elementId);
        if ($return === false) {
            $sql = 'select ccer.related_content_element_id ,cce.name,cc.name as etalon_name,cc.id as etalon_id
            from cms_content_element_relation ccer
            left join cms_content_element cce on ccer.related_content_element_id=cce.id
            left join cms_content cc on cce.content_id = cc.id
            where ccer.content_element_id=:elementId';

            $return = \Yii::$app->db->createCommand($sql, [
                ':elementId' => $elementId
            ])->queryOne();

            \Yii::$app->cache->set('dest-size-by-source-'.$elementId, $return,PageCache::CACHE_DURATION);

        }

        return $return ;
    }

    public static function getRelatedScale(int $elementId)
    {
        $sql = 'select ccer.content_element_id ,ccep.value
        from cms_content_element_relation  ccer 
        left join cms_content_element_property ccep on ccer.content_element_id=ccep.element_id
        where ccer.related_content_element_id=:elementId';

        $rows = \Yii::$app->db->createCommand($sql,[
            ':elementId' => $elementId
        ])->queryAll();

        return $rows;

    }

    public static function addSizeRelation(int $sizeid, int $relationSizeId)
    {
        $sql = "INSERT IGNORE INTO cms_content_element_relation SET
                       content_element_id = :content_element_id,
                       related_content_element_id = :related_content_element_id";

        \Yii::$app->db->createCommand($sql, [
            ':content_element_id' => $sizeid,
            ':related_content_element_id' => $relationSizeId
        ])->query();
    }

    public static function getTypesSizeData()
    {
        $return = \Yii::$app->cache->get('sizes-types-data');
        if ($return === false) {
            $return = ProductParamType::find()
                ->where(['like', 'code', 'KFSS_ETALON_'])
                ->orWhere(['code' => 'KFSS_RAZMER_OBUVI'])
                ->orWhere(['code' => 'KFSS_RAZMER_KOLTSA'])
                ->asArray()
                ->all();
            \Yii::$app->cache->set('sizes-types-data', $return, PageCache::CACHE_DURATION);
        }
        return $return;
    }

    public static function getCardSizes($cardId,$use_cache = true)
    {
        if($use_cache) {
            $return = \Yii::$app->cache->get('sizes-by-card-' . $cardId);
        }else{
            $return = false;
        }

        if ($return === false) {

            $typesSizeData = self::getTypesSizeData();

            $typesSize = [];
            foreach ($typesSizeData as $part) {
                $typesSize[] = $part['id'];
            }

            $return = [];
            $mods = [];
            $modsQuery = Product::find()
                ->select([
                    'id'
                ])
                ->onlyModification()
                ->canSale()
                ->andWhere(['parent_content_element_id' => $cardId]);

            foreach ($modsQuery->each() as $mod){
                $mods[] = $mod->id;
            }

//                $data = ProductParam::find()
//                    ->leftJoin(ProductParamProduct::tableName(), ProductParamProduct::tableName() . '.product_param_id = ' . ProductParam::tableName() . '.id')
//                    ->leftJoin(\common\models\Product::tableName() . ' as p', 'p.id = ' . ProductParamProduct::tableName() . '.card_id')
//                    ->andWhere(['card_id' => $cardId])
//                    ->andWhere(['in', 'type_id', $typesSize])
//                    ->andWhere(['>', 'p.new_quantity', 0])
//                    ->addOrderBy('name')
//                    ->asArray()
//                    ->all();

            if($mods){
                $data = ProductParam::find()
                    ->leftJoin(ProductParamProduct::tableName(), ProductParamProduct::tableName() . '.product_param_id = ' . ProductParam::tableName() . '.id')
                    ->andWhere(['in', 'type_id', $typesSize])
                    ->andWhere(['in', ProductParamProduct::tableName().'.product_id', $mods])
                    ->addOrderBy('name')
                    ->asArray()
                    ->all();

                if (count($data)) {
                    foreach ($data as $part) {
                        $return[] = $part['name'];
                    }
                }
            }

            \Yii::$app->cache->set('sizes-by-card-' . $cardId, $return, PageCache::CACHE_DURATION);

        }
        return $return;
    }


    public static function getLotSizes($lotId,$use_cache = true)
    {
        if($use_cache) {
            $return = \Yii::$app->cache->get('sizes-by-lot-' . $lotId);
        }else{
            $return = false;
        }

        if ($return === false) {

            $typesSizeData = self::getTypesSizeData();

            $typesSize = [];
            foreach ($typesSizeData as $part){
                $typesSize[] = $part['id'];
            }
            $return = [];
            if(count($typesSize)) {
                $data = ProductParam::find()
                    ->leftJoin(ProductParamProduct::tableName(), ProductParamProduct::tableName() . '.product_param_id = ' . ProductParam::tableName() . '.id')
//                    ->leftJoin(ShopProduct::tableName().' as sp','sp.id = '.ProductParamProduct::tableName().'.product_id')
                    ->leftJoin(\common\models\Product::tableName().' as p','p.id = '.ProductParamProduct::tableName().'.product_id')
                    ->andWhere(['lot_id' => $lotId])
                    ->andWhere(['in', 'type_id', $typesSize])
//                    ->andWhere(['>','sp.quantity',0])
                    ->andWhere(['>','p.new_quantity',0])
                    ->addOrderBy('name')
                    ->asArray()
                    ->all();

                if (count($data)) {
                    foreach ($data as $part) {
                        $return[] = $part['name'];
                    }
                }
            }
            \Yii::$app->cache->set('sizes-by-lot-'.$lotId, $return, PageCache::CACHE_DURATION);

        }
        return $return;
    }

    public static function getEtalonSizes($scale)
    {
        //ToDo добавить новые эталонные шкалы

        if(!$scale){
            return false;
        }

        $return = [];

        $code = self::$etalons[$scale];

        if(isset($code) && !empty($code)){
            $typeParam = ProductParamType::find()->where(['code' =>  $code])->one();
            if($typeParam) {
                $params = $typeParam->getProductParams()->orderBy('name')->all();
                if($params) {
                    foreach ($params as $param) {
                        $return[$param->id] = $param->name;
                    }
                }
            }
        }

        return $return;
    }

}