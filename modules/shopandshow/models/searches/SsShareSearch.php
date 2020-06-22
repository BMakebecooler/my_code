<?php
namespace modules\shopandshow\models\searches;

use modules\shopandshow\models\shares\SsShare;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class SsShareSearch extends SsShare
{
    public $period_from;
    public $period_to;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            ['period_from', 'integer'],
            ['period_to', 'integer'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'period_from' => 'Дата начала',
            'period_to' => 'Дата окончания',
        ]);
    }

    public function search($params)
    {
        $tableName = $this->tableName();

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

        if ($this->period_from)
        {
            $query->andFilterWhere([
                '>=', $this->tableName() . '.end_datetime', $this->period_from
            ]);
        }

        if ($this->period_to)
        {
            $query->andFilterWhere([
                '<=', $this->tableName() . '.begin_datetime', $this->period_to
            ]);
        }

        return $activeDataProvider;
    }


    /**
     * Returns the list of attribute names.
     * By default, this method returns all public non-static properties of the class.
     * You may override this method to change the default behavior.
     * @return array list of attribute names.
     */
    public function attributes()
    {
        $class = new \ReflectionClass($this);

        $names = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $names[] = $property->getName();
            }
        }

        return ArrayHelper::merge(parent::attributes(), $names);
    }
}
