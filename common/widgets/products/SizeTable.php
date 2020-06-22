<?php

namespace common\widgets\products;

use skeeks\cms\base\WidgetRenderable;
use common\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class SizeTable
 * @package skeeks\cms\shop\cmsWidgets\filters
 */
class SizeTable extends WidgetRenderable
{


    /**
     * @var \skeeks\cms\models\CmsContentElement
     */
    public $model;


    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => 'Настройка виджета размеров',
        ]);
    }

    public function init()
    {
        parent::init();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'content_id' => \Yii::t('skeeks/shop/app', 'Content'),
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [['content_id'], 'integer'],
            ]);
    }

    public function renderConfigForm(ActiveForm $form)
    {
        echo \Yii::$app->view->renderFile(__DIR__ . '/size-tables/_form.php', [
            'form' => $form,
            'model' => $this
        ], $this);
    }
}