<?php

namespace common\models\search;

use common\helpers\Promo;
use common\lists\Contents;
use common\lists\TreeList;
use common\models\user\User;
use common\widgets\products\ModificationsWidget;
use modules\api\resource\Product;
use modules\api\resource\Variation;
use skeeks\cms\models\CmsComponentSettings;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\query\CmsActiveQuery;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 26/12/2018
 * Time: 12:12
 */
class ProductSearch extends Model
{

    public $category;
    public $product_id;

    public function rules()
    {
        return [
            [['product_id'], 'safe'],
            [['category'], 'integer'],
        ];
    }

    /**
     * @param array $params
     * @return array|ActiveDataProvider
     */
    public function search($params = [])
    {
        if ($params && !($this->load($params, '') && $this->validate())) {
            return [];
        }

        $query = $this->baseQuery();
        $this->defaultSort($query);

        if ($this->category) {
            $descendantsIds = TreeList::getDescendantsById((int)$this->category);
            $query->andWhere(['cms_content_element.tree_id' => $descendantsIds]);
        }

        if ($this->product_id) {
            $query->andWhere(['cms_content_element.id' => $this->product_id]);
        }

        return $this->getActiveDataProvider($query, $params);
    }

    /**
     * Получить популярные товары
     * @param array $params
     * @return ActiveDataProvider
     */
    public function popular($params = [])
    {
        $query = $this->baseQuery();
        $query->addOrderBy(['shop_product_statistic.k_rating' => SORT_DESC]);

        return $this->getActiveDataProvider($query, $params);
    }

    /**
     * Получить рекомендованные товары
     * @param array $params
     * @return ActiveDataProvider
     */
    public function recommended($params = [])
    {
        $query = $this->baseQuery();
        $query->addOrderBy(['shop_product_statistic.k_rnd' => SORT_DESC]);

        return $this->getActiveDataProvider($query, $params);
    }


    /**
     * Недавно в эфире
     * @param array $params
     * @return array|ActiveDataProvider
     */
    public function recentlyInOnair($params = [])
    {
        $time = time();

        $query = $this->baseQuery();
        $query->innerJoin('ss_mediaplan_air_day_product_time  AS pt',
            'pt.begin_datetime >= :begin_datetime AND pt.begin_datetime <= :end_datetime AND cms_content_element.id = pt.lot_id', [
                ':begin_datetime' => $time - 3600,
                ':end_datetime' => $time + 3600,
            ]);

        $query->addOrderBy(['pt.begin_datetime' => SORT_DESC]);

        return $this->getActiveDataProvider($query, $params);
    }

    /**
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public function variations($params)
    {
        if (!($this->load($params, '') && $this->validate())) {
            return [];
        }

        $modificationSetting = CmsComponentSettings::findOne(['namespace' => ModificationsWidget::NAMESPACE_NAME]);

        if (!$modificationSetting) {
            return [];
        }

        $propertyCodes = array_keys($modificationSetting->value['modificationPropertiesModel']);

        $propertyIds = Contents::getPropertiesIdsByCode($propertyCodes);

        $propertiesString = join(',', $propertyIds);

        $colorId = ModificationsWidget::KFSS_COLOR_ID;

        $sql = <<<SQL
SELECT modification.*, spp.price, spp.max_price, spp.type_price_id, card.image_id AS cart_image_id, card.id AS card_id,
  GROUP_CONCAT(CONCAT_WS(',', cce_p.id, card.properties_id) SEPARATOR ',') AS properties_id, sp.quantity AS quantity
FROM cms_content_element AS modification 
INNER JOIN shop_product AS sp ON sp.id = modification.id
LEFT JOIN ss_shop_product_prices AS spp ON spp.product_id = modification.id
INNER JOIN (
    SELECT cart.id, cart.image_id, cce_p.id AS properties_id
    FROM cms_content_element AS cart 
    INNER JOIN shop_product AS sp ON sp.id = cart.id
    INNER JOIN cms_content_element_property AS cce_p ON cce_p.element_id = cart.id AND cce_p.property_id IN($colorId,$propertiesString) AND cce_p.value <> ''
    LEFT JOIN ss_shop_product_prices AS spp ON spp.product_id = cart.id
    WHERE cart.active = 'Y' AND cart.`parent_content_element_id` = :id -- AND sp.quantity >=1 
) AS card ON card.id = modification.parent_content_element_id
LEFT JOIN cms_content_element_property AS cce_p ON cce_p.element_id = modification.id AND cce_p.property_id IN($propertiesString) AND cce_p.value <> ''
WHERE modification.active = 'Y' -- AND sp.quantity >=1
GROUP BY modification.id
SQL;

        return Variation::findBySql($sql, [
            ':id' => $this->product_id,
        ])->all();
    }

    /**
     * @return CmsActiveQuery
     */
    private function baseQuery()
    {
        $query = Product::find()
            ->select('distinct (cms_content_element.id),cms_content_element.*')
            ->innerJoin('shop_product_statistic', 'shop_product_statistic.id=cms_content_element.id')
//            ->innerJoin('shop_product', 'shop_product.id=cms_content_element.id')
            ->innerJoin('ss_shop_product_prices', 'ss_shop_product_prices.product_id=cms_content_element.id')
            ->innerJoin('cms_content_element_image', 'cms_content_element_image.content_element_id=cms_content_element.id')
            ->leftJoin('ss_mediaplan_air_day_product_time AS air_day_product_time', 'air_day_product_time.lot_id = cms_content_element.id')
            ->leftJoin(CmsContentElementProperty::tableName() . ' AS not_public_value',
                'not_public_value.element_id = cms_content_element.id AND not_public_value.property_id = 83')
            ->andWhere("not_public_value.value IS NULL OR not_public_value.value = ''")
            ->andWhere(['cms_content_element.active' => 'Y'])
            ->andWhere(['>=', 'new_quantity', 1])
            ->andWhere(['>', 'ss_shop_product_prices.min_price', 3])
            ->andWhere('ss_shop_product_prices.min_price is not null')
            ->andWhere('cms_content_element.image_id is not null')
            ->andWhere('cms_content_element.tree_id is not null');

        return $query;
    }

    private function defaultSort(ActiveQuery &$query)
    {
        $query->addOrderBy(new \yii\db\Expression('IF(unix_timestamp(now()) - air_day_product_time.begin_datetime between 0 and 3600, 0, 1) ASC'))
            // далее сортируем по дате выхода в эфир (т.е. все эфирные лоты поднимаем наверх)
            ->addOrderBy(['air_day_product_time.begin_datetime' => SORT_DESC]);

        // для 999 дополнительно сортируем по цене, 999 сверху
        if (Promo::is999()) {
            $query->addOrderBy(new \yii\db\Expression("if(ss_shop_product_prices.price=999,0,1) ASC"));
        }

        $query->addOrderBy(new \yii\db\Expression('shop_product_statistic.k_stock DESC'));
        $query->addOrderBy(new \yii\db\Expression('shop_product_statistic.k_1 DESC'));
    }

    /**
     * @param $query
     * @param $params
     * @return ActiveDataProvider
     */
    private function getActiveDataProvider($query, $params)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeParam' => 'per_page'
            ]
        ]);

        return $dataProvider;
    }
}