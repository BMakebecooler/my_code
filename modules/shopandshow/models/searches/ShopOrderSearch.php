<?php


namespace modules\shopandshow\models\searches;

use common\helpers\ArrayHelper;
use modules\shopandshow\models\shop\ShopOrder;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class ShopOrderSearch extends ShopOrder
{
    public $createdAtFrom;
    public $createdAtTo;

    public $priceFrom;
    public $priceTo;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['createdAtFrom', 'createdAtTo'], 'integer'],
            [['priceFrom', 'priceTo'], 'string'],
            [['source', 'source_detail'], 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'createdAtFrom' => 'Дата С',
            'createdAtTo'   => 'Дата ПО',
            'priceFrom'     => 'Сумма ОТ',
            'priceTo'       => 'Сумма ДО',
            'source'        => 'Источник',
            'source_detail' => 'Источник детально',
        ]);
    }

    public function search($params)
    {
        if (empty($params)){
            $params = Yii::$app->request->get();
        }

        $activeDataProvider = new ActiveDataProvider([
            'query' => static::find()
        ]);

        if (!($this->load($params)))
        {
            return $activeDataProvider;
        }

        $query = $activeDataProvider->query;

        //Standart
        if ($columns = $this->getTableSchema()->columns)
        {
            /**
             * @var \yii\db\ColumnSchema $column
             */
            foreach ($columns as $column)
            {
                if ($column->phpType == "integer")
                {
                    $query->andFilterWhere([$this->tableName() . '.' . $column->name => $this->{$column->name}]);
                } else if ($column->phpType == "string")
                {
                    $query->andFilterWhere(['like', $this->tableName() . '.' . $column->name, $this->{$column->name}]);
                }
            }
        }

        if ($this->createdAtFrom)
        {
            $query->andFilterWhere([
                '>=', $this->tableName() . '.created_at', strtotime($this->createdAtFrom)
            ]);
        }

        if ($this->createdAtTo)
        {
            $query->andFilterWhere([
                '<=', $this->tableName() . '.created_at', strtotime($this->createdAtTo)
            ]);
        }

        if ($this->priceFrom)
        {
            $query->andFilterWhere([
                '>=', $this->tableName() . '.price', $this->priceFrom
            ]);
        }

        if ($this->priceTo)
        {
            $query->andFilterWhere([
                '<=', $this->tableName() . '.price', $this->priceTo
            ]);
        }

        return $activeDataProvider;
    }

    public function getNonEmptySources(ActiveDataProvider $dataProvider){
        $query = clone $dataProvider->query;

        return $query->select(['count(*) AS num', "IF(ISNULL(source), '".ShopOrder::SOURCE_UNKNOWN."', source) AS source"])
            ->groupBy('source')
            ->asArray()
            ->indexBy('source')
            ->all();
    }

    public function getNonEmptySourcesDetail(ActiveDataProvider $dataProvider){
        /** @var ActiveQuery $query */
        $query = clone $dataProvider->query;

        return $query->select(['count(*) AS num', "IF(ISNULL(source_detail), '".ShopOrder::SOURCE_DETAIL_UNKNOWN."', source_detail) AS source_detail"])
            ->groupBy('source_detail')
            ->asArray()
            ->indexBy('source_detail')
            ->all();
    }

    public function getTotalPrice(ActiveDataProvider $dataProvider){
        /** @var ActiveQuery $query */

        $query = clone $dataProvider->query;
        $price = $query->select('SUM(price) AS price')->column();

        return !empty($price[0]) ? $price[0] : 0;
    }
}