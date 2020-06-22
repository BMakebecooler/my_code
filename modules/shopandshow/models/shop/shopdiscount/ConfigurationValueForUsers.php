<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use common\models\user\User;
use modules\shopandshow\models\shop\ShopBasket;

/**
 * Расширение конфигурации для условия "тестовые пользователи"
 */
class ConfigurationValueForUsers extends ConfigurationValue
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => 'Список пользователей'
        ];
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function formatOutput($value)
    {
        return 'Разработчики и тестировщики';
    }

    /**
     * Проверяет права пользователя
     * @inheritdoc
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        if (!\Yii::$app->has('user')) {
            return false;
        }

        return \common\helpers\User::isDeveloper() || \common\helpers\User::isTester();
    }
}
