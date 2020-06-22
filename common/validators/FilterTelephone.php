<?php
/**
 * Created by PhpStorm.
 * User: ignatenkovnikita
 * Date: 18/10/16
 * Time: 00:50
 * Web Site: http://IgnatenkovNikita.ru
 */

namespace common\validators;


use yii\validators\Validator;
use \yii\base\Model;
/**
 * Class FilterTelephone
 *
 * Delete all symbols expect numbers, replace 8 => 7. Check length.
 *
 * @author Petr Marochkin <petun911@gmail.com>
 *
 * @updated andrei.a <arhan89@gmail.com>
 * add check first symbol for country(ru)
 * Error message get from props or set default
 */
class FilterTelephone extends Validator
{
    /**
     * @param Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $errorMessage = $this->message ?? 'Невалидный номер телефона';
        $model->{$attribute} = preg_replace('/[^0-9]/','', $model->{$attribute});

        if (strlen($model->{$attribute}) == 10){
            $model->{$attribute} = 7 . $model->{$attribute};
        }

        if (!preg_match('/^\d{11}$/', $model->{$attribute})) {
            $this->addError($model, $attribute, $errorMessage);
        } else {

            $codePrefix = mb_substr($model->{$attribute}, 0, 1);

            //проверяем 1 вый символ на +7 или +8
            if (!in_array($codePrefix, [7, 8])) {
                $this->addError($model, $attribute, $errorMessage);
            } elseif($codePrefix == '8') {
                //+8 меняем на 7
                $model->{$attribute} = substr_replace($model->{$attribute}, '7', 0, 1);
            }
        }
    }
}