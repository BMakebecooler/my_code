<?php

namespace modules\shopandshow\models\form;

use skeeks\modules\cms\form2\models\Form2FormSend as SxForm2FormSend;
use skeeks\modules\cms\form2\models\Form2FormSendProperty;


/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 20.12.17
 * Time: 16:41
 */
class Form2FormSend extends SxForm2FormSend
{


    /**
     * Добавить значение в форму
     * @param array $data
     * @return bool
     */
    public static function addData($data = [])
    {
        $formSend = new self();

        $formSend->data_values = $data['data_values'];
        $formSend->data_labels = $data['data_labels'];
        $formSend->form_id = $data['form_id'];
        $formSend->status = $data['status'];

        if ($formSend->save()) {

            $formSendProperty = new Form2FormSendProperty();
            $formSendProperty->element_id = $formSend->id;
            $formSendProperty->property_id = $data['property_id'];
            $formSendProperty->value = $data['value'];


            if (!$formSendProperty->save()) {
                var_dump($formSendProperty->getErrors());
                die();
            }
            return true;

        } else {
            var_dump($formSend->getErrors());
            die();
        }

    }
}