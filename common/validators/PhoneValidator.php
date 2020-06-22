<?php

    namespace common\validators;

    use yii\validators\Validator;
    use libphonenumber\PhoneNumberUtil;
    use libphonenumber\PhoneNumberFormat;
    use libphonenumber\NumberParseException;
    use Exception;

    class PhoneValidator extends Validator
    {
        public $strict = true;
        public $countryAttribute;
        public $country = 'ru';
        public $format = true;

        public function validateAttribute($model, $attribute)
        {
            // if countryAttribute is set
            if (!isset($country) && isset($this->countryAttribute)) {
                $countryAttribute = $this->countryAttribute;
                $country = $model->$countryAttribute;
            }
            // if country is fixed
            if (!isset($country) && isset($this->country)) {
                $country = $this->country;
            }

            // if none select from our models with best effort
            if (!isset($country) && isset($model->country_code))
                $country = $model->country_code;
            if (!isset($country) && isset($model->country))
                $country = $model->country;

            // if none and strict
            if (!isset($country) && $this->strict) {
                $this->addError($model, $attribute, 'Не определена страна');
                return false;
            }
            if (!isset($country)) {
                return true;
            }
            $phoneUtil = PhoneNumberUtil::getInstance();
            $errorMessage = $this->message ?? 'Введите корректный номер телефона';

            try {
                $numberProto = $phoneUtil->parse($model->$attribute, $country);

                if ($phoneUtil->isValidNumber($numberProto)) {
                    if ($this->format == true)
                        $model->$attribute = $phoneUtil->format($numberProto, PhoneNumberFormat::INTERNATIONAL);
                    return true;
                } else {
                    $this->addError($model, $attribute, $errorMessage);
                    return false;
                }
            } catch (NumberParseException $e) {
                $this->addError($model, $attribute, $errorMessage);
            } catch (Exception $e) {
                $this->addError($model, $attribute, $errorMessage);
            }
        }
    }