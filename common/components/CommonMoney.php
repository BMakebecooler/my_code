<?php

namespace common\components;

use common\helpers\Strings;
use skeeks\modules\cms\money\components\money\Money;

/**
 * Class CommonMoney
 * @package common\components
 */
class CommonMoney extends Money
{

    /**
     * Сконвертировать и отформатировать для текущих настроек
     *
     * @param \skeeks\modules\cms\money\Money $money
     * @param null $language
     * @param null $currency
     * @return string
     */
    public function convertAndFormat(\skeeks\modules\cms\money\Money $money, $language = null, $currency = null)
    {
        if ($money->getCurrency()->getCurrencyCode() != "RUB") {
            return parent::convertAndFormat($money, $language, $currency);
        }

        if (!$currency) {
            $currency = $this->currencyCode;
        }

        $convertedMoney = $money->convertToCurrency($currency);

        $f = new \NumberFormatter(\Yii::$app->language, \NumberFormatter::DECIMAL);
        $f->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);

        return $f->format($convertedMoney->getValue()) ; //. Strings::roubleSymbolSpan();
    }
}