<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use yii\data\ActiveDataProvider;

/**
 * ConfigurationSearch represents the model behind the search form about `Configuration`.
 */
class ConfigurationSearch extends Configuration
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'shop_discount_id', 'shop_discount_entity_id'], 'integer'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Configuration::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'shop_discount_id' => $this->shop_discount_id,
            'shop_discount_entity_id' => $this->shop_discount_entity_id,
        ]);

        return $dataProvider;
    }
}
