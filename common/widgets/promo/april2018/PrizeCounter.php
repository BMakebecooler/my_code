<?php

/**
 * Класс для вычесления оставшихся призов
 */

namespace common\widgets\promo\april2018;


class PrizeCounter
{

    /**
     * Общее кол-во телефонов
     */
    const COUNT_PHONE = 500;

    /**
     * Общее кол-во планшетов
     */
    const COUNT_TABLET = 50;

    /**
     * Общее кол-во телевизоров
     */
    const COUNT_TV = 1;

    private $dateStart = '2018-05-21 08:00:00';
    private $dateEnd = '2018-05-26 06:59:59';

    private $contestDayNum = 0;

    /**
     * Кол-во оставшихся дней акции
     * @var
     */
    private $countDays;

    public function __construct()
    {
        $this->getDays();
        $this->getContestDayNum();
    }

    /**
     * Получить кол-во дней акции
     * @return mixed
     */
    private function getDays()
    {
        if ($this->countDays) {
            return $this->countDays;
        }

        $ts1 = strtotime($this->dateStart);
        $ts2 = strtotime($this->dateEnd);

        $result = ceil(($ts2 - $ts1) / 3600 / 24);

        $this->countDays = ($result) ?: 1;
    }

    public function getContestDayNum()
    {
        if ($this->contestDayNum) {
            return $this->contestDayNum;
        }

        $this->contestDayNum = max(0, ceil((time() - strtotime($this->dateStart)) / 3600 / 24));

        return $this->contestDayNum;
    }

    /**
     * Всего телефонов
     * @return int
     */
    public function getAllPhone()
    {
        return self::COUNT_PHONE;
    }

    /**
     * Выйграно телефонов
     * @return int
     */
    public function getWonPhone($widget)
    {
        //Если конкурс не начался - ничего не выиграно
        if (!$this->contestDayNum) {
            return self::COUNT_PHONE;
        } elseif ($this->contestDayNum > $this->getDays()) {
            return 0;
        }

        $countPhoneWon = $widget->getCountWinners($widget::WINNER_TYPE_PHONE);
        return max(0, self::COUNT_PHONE - $countPhoneWon);

        /*
        $countPhoneWonDay = floor(self::COUNT_PHONE / $this->countDays); //Получаем кол-во телефонов в день
        $hour = (int)date('G') + 1;

        $countPhoneWonHour = 14;

        //Кол-во выиграных призов за уже прошедшие дни конкурса
        $prevDaysWonPrizes = $this->contestDayNum ? ($this->contestDayNum - 1) * $countPhoneWonDay : 0;

        if ($hour >= 8 && $hour <= 22) {
            $nowHour = $hour - 8;

            if ($nowHour <= 1) {
                return self::COUNT_PHONE - floor($countPhoneWonDay / $countPhoneWonHour) - $prevDaysWonPrizes;
            } else {
                return self::COUNT_PHONE - floor($countPhoneWonDay / $countPhoneWonHour * $nowHour) - $prevDaysWonPrizes;
            }
        }

        return self::COUNT_PHONE - $countPhoneWonDay - $prevDaysWonPrizes;
        */
    }

    public function getAllTablet()
    {
        return self::COUNT_TABLET;
    }

    public function getWonTablet($widget)
    {
        //Если конкурс не начался - ничего не выиграно
        if (!$this->contestDayNum) {
            return self::COUNT_TABLET;
        } elseif ($this->contestDayNum > $this->getDays()) {
            return 0;
        }

        $countTabletWon = $widget->getCountWinners($widget::WINNER_TYPE_TABLET);
        return max(0, self::COUNT_TABLET - $countTabletWon);

        /*
        $countTabletWonDay = floor(self::COUNT_TABLET / $this->countDays); //Получаем кол-во планшетов в день
        $hour = (int)date('G') + 1;

        //$countTabletWonHour = 7;
        $countTabletWonHour = 14;

        //Кол-во выиграных призов за уже прошедшие дни конкурса
        $prevDaysWonPrizes = $this->contestDayNum ? ($this->contestDayNum - 1) * $countTabletWonDay : 0;

        if ($hour >= 8 && $hour <= 22) {
            $nowHour = $hour - 8;

            if ($nowHour <= 1) {
                return self::COUNT_TABLET - floor($countTabletWonDay / $countTabletWonHour) - $prevDaysWonPrizes;
            } else {
                return self::COUNT_TABLET - floor($countTabletWonDay / $countTabletWonHour * $nowHour) - $prevDaysWonPrizes;
            }
        }

        return self::COUNT_TABLET - $countTabletWonDay - $prevDaysWonPrizes;
        */
    }

    public function getAllTv()
    {
        return self::COUNT_TV;
    }

    public function getWonTv($widget)
    {
        //Если конкурс не начался - ничего не выиграно
        if (!$this->contestDayNum) {
            return self::COUNT_TV;
        } elseif ($this->contestDayNum > $this->getDays()) {
            return 0;
        }

        $countTvWon = $widget->getCountWinners($widget::WINNER_TYPE_TV);
        return max(0, self::COUNT_TV - $countTvWon);

        /*
        //Для телевизоров логика чуть другая ибо их получается меньше одного в день

        $countTvWonDay = self::COUNT_TV / $this->countDays; //Получаем кол-во телевизоров в день
        $countTvWonHour = $countTvWonDay / 14; //Получаем кол-во телевизоров в час
        $hour = (int)date('G') + 1;

        $prevDaysNum = $this->contestDayNum ? ($this->contestDayNum - 1) : 0;

        //Кол-во выиграных призов за уже прошедшие дни конкурса
        $nowHour = ($hour >= 8 && $hour <= 22) ? ($hour - 8) : 0;
        $wonPrizes = floor( $prevDaysNum * $countTvWonDay + $countTvWonHour * $nowHour );

        return self::COUNT_TV - $wonPrizes;
        */
    }

    /**
     * @return string
     */
    public function getDateStart(): string
    {
        return $this->dateStart;
    }

    /**
     * @return string
     */
    public function getDateEnd(): string
    {
        return $this->dateEnd;
    }

}