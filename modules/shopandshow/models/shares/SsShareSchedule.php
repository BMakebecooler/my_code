<?php

namespace modules\shopandshow\models\shares;
use common\widgets\onair\OnAir;
use modules\shopandshow\models\mediaplan\AirBlock;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "ss_share_schedule".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $begin_datetime
 * @property integer $end_datetime
 * @property string  $block_type
 * @property integer $tree_id
 * @property integer $block_position
 * @property string  $name
 * @property string  $description
 * @property string  $type
 *
 */
class SsShareSchedule extends \yii\db\ActiveRecord
{
    const TYPE_BANNER_GRID = 'G';
    const TYPE_MAIL_TEMPLATE = 'M';

    protected static $_type = self::TYPE_BANNER_GRID;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_shares_schedule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['created_at'], 'safe'],
            [['begin_datetime', 'end_datetime'], 'required'],
            [['begin_datetime', 'end_datetime', 'tree_id', 'block_position'], 'integer'],
            [['block_type'], 'string', 'max' => 256],
            [['type'], 'string', 'max' => 2],
            [['name', 'description'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'begin_datetime' => 'Начало активности',
            'end_datetime' => 'Конец активности',
            'block_type' => 'Тип блока',
            'tree_id' => 'Категория эфира',
            'block_position' => 'Порядок блока',
            'name' => 'Заголовок',
            'description' => 'Подзаголовок',
            'type' => 'Тип сетки'
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return \common\helpers\ArrayHelper::merge(parent::behaviors(), [
            \yii\behaviors\BlameableBehavior::className(),
            \yii\behaviors\TimestampBehavior::className(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->type = static::$_type;
    }

    /**
     * Список привязанных баннеров
     * @return ActiveQuery
     */
    public function getShares()
    {
        return $this->hasMany(SsShare::className(), ['share_schedule_id' => 'id'])->inverseOf('shareSchedule');
    }

    /**
     * @param int $searchDate
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findByDate($searchDate)
    {
        return self::find()
            ->andWhere(['<=', 'begin_datetime', $searchDate])
            ->andWhere(['>=', 'end_datetime', $searchDate])
            ->andWhere(['=', 'type', static::$_type])
            ->indexBy('id')
            ->orderBy(['block_position' => SORT_ASC]);
    }

    /**
     * @param int $searchDate
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findByDateAndEfir($searchDate)
    {
        $schedules = self::findByDate($searchDate);

        $scheduleList = AirBlock::getScheduleList();
        $activeScheduleList = array_filter($scheduleList, function($row) {return $row['active'];}, null);

        $activeTreeId = null;
        if ($activeScheduleList) {
            $activeTreeId = end($activeScheduleList)['tree_id'];

            $schedules->orderBy(["IF($activeTreeId = tree_id, 1, 0)" => SORT_DESC, 'block_position' => SORT_ASC]);
        }

        return $schedules;
    }

    /**
     * Получение блоков баннеров за период
     *
     * @param int $dateFrom
     * @param int $dateTo
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findByDatePeriod($dateFrom = null, $dateTo = null)
    {
        return self::find()
            ->andWhere(['=', 'type', static::$_type])
            ->andFilterWhere(['<=', 'begin_datetime', $dateTo])
            ->andFilterWhere(['>=', 'end_datetime', $dateFrom])
            ->orderBy(['begin_datetime' => SORT_ASC, 'block_position' => SORT_ASC]);
    }

    /**
     * Находит подходящие блоки для баннера
     * @param SsShare $share
     * @return array|SsShareType[]|\yii\db\ActiveRecord[]
     */
    public static function getAvailSchedulesForBanner(SsShare $share)
    {
        if (!preg_match('/^BANNER_(SLIDER|\d+)_*(\d*)$/', $share->banner_type, $match)) {
            return [];
        }

        $blockType = 'BLOCK'.$match[1];

        $bannerCount = self::getBannerCount($blockType);
        if (!$bannerCount) {
            return [];
        }

        // блок уже заполнен нужным числом баннеров
        $sharesCountQuery = SsShare::find()
            ->select(new \yii\db\Expression('1'))
            ->andWhere(['>=', 'end_datetime', time()])
            ->andWhere('share_schedule_id = ss_shares_schedule.id')
            ->groupBy('share_schedule_id')
            ->having(new \yii\db\Expression("COUNT(*) >= {$bannerCount}"));

        // блок уже содержит баннер такого типа
        $sharesTypeQuery = SsShare::find()
            ->select(new \yii\db\Expression('1'))
            ->andWhere(['>=', 'end_datetime', time()])
            ->andWhere(['banner_type' => $share->banner_type])
            ->andWhere('share_schedule_id = ss_shares_schedule.id');

        return
            self::findByDatePeriod(time())
                ->andWhere(['block_type' => $blockType])
                ->andWhere(['not exists', $sharesCountQuery])
                ->andWhere(['not exists', $sharesTypeQuery])
                ->orFilterWhere(['=', 'id', $share->share_schedule_id])
                ->all();
    }

    /**
     * отображаемое имя
     * @return string
     */
    public function getDisplayName()
    {
        return "{$this->name} [block_{$this->id}]";
    }

    /**
     * Создает запись по умолчанию на завтрашний день
     *
     * @return static
     */
    public static function createNew()
    {
        $ssShareSchedule = new static();
        $ssShareSchedule->begin_datetime = mktime(7, 0, 0, date('m'), date('d') + 1);
        $ssShareSchedule->end_datetime = mktime(7, 0, -1, date('m'), date('d') + 2);
        $ssShareSchedule->type = static::$_type;

        return $ssShareSchedule;
    }

    /**
     * Выводит список доступных блоков
     * @return array
     */
    public static function getBlockList()
    {
        return [
            'BLOCK1' => 'BLOCK1',
            'BLOCK2' => 'BLOCK2',
            'BLOCK3' => 'BLOCK3',
            'BLOCK4' => 'BLOCK4',
            'BLOCK5' => 'BLOCK5',
            'BLOCK6' => 'BLOCK6',
            'BLOCK7' => 'BLOCK7',
            'BLOCK8' => 'BLOCK8',
            'BLOCK9' => 'BLOCK9',
            'BLOCK10' => 'BLOCK10',
            'BLOCK11' => 'BLOCK11',
            'BLOCK12' => 'BLOCK12',
            'BLOCKSLIDER' => 'BLOCKSLIDER',
        ];
    }

    /**
     * Получает кол-во баннеров в блоке
     * @return int
     */
    public static function getBannerCount($blockType)
    {
        static $blockCount = [
            'BLOCK1' => 4,
            'BLOCK2' => 3,
            'BLOCK3' => 4,
            'BLOCK4' => 3,
            'BLOCK5' => 5,
            'BLOCK6' => 1,
            'BLOCK7' => 3,
            'BLOCK8' => 1,
            'BLOCK9' => 3,
            'BLOCK10' => 4,
            'BLOCK11' => 5,
            'BLOCK12' => 2,
            'BLOCKSLIDER' => 1,
        ];

        return $blockCount[$blockType] ?? 0;
    }
}
