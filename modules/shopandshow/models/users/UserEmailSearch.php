<?php

namespace modules\shopandshow\models\users;

use yii\data\ActiveDataProvider;

class UserEmailSearch extends UserEmail
{
    public $dateFrom;
    public $dateTo;

    public function init()
    {
        parent::init();

        if (!$this->dateFrom){
            $this->dateFrom = date('Y-m-d', time() - DAYS_7);
        }

        if (!$this->dateTo){
            $this->dateTo = date('Y-m-d');
        }
    }

    public function rules()
    {
        return [
            [['source', 'source_detail', 'value', 'dateFrom', 'dateTo', 'is_valid_site'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'dateFrom' => 'Дата С',
            'dateTo' => 'Дата ПО',
            'is_valid_site' => 'Email валидный (сайт)',
        ]);
    }

    public function search($params){
        $this->load($params);

        $query = UserEmail::find()
            ->where('created_at>=:dateFrom')
            ->andWhere('created_at<=:dateTo')
            ->params([
                ':dateFrom' => strtotime($this->dateFrom),
                ':dateTo' => strtotime($this->dateTo . ' 23:59:59')
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => (int)\Yii::$app->request->get('per-page')?: 20
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ]
            ],
        ]);

        $query->andFilterWhere(['like', 'value', $this->value]);

        $query->andFilterWhere([
            'source'    => $this->source
        ]);

        $query->andFilterWhere([
            'source_detail'    => $this->source_detail
        ]);

        if ($this->is_valid_site == 'null'){
            $query->andWhere([
                'is_valid_site'    => null
            ]);
        }else{
            $query->andFilterWhere([
                'is_valid_site'    => $this->is_valid_site
            ]);
        }

        return $dataProvider;
    }

    public function getNonEmptySources(ActiveDataProvider $dataProvider){
        $query = clone $dataProvider->query;

        return $query->select(['count(*) AS num', 'source'])
            ->groupBy('source')
            ->asArray()
            ->indexBy('source')
            ->all();
    }

    public function getNonEmptySourcesDetail(ActiveDataProvider $dataProvider){
        $query = clone $dataProvider->query;

        return $query->select(['count(*) AS num', 'source_detail'])
            ->groupBy('source_detail')
            ->asArray()
            ->indexBy('source_detail')
            ->all();
    }
}