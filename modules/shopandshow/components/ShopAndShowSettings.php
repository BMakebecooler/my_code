<?php

namespace modules\shopandshow\components;

use skeeks\cms\base\Component;

use skeeks\cms\shop\models\ShopPersonType;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * Class ShopAndShowSettings
 * @package modules\shopandshow\components
 */
class ShopAndShowSettings extends Component
{

    /**
     * Ссылка на трансляцию YouTube
     * @var string
     */
    public $translationLink = '';

    /**
     * Телефон для заказов
     * @var string
     */
    public $orderPhone = '';

    /**
     * Гуид канала продаж, по которому сайт отправляет данные в очередь
     * @var string
     */
    public $channelSaleGuid = '';

    /**
     * Гуид канала продаж, по которому сайт принимает остатки
     * @var string
     */
    public $channelReserveGuid = '';

    /**
     * Признак включения Ритейл рокета
     * @var bool
     */
    public $enabledRR = 0;

    /**
     * Признак включения трекинг кодов Ритейл рокета
     * @var bool
     */
    public $enabledRRtrack = 0;

    /**
     * Признак включения гугл приложений
     * @var bool
     */
    public $enabledGoogle = 0;

    /**
     * Процент накрутки фактических продаж в отчете
     * @var float
     */
    public $monitoringDayFactor = 0;

    /**
     * включить фильтры
     * @var bool
     */
    public $enabledShopFilters = 0;

    /**
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => 'Настройки shop & show',
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [[
                'translationLink', 'orderPhone', 'channelSaleGuid', 'channelReserveGuid', 'enabledRR', 'enabledRRtrack', 'enabledGoogle',
                'enabledShopFilters',
            ], 'string'],
            [['monitoringDayFactor'], 'double'],
        ]);

    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'translationLink' => 'Ссылка на трансляцию YouTube',
            'orderPhone' => 'Телефон для заказов',
            'channelSaleGuid' => 'Гуид канала продаж для отправки в очередь',
            'channelReserveGuid' => 'Гуид канала продаж для получения остатков',
            'enabledRR' => 'Включение ритейл рокета (1 - да; 0 - нет;)',
            'enabledRRtrack' => 'Включение трекинг кодов ритейл рокета (1 - да; 0 - нет;)',
            'enabledGoogle' => 'Включение гугл приложений (1 - да; 0 - нет;)',
            'monitoringDayFactor' => 'Поправляющий коэффициент в мониторинге дня (в %)',
            'enabledShopFilters' => 'Включить фильтры (1 - да; 0 - нет;)',
        ]);
    }

    public function attributeHints()
    {

    }

    public function renderConfigForm(ActiveForm $form)
    {
        echo $form->fieldSet('Общие настройки');
        echo $form->field($this, 'translationLink');
        echo $form->field($this, 'orderPhone');
        echo $form->field($this, 'enabledShopFilters');
        echo $form->fieldSetEnd();

        echo $form->fieldSet('Обмен с другими системами');
        echo $form->field($this, 'channelSaleGuid');
        echo $form->field($this, 'channelReserveGuid');
        echo $form->fieldSetEnd();

        echo $form->fieldSet('Отчеты');
        echo $form->field($this, 'monitoringDayFactor');
        echo $form->fieldSetEnd();

        echo $form->fieldSet('Внешние системы продаж');
        echo $form->field($this, 'enabledGoogle');
        echo $form->field($this, 'enabledRR');
        echo $form->field($this, 'enabledRRtrack');
        echo $form->fieldSetEnd();

        /*
        echo $form->fieldSet('Другие настройки 2');
        echo $form->fieldSetEnd();*/

    }

}
