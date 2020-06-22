<?php

use modules\shopandshow\models\common\form\Form2FormProperty;
use skeeks\cms\components\Cms;
use skeeks\modules\cms\form2\models\Form2Form;
use yii\db\Migration;

class m181101_085150_black_friday_form_2018 extends Migration
{

    const FORM_CODE = 'black-friday-2018';

    /**
     * @var Form2Form
     */
    protected $form;

    public function init()
    {
        parent::init();

        $this->form = Form2Form::find()->andWhere(
            'code = :code', [':code' => self::FORM_CODE]
        )->one();
    }

    public function safeUp()
    {

        if (!$this->form) {
            $this->form = new Form2Form([
                'name' => 'Подписчики на черную пятницу 2018',
                'code' => self::FORM_CODE,
            ]);

            if (!$this->form->save()) {
                var_dump($this->form->getErrors());
                die();
            }
        }

        $properties = [
            [
                'name' => 'E-mail',
                'code' => 'email',
                'property_type' => 'S',
                'is_required' => Cms::BOOL_Y,
                'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText',
//                'component_settings' => 'a:8:{s:4:"code";s:1:"S";s:4:"name";s:10:"Текст";s:13:"default_value";s:0:"";s:12:"fieldElement";s:9:"textInput";s:4:"rows";s:1:"5";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:8:"property";a:23:{s:2:"id";i:2;s:10:"created_by";i:1;s:10:"updated_by";i:1;s:10:"created_at";i:1445106328;s:10:"updated_at";i:1445555040;s:4:"name";s:14:"Телефон";s:4:"code";s:40:"property77a7cc55fa8579d0cc5a1e7ed69cb36a";s:6:"active";s:1:"N";s:8:"priority";s:4:"1000";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:12:"multiple_cnt";N;s:16:"with_description";s:1:"N";s:10:"searchable";s:1:"Y";s:9:"filtrable";s:1:"N";s:11:"is_required";s:1:"N";s:7:"version";i:1;s:9:"component";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText";s:18:"component_settings";a:0:{}s:4:"hint";s:0:"";s:15:"smart_filtrable";s:1:"N";s:7:"form_id";s:1:"2";}s:10:"activeForm";N;}',
                'form_id' => $this->form->id,
            ],
        ];

        foreach ($properties as $property) {
            $newProperty = new Form2FormProperty();
            $newProperty->setAttributes($property);
            $newProperty->save();
        }

        return true;
    }

    public function safeDown()
    {
        if ($this->form) {
            $this->form->delete();
        }

        return true;
    }

}
