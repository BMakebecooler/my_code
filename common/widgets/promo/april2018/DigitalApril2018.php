<?php

namespace common\widgets\promo\april2018;

use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use skeeks\cms\base\WidgetRenderable;
use yii\db\ActiveRecord;

/**
 * Class DigitalApril2018
 */
class DigitalApril2018 extends WidgetRenderable
{

    const WINNER_TYPE_NONE = 0;
    const WINNER_TYPE_PHONE = 1;
    const WINNER_TYPE_TABLET = 2;
    const WINNER_TYPE_TV = 3;

    const WINNER_CONTENT_CODE = 'winners';

    public $viewFile = '@template/widgets/Promo/_digital_april_bar';

    /** @var CmsContent */
    private $winnerContent;

    // закешированный вариант значения метода getTimeToWin()
    private $timeToWin = null;

    /**
     * @var $prizeCounter \common\widgets\promo\april2018\PrizeCounter
     */
    private $prizeCounter;

    public function init()
    {
        $this->winnerContent = \common\lists\Contents::getContentByCode(self::WINNER_CONTENT_CODE);

        if (!$this->winnerContent) {
            throw new \ErrorException('Не найден контент с победителями по коду ' . self::WINNER_CONTENT_CODE);
        }

        $this->prizeCounter = new PrizeCounter();

        parent::init();
    }

    public function run()
    {
        return $this->render($this->viewFile);
    }

    /**
     * Получает последнего победителя
     * @return CmsContentElement | ActiveRecord
     **/
    public function getLastWinner()
    {
        return CmsContentElement::find()
            ->where(['content_id' => $this->winnerContent->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    /**
     * Получает победителя за последние 5 минут (для блока только что выиграл)
     * @param int $minutes - сколько минут отсчитывать
     * @return CmsContentElement | array|null|ActiveRecord
     */
    public function getCurrentWinner($minutes = 1)
    {
        return CmsContentElement::find()
            ->where(['content_id' => $this->winnerContent->id])
            ->andWhere(new \yii\db\Expression('created_at > UNIX_TIMESTAMP(NOW() - INTERVAL ' . intval($minutes) . ' MINUTE)'))
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    /**
     * Сообщает о том, что осталось примерно 5 заказов до победы
     * @param int $numOrdersToWin - число заказов до победы
     * @return int тип победителя (1 - 100, 2 - 1000, 3 - 50000, 0 - до победы еще долго...)
     */
    public function getTimeToWin($numOrdersToWin = 10)
    {
        if (!\Yii::$app->has('front_db')) {
            return self::WINNER_TYPE_NONE;
        }

        if ($this->timeToWin === null) {
            $query = <<<SQL
    select max(id) from b_sale_order
SQL;

            $orderId = \Yii::$app->front_db->createCommand($query)->queryScalar();
            $roundedOrderId = 100 * ceil($orderId / 100);

            if ($roundedOrderId - $orderId > $numOrdersToWin) {
                $this->timeToWin = self::WINNER_TYPE_NONE;
                return $this->timeToWin;
            }

            if ($roundedOrderId % 50000 == 0) {
                $this->timeToWin = self::WINNER_TYPE_TV;
            } elseif ($roundedOrderId % 1000 == 0) {
                $this->timeToWin = self::WINNER_TYPE_TABLET;
            }

            $this->timeToWin = self::WINNER_TYPE_PHONE;
        }

        return $this->timeToWin;
    }

    /**
     * Признак что в ближ время будет разыгран телефон
     * @return bool
     */
    public function isTimeToWinPhone()
    {
        return $this->getTimeToWin() === self::WINNER_TYPE_PHONE;
    }

    /**
     * Признак что в ближ время будет разыгран планшет
     * @return bool
     */
    public function isTimeToWinTablet()
    {
        return $this->getTimeToWin() === self::WINNER_TYPE_TABLET;
    }

    /**
     * Признак что в ближ время будет разыгран телек
     * @return bool
     */
    public function isTimeToWinTv()
    {
        return $this->getTimeToWin() === self::WINNER_TYPE_TV;
    }

    /**
     * Получает всех победителей
     * @return array|ActiveRecord[]
     */
    public function getAllWinners()
    {
        return CmsContentElement::find()
            ->with('cmsContentElementProperties')
            ->where(['content_id' => $this->winnerContent->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }

    public function getCountWinners($type)
    {
        switch ($type) {
            case self::WINNER_TYPE_TV:
                $prize = ['Телевизор Harper 49U750TS (004777280)', 'Телевизор Harper 49U750TS (004919530)'];
                break;
            case self::WINNER_TYPE_TABLET:
                $prize = ['Планшет Irbis TZ184 (004777276)', 'Планшет Irbis TZ184 (004919529)'];
                break;
            case self::WINNER_TYPE_PHONE:
            default:
                $prize = ['ARK Benefit S504 (004777251)', 'ARK Benefit S504 (004919527)'];
                break;
        }

        return CmsContentElement::find()
            ->joinWith('cmsContentElementProperties')
            ->where(['content_id' => $this->winnerContent->id])
            ->andWhere(['cms_content_element_property.value' => $prize])
            ->andWhere(['>=', 'cms_content_element_property.created_at', strtotime($this->prizeCounter->getDateStart())])
            ->count();
    }

    /**
     * Получает список победителей по последним 4 цифрам телефона
     * @return array|ActiveRecord[]
     */
    public function getWinnersByPhone($phone)
    {
        return CmsContentElement::find()
            ->alias('content_element')
            ->joinWith('cmsContentElementProperties')
            ->leftJoin(CmsContentProperty::tableName().' AS content_prop',
                "content_prop.content_id={$this->winnerContent->id}")
            ->andWhere(['cms_content_element_property.property_id' => CmsContentProperty::find()->select(['id'])->andWhere(['code' => 'winner_phone'])])
            ->andWhere(['like', 'cms_content_element_property.value', "%$phone", false])
            ->all();
    }

    /**
     * Отдает последние 4 цифры телефона
     * @param $phone
     * @return string
     */
    public function getLastPhoneDigits($phone)
    {
        // берем последние 5 цифр
        $lastNums = substr($phone, -5);
        // если это число, т.е. номер был неотформатирован вида 74951234567, то вырезаем еще одну цифру
        if (is_numeric($lastNums)) {
            $lastNums = sprintf('%s-%s', substr($lastNums, 1, 2), substr($lastNums, -2));
        }
        return  $lastNums;
    }
}