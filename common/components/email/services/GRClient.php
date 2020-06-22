<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 14.09.17
 * Time: 20:46
 */

namespace common\components\email\services;

use common\components\email\services\modules\newsLetters\GRCreateNewsLettersOptions;
use modules\shopandshow\models\mail\MailDispatch;
use rvkulikov\yii2\getResponse\modules\segments\GRGetSegmentsOptions;
use rvkulikov\yii2\getResponse\GRClient as KulikovGRClient;
use rvkulikov\yii2\getResponse\modules\contacts\GRUpdateContactOptions;
use rvkulikov\yii2\getResponse\modules\customFields\GRGetCustomFieldsOptions;
use rvkulikov\yii2\getResponse\modules\segments\GRGetSegmentContactsOptions;

class GRClient extends KulikovGRClient
{

    /**
     * @var GRApiNewsLetters
     */
    protected $newsLetters;


    public $campaignToken;

    public $segmentContactsPerPage = 1000;

    /**
     * @return GRApiNewsLetters
     */
    public function getNewsLetters()
    {
        if (!$this->newsLetters) {
            $this->newsLetters = new GRApiNewsLetters([
                'httpClient' => $this->getHttpClient()
            ]);
        }

        return $this->newsLetters;
    }

    /**
     * Вернуть токен компании
     * Смотреть тут http://www.email.shopandshow.ru/campaign_list.html
     * @return mixed
     */
    public function getCampaignToken()
    {
        return $this->campaignToken;
    }

    /**
     * Установить токен компании
     * Смотреть тут http://www.email.shopandshow.ru/campaign_list.html
     * @param $campaignToken
     */
    public function setCampaignToken($campaignToken)
    {
        $this->campaignToken = $campaignToken;
    }

    /**
     * @param MailDispatch $mailDispatch
     *
     * @return array|mixed
     */
    public function sendMailDispatch(MailDispatch $mailDispatch, array $segments = [])
    {

        $todayDispatches = MailDispatch::getTodayDispatches(['status' => 'S']);

        $grClient = \Yii::$app->getResponseService;
        $campaign = $grClient->getCampaigns()->getCampaign($grClient->getCampaignToken());

        $settings = [];
        $segmentsIds = [];

        //Пришли сегменты - запишем об этом в лог рассылки
        if (!empty($segments)) {
            $mailDispatch->setSegments(implode(', ', $segments));
        }

        //Проверка на кол-во отправленных сегодня рассылок
        //Если больше разрешенного - пишем ошибку и прекращаем попытку отправки
        if (count($todayDispatches) >= MailDispatch::DISPATCHES_LIMIT_PER_DAY) {
            $logMessage = "[ERROR] Сверхлимитная рассылка (лимит = " . MailDispatch::DISPATCHES_LIMIT_PER_DAY . ")";
            $mailDispatch->setMessage($logMessage);
            \Yii::error("Ошибка при отправке рассылки: {$logMessage}");
            return ['error' => $logMessage];
        }

        //Если пришли названия сегментов - ищем их и записываем ID
        if (!empty($segments)) {
            //Пробуем получить сегменты
            foreach ($segments as $segment) {
                $grSegments = $this->getSegmentsByName($segment);
                if ($grSegments) {
                    foreach ($grSegments as $grSegment) {
                        $segmentsIds[] = $grSegment['searchContactId'];
                    }
                }
            }

            //Если сегменты нашлись - пишем их ID в настройки для отправки рассылки
            if ($segmentsIds) {
                $settings = [
                    'selectedSegments' => $segmentsIds
                ];
            }
        }

        //Если в настройках отправки указан сегмент, то в блоке sendSettings не указываем selectedCampaigns
        if (empty($segments)) {
            $settings['selectedCampaigns'] = [
                $campaign['campaignId']
            ];
        }

        $newLetter = new GRCreateNewsLettersOptions([
            'name' => $mailDispatch->subject,
            'type' => 'broadcast', // draft - черновик broadcast - рассылка
            'editor' => 'html2',
            'subject' => $mailDispatch->subject, //*
            'campaign' => [
                'campaignId' => $campaign['campaignId'],
            ], //*
            'fromField' => [
                'fromFieldId' => $campaign['confirmation']['fromField']['fromFieldId']
            ], //*

            'replyTo' => null,
            'content' => [
                'html' => $mailDispatch->body
            ], //*

            'flags' => ['openrate', 'clicktrack'], //Message flags. Allowed values: openrate, clicktrack and google_analytics
//            'attachments' => 'test',

            'sendSettings' => array_merge(
                $settings,
                [
                    /*'selectedContacts' => [
                        $campaign['campaignId']
                    ],*/
                    'timeTravel' => 'false',
                    'perfectTiming' => 'false',
                ]
            ), //*
        ]);

        //Проверим, если указаны сегменты для рассылки и для них не нашлось самих сегментов - запишем как ошибка
        //И саму рассылку не запускаем
        if ($segments && empty($segmentsIds)){
            $logMessage = "[ERROR] Сегменты не найдены";
            $mailDispatch->setMessage($logMessage);
            \Yii::error("Ошибка при отправке рассылки: {$logMessage}");
            return ['error' => $logMessage];
        }

        return $grClient->getNewsLetters()->sendNewsletter($newLetter);
    }

    /** Получение массива сегментов рассылки по названию сегмента
     * @param string $name
     * @return array
     */
    public function getSegmentsByName($name){
        $grClient = \Yii::$app->getResponseService;

        //Получение списка всех сегментов
        //$grSegments = $grClient->getSegments()->getSegments();
        //Контакты отдельного сегмента
        //$grSegmentContacts = $grClient->getSegments()->getSegmentContacts('9V');

        //Получение списка всех сегментов
        $grSegments = $grClient->getSegments()->getSegments(
            new GRGetSegmentsOptions(
                [
                    'query' => ['name' => $name]
                ]
            )
        );

        return !empty($grSegments) ? $grSegments : [];
    }

    /**
     * Получение общего списка настраиваемых полей
     *
     * @param string $fields - список свойств настраиваемого поля через зпт (href, name, fieldType, format, valueType, type, hidden, values)
     * @return mixed[]
     */
    public function getCustomFieldsList($fields = 'name')
    {
        $grClient = \Yii::$app->getResponseService;

        $getCustomFieldsOptions = new GRGetCustomFieldsOptions([
            'fields'    => (string)$fields
        ]);

        return $grClient->getCustomFields()->getCustomFields($getCustomFieldsOptions);
    }

    /**
     * Обновление настраиваемых полей указанного контакта
     *
     * @param $contactId - идентификатор контакта в GR
     * @param $customFieldsForUpdate - массив полей для обновления, ключи - customFieldId, значения - (строка|массив) значений поля
     * @return array актуальные данные контакта
     */
    public function updateContactCustomFields($contactId, $customFieldsForUpdate){
        $grClient = \Yii::$app->getResponseService;

        $contactData = $grClient->getContacts()->getContact($contactId);

        if ($contactData){

            $contactCustomFields = !empty($contactData['customFieldValues']) ? $contactData['customFieldValues'] : [];

            //обновление данных контакта (настраиваемое поле)
            //Так как не указанные в обновлении поля почему то удаляются - то пересобираем массив настраиваемых полей контакта
            //дописывая изменения в нужные нам места
            //Учитываем что нужного нам поля в списке может и не быть

            $updateContactCustomFields = [];

            $updatedCustomFields = [];

            if ($contactCustomFields){
                foreach ($contactCustomFields as $contactCustomField) {
                    $customFieldId = $contactCustomField['customFieldId'];

                    if( isset($customFieldsForUpdate[$customFieldId]) ){
                        $customFieldValue = (array)$customFieldsForUpdate[$customFieldId];
                        $updatedCustomFields[] = $customFieldId;
                    }else{
                        $customFieldValue = $contactCustomField['value'];
                    }

                    $updateContactCustomFields[] = [
                        'customFieldId' => $customFieldId,
                        'value' => $customFieldValue
                    ];
                }
            }

            //Проходимся по списку того что надо обновить проверяя все ли у нас записалось на обновление
            foreach ($customFieldsForUpdate as $customFieldId => $customFieldValue) {
                if (!in_array($customFieldId, $updatedCustomFields)){
                    $updateContactCustomFields[] = [
                        'customFieldId' => $customFieldId,
                        'value' => (array)$customFieldValue
                    ];
                }
            }

            $updateContactOptions = new GRUpdateContactOptions([
                'customFieldValues' => $updateContactCustomFields
            ]);

            $updateContactResult = $grClient->getContacts()->updateContact($contactId, $updateContactOptions);
        }

        return !empty($updateContactResult) ? $updateContactResult : $contactData;
    }

    /**
     * Получение списка контактов сегмента по его имени с возмжностью фильтрации по вилке дат
     *
     * @param $segmetName - название сегмента в getResponse
     * @param array $options - массив опций, в основном для вилки дат
     * @return array|mixed[]|string - массив если сегмент нашелся, иначе - строка с текстом ошибки
     */
    public function getSegmentContacts($segmetName, $options = [], $fields = ''){
        $grClient = \Yii::$app->getResponseService;

        $grSegments = $this->getSegmentsByName($segmetName);

        if (!empty($grSegments)){
            $grSegmentId = (current($grSegments))['searchContactId'];

            //Если фильтруется по датам, то в выборку добавим и это поле для нужд дальнейшей фильтрации
            if (!empty($options['dateFrom']) || !empty($options['dateTo'])){
                $fields .= ($fields ? ',':'') . 'createdOn';
            }

            //Получаем контакты сегмента
            $segmentContactsOptions = new GRGetSegmentContactsOptions([
                'fields'    => $fields,
                //GR не умеет возвращать просто кол-во, приходится запрашивать всё и потом считать/фильтровать
                //Указав большой perPage все равно получаем от gR только 1000 контактов :(
                //Так что приходится использовать постраничный перебор. Пока так.
                'perPage'   => $this->segmentContactsPerPage
            ]);

            //$segmentContacts = $grClient->getSegments()->getSegmentContacts($grSegmentId, $segmentContactsOptions);
            $segmentContacts = [];

            $pageNum = 1;
            do{
                $segmentContactsOptions->page = $pageNum;

                $segmentContactsPart = $grClient->getSegments()->getSegmentContacts($grSegmentId, $segmentContactsOptions);

                if ($segmentContactsPart){
                    $segmentContacts = array_merge($segmentContacts, $segmentContactsPart);
                }

                $pageNum++;
            }while(count($segmentContactsPart) > 0);

            //Так как в API контактов сегмента нельзя фильтровать произвольно по датам
            //То приходится фильтровать полный набор после получения
            if (!empty($options['dateFrom']) || !empty($options['dateTo'])){
                $dateFrom   = !empty($options['dateFrom']) ? strtotime($options['dateFrom']) : false;
                $dateTo     = !empty($options['dateTo']) ? strtotime($options['dateTo'] . ' 23:59:59') : false;

                $segmentContacts = array_filter($segmentContacts, function ($contact) use ($dateFrom, $dateTo){
                    $dateContact = strtotime($contact['createdOn']);

                    return ($dateFrom ? ($dateContact >= $dateFrom) : true) && ($dateTo ? ($dateContact <= $dateTo) : true);
                });
            }

        }else{
            $segmentContacts = "Сегмент '{$segmetName}' не найден!";
        }

        return $segmentContacts;
    }
}