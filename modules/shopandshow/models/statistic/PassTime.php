<?php
namespace modules\shopandshow\models\statistic;
use common\helpers\ArrayHelper;
use common\models\user\User;
use yii\db\Expression;

/**
 * Class PassTime
 *
 * @property int $id
 * @property int $user_id
 * @property int $created_at
 * @property int $seconds
 *
 * @package modules\shopandshow\models\statistic
 */
class PassTime extends \yii\db\ActiveRecord
{

    public $dateFrom;
    public $dateTo;

    public $secondsPerBlock = 10;
    public $groupAllMoreThen = 200;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_pass_time';
    }

    public function init()
    {
        parent::init();

        if (!$this->dateFrom){
            $this->dateFrom = date('Y-m-d', time() - DAYS_14);
        }

        if (!$this->dateTo){
            $this->dateTo = date('Y-m-d');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'seconds', 'seconds_first_symbol', 'fuser_id'], 'number'],
            [['dateFrom', 'dateTo'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fuser_id' => 'fUser ID',
            'created_at' => 'Created At',
            'seconds' => 'Seconds',
            'seconds_first_symbol' => 'Время ввода первого символа пароля',
        ];
    }

    /**
     * Получение исходных данных по скоростям авторизации пользователей
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDataBySeconds(){
        return $this->find()
            ->select(['seconds', new Expression('COUNT(1) AS quantity')])
            ->andWhere(['>=', 'created_at', \common\helpers\Dates::beginOfDate(strtotime($this->dateFrom))])
            ->andWhere(['<=', 'created_at', \common\helpers\Dates::endOfDate(strtotime($this->dateTo))])
            ->andWhere(['not', ['seconds' => null]])
            ->groupBy('seconds')
            ->orderBy('seconds')
            ->asArray()
            ->indexBy('seconds')
            ->all();
    }

    /**
     * Получение исходных данных по скоростям ввода первого символа при авторизации пользователей
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDataBySecondsFirstSymbol(){
        return $this->find()
            ->select(['seconds_first_symbol AS seconds', new Expression('COUNT(1) AS quantity')])
            ->andWhere(['>=', 'created_at', \common\helpers\Dates::beginOfDate(strtotime($this->dateFrom))])
            ->andWhere(['<=', 'created_at', \common\helpers\Dates::endOfDate(strtotime($this->dateTo))])
            ->andWhere(['not', ['seconds_first_symbol' => null]])
            ->groupBy('seconds')
            ->orderBy('seconds')
            ->asArray()
            ->indexBy('seconds')
            ->all();
    }

    /**
     * Из исходных данных собирает серии для highcharts
     * @param array $data
     * @return array
     */
    public function getSeriesWithDrilldownForHighcharts(array $data){
        //Так как сырые данные на графике превращают его в дикую простыню (из-за очень большого разброса между мин и макс)
        //Разобьем на группы (категории) с возможностью посмотреть более детально

        //Разбиваем весь блок данных на группы, сначала все что больше $groupAllMoreThen
        //А то что меньше уже делим по secondsPerBlock
        //Попутно записывая детализированные серии

        $seriesData = [];
        $drilldownSeries = [];

        $dataNormal = array_filter($data, function ($row){
            return $row['seconds'] < $this->groupAllMoreThen;
        });

        $dataHigh = array_filter($data, function ($row){
            return $row['seconds'] >= $this->groupAllMoreThen;
        });

        //Массив для каждого тика (секунды)
        $dataNormalFullTime = array_fill(0, $this->groupAllMoreThen, []);

        //Заполняем тики значениями
        foreach ($dataNormal as $row) {
            $dataNormalFullTime[$row['seconds']] = $row;
        }

        //Разбиваем на блоки
        $normalSeriesDataBlocks = array_chunk($dataNormalFullTime, $this->secondsPerBlock, true);

        foreach ($normalSeriesDataBlocks as $seriesDataBlock) {
            $blockQuantity = \common\helpers\ArrayHelper::arraySumColumn($seriesDataBlock, 'quantity');

            //Вычисляем мин и макс время блока
            $seconds = array_keys($seriesDataBlock);
            sort($seconds);

            $minSeconds = $seconds[0];
            $maxSeconds = $seconds[ count($seconds)-1 ];

            $serieId = "{$minSeconds}-{$maxSeconds}";

            $seriesData[] = [
                'name' => $serieId,
                'y' => (int)$blockQuantity,
                'drilldown' => $serieId
            ];

            $drilldownData = [];

            foreach ($seriesDataBlock as $tickSecond => $tick) {
                $drilldownData[] = [(string)$tickSecond, (int)($tick['quantity'] ?? 0)];
            }

            $drilldownSeries[] = [
                'id'    => $serieId,
                'data'  => $drilldownData
            ];
        }

        //* Инфа о сверхдолгих авторизациях *//

        $blockQuantity = ArrayHelper::arraySumColumn($dataHigh, 'quantity');

        $serieId = "{$this->groupAllMoreThen}+";

        $seriesData[] = [
            'name' => $serieId,
            'y' => (int)$blockQuantity,
            //'drilldown' => $serieId //Детали этого блока вроде как не интересны ибо не показательны
        ];

        //* /Инфа о сверхдолгих авторизациях *//

        $series = [
                [
                    'name'  => 'Авторизации',
                    'colorByPoint' => true,
                    'data'  => $seriesData,
                ]
        ];

        return ['series' => $series, 'drilldownSeries' => $drilldownSeries];
    }

    /**
     * Получение массива опций для highcharts
     * @param $series
     * @param $drilldownSeries
     * @return array
     */
    public function getHighchartsConfig($series, $drilldownSeries, $options = []){
        $optionsDefault = [
            'options' => [
                'chart' => [
                    'type' => 'column',
                ],
                'title' => ['text' => 'Количество авторизаций за время'],
                'xAxis' => [
                    'title' => ['text' => 'Время (интервал), сек.'],
                    'type' => 'category',
                ],
                'yAxis' => [
                    [
                        'title' => ['text' => 'Попыток авторизации, шт.'],
                    ]
                ],
                'plotOptions' => [
                    'series' => [
                        'dataLabels' => [
                            'enabled' => true,
                        ],
                    ],
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => $series,
                'drilldown' => [
                    'series' => $drilldownSeries
                ]
            ]
        ];

        return array_replace_recursive($optionsDefault, $options);
    }
}
