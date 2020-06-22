<?php

namespace modules\shopandshow\services;

use common\helpers\User;
use modules\shopandshow\components\task\SendSurveyMailTaskHandler;
use modules\shopandshow\models\mail\MailTemplate;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\task\SsTask;

/**
 * Опросы
 *
 * Class Survey
 * @package common\helpers
 */
class Survey
{

    const ORDER_FINISH_TYPE = 'ORDER_FINISH_TYPE';
    const ORDER_CANCEL_TYPE = 'ORDER_CANCEL_TYPE';
    const ORDER_COMPLETE_TYPE = 'ORDER_COMPLETE_TYPE';

    /**
     * Для тех, кто оформил заказ (отправляем сразу после оформления заказа)
     */
    const ORDER_FINISH_LINK = 'https://ru.surveymonkey.com/r/TRKLH8M';
    /**
     * Для тех, кто отменил заказ
     */
    const ORDER_CANCEL_LINK = 'https://ru.surveymonkey.com/r/D5YTFDT';
    /**
     * Для тех, кто выкупил заказ
     */
    const ORDER_COMPLETE_LINK = 'https://ru.surveymonkey.com/r/R7XTQVM';


    const SURVEY_LINKS = [
        self::ORDER_FINISH_TYPE => self::ORDER_FINISH_LINK,
        self::ORDER_CANCEL_TYPE => self::ORDER_CANCEL_LINK,
        self::ORDER_COMPLETE_TYPE => self::ORDER_COMPLETE_LINK,
    ];

    public $type;

    /**
     * Survey constructor.
     * @param null $type
     */
    public function __construct($type = null)
    {
        $this->type = $type;
    }

    /**
     * получает тему письма
     * @return null|string
     * @throws \Exception
     */
    public function getSubject()
    {
        if (!$this->type) {
            throw new \Exception('не указан тип');
        }

        switch ($this->type) {
            case self::ORDER_FINISH_TYPE:
                return 'Пожалуйста, пройдите опрос Shop&Show для покупателей. Займет 2 минуты.';
            case self::ORDER_CANCEL_TYPE:
                return 'Отменили заказ? Пожалуйста, заполните короткую анкету. Займет 2 минуты.';
            case self::ORDER_COMPLETE_TYPE:
                return 'Получили заказ от Shop&Show? Пожалуйста, заполните короткую анкету. Займет 2 минуты.';
        }
        return null;
    }

    /**
     * получает тело письма
     * @return null|string
     * @throws \Exception
     */
    public function getBody()
    {
        if (!$this->type || !array_key_exists($this->type, self::SURVEY_LINKS)) {
            throw new \Exception('не указан тип, или он указан неверно');
        }

        $link = self::SURVEY_LINKS[$this->type];

        switch ($this->type) {
            case self::ORDER_FINISH_TYPE:
                return <<<HTML
<p>Здравствуйте! Спасибо, что выбрали Shop&Show!</p>
<br>
<p>Пожалуйста, ответьте на несколько вопросов о нашей компании. Это займет у вас не больше двух минут, а нам поможет стать лучше.</p>
<br>
<p><a href="{$link}" target="_blank">Пройти опрос</a></p>
<br>
<p>С уважением, Shop&Show</p>
<br>
HTML;
            case self::ORDER_CANCEL_TYPE:
                return <<<HTML
<p>Здравствуйте! Для улучшения качества обслуживания наших клиентов, мы просим вас пройти короткую анкету. Это займет не более двух минут.</p>
<br>
<p>Заранее спасибо!</p>
<br>
<p><a href="{$link}" target="_blank">Пройти опрос</a></p>
<br>
<p>С уважением, Shop&Show</p>
<br>
HTML;
            case self::ORDER_COMPLETE_TYPE:
                return <<<HTML
<p>Здравствуйте! Для улучшения качества обслуживания наших клиентов, мы просим вас пройти короткую анкету. Это займет не более двух минут.</p>
<br>
<p>Заранее спасибо!</p>
<br>
<p><a href="{$link}" target="_blank">Пройти опрос</a></p>
<br>
<p>С уважением, Shop&Show</p>
<br>
HTML;
        }
        return null;
    }

    /**
     * Получает емейл пользователя
     *
     * @param \common\models\user\User $orderUser
     * @return string
     */
    public static function getUserEmail($orderUser = null)
    {
        $user = $orderUser ?: User::getUser();

        if ($user && $user->email && User::isRealEmail($user->email)) {
            return $user->email;
        }

        return null;
    }

    /**
     * получает ссылку на анкету
     * @return string
     */
    public static function getOrderFinishLink()
    {
        // если у пользователя есть email - ссылку не выводим
        if (self::getUserEmail()) {
            return '';
        }

        return '<p><a href="'.self::ORDER_FINISH_LINK.'" target="_blank">Пройдите анкету для покупателей сайта!</a></p>';
    }

    /**
     * выполняет отправку письма (в данномслучае ставит задание на отправку)
     * @param $type
     * @param ShopOrder $shopOrder
     */
    public static function sendSurvey($type, $shopOrder = null)
    {
        $user = $shopOrder ? $shopOrder->user : null;
        $email = self::getUserEmail($user);
        if ($email) {
            SsTask::createNewTask(SendSurveyMailTaskHandler::className(), ['email' => $email, 'type' => $type]);
        }
    }
}