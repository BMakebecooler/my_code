<?php

use modules\shopandshow\models\common\form\Form2FormProperty;
use skeeks\cms\components\Cms;
use skeeks\modules\cms\form2\models\Form2Form;
use yii\db\Migration;

class m190211_154505_add_form_problem_feedback extends Migration
{
    /**
     * @var Form2Form
     */
    private $form;
    private $formCode = 'problem-feedback';

    public function init()
    {
        parent::init();

        $this->form = Form2Form::find()->andWhere(
            'code = :code', [':code' => $this->formCode]
        )->one();
    }

    public function safeUp()
    {
        if (!$this->form) {
            $this->form = new Form2Form([
                'name' => 'Форма обратной связи Проблемы с покупкой',
                'code' => $this->formCode,
            ]);

            if (!$this->form->save()) {
                var_dump($this->form->getErrors());
                die();
            }
        }

        $properties = [
            [
                'name' => 'Телефон',
                'code' => 'phone',
                'property_type' => 'S',
                'is_required' => Cms::BOOL_N,
                'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
                'form_id' => $this->form->id,
            ],
            [
                'name' => 'Описание',
                'code' => 'description',
                'property_type' => 'S',
                'is_required' => Cms::BOOL_Y,
                'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
                'form_id' => $this->form->id,
            ],
            [
                'name' => 'Страница отправки',
                'code' => 'sourceUrl',
                'property_type' => 'S',
                'is_required' => Cms::BOOL_N,
                'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
                'form_id' => $this->form->id,
            ],
            [
                'name' => 'Браузер',
                'code' => 'browser',
                'property_type' => 'S',
                'is_required' => Cms::BOOL_N,
                'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
                'form_id' => $this->form->id,
            ],
            [
                'name' => 'Платформа',
                'code' => 'platformType',
                'property_type' => 'S',
                'is_required' => Cms::BOOL_N,
                'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
                'form_id' => $this->form->id,
            ],
        ];

        foreach ($properties as $property) {
            $newProperty = new Form2FormProperty();
            $newProperty->setAttributes($property);
            if (!$newProperty->save()){
                echo "ERRORS: " . var_export($newProperty->getErrors(), true);
            }
        }

    }

    public function safeDown()
    {
        if ($this->form) {
            $this->form->delete();
        }
    }
}
