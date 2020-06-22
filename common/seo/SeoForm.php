<?php
namespace common\seo;

use common\models\Seo;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

class SeoForm extends \yii\base\Widget
{
    /** @var ActiveRecord */
    public $model = null;
    public $modelName = null;
    /** @var ActiveForm */
    public $form = null;
    public $title = 'SEO';
    const DEFAULT_META_INDEX = 'INDEX,FOLLOW';


    public function init()
    {
        if(empty($this->modelName)) {
            $this->modelName = $this->model->className();
        }

        parent::init();
    }

    public function run()
    {

        if (!$this->model->isNewRecord) {
            if (($this->model = Seo::findOne(['owner_id' => $this->model->id, 'owner' => $this->modelName])) === null) {
                $this->model = new Seo;
            }
        } else {
            $this->model = new Seo;
        }

        if(empty($this->model->meta_index)) {
            $this->model->meta_index = self::DEFAULT_META_INDEX;
        }

        $content = [];

        $content[] = $this->form->field($this->model, 'owner')->hiddenInput(['value' => $this->modelName])->label(false);

        $content[] = $this->form->field($this->model, 'h1')->textInput(['maxlength' => true]);
        $content[] = $this->form->field($this->model, 'title')->textInput(['maxlength' => true]);
        $content[] = $this->form->field($this->model, 'slug')->textInput(['maxlength' => true]);
        $content[] = $this->form->field($this->model, 'meta_keywords')->textInput(['maxlength' => true]);
        $content[] = $this->form->field($this->model, 'meta_description')->textarea(['rows' => 6]);
        $content[] = $this->form->field($this->model, 'meta_index')->textInput(['maxlength' => true]);
        $content[] = $this->form->field($this->model, 'redirect_301')->textInput(['maxlength' => true]);

        $title = Html::a($this->title, '#seo-body', ['class' => 'toggle']);
        $heading = Html::tag('div', $title, ['class' => 'panel-heading']);
        $body = Html::tag('div', implode('', $content), ['class' => 'panel-body', 'id' => 'seo-body']);

        $view = Html::tag('div', $heading . $body, ['class' => 'panel panel-default seo']);

        return $view;
    }
}