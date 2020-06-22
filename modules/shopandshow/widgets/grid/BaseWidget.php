<?php
namespace modules\shopandshow\widgets\grid;

use modules\shopandshow\components\mail\BaseTemplate;
use skeeks\cms\base\WidgetRenderable;
use yii\widgets\ActiveForm;

/**
 * Class BaseWidget
 *
 * @property string $absUrl
 *
 * @package modules\shopandshow\widgets\grid
 */
class BaseWidget extends WidgetRenderable
{
    // неймспейс для хранения в БД
    public $namespace = '';
    // верстка виджета для рассылки
    public $viewFile = '';
    // файл с формой администрирования виджета
    public $formFile = '';
    // шаблон
    /** @var BaseTemplate */
    public $template = null;

    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => 'Настройка виджета',
        ]);
    }

    public function renderConfigForm(ActiveForm $form)
    {
        return $this->render($this->formFile, [
            'form' => $form,
            'model' => $this,
        ]);
    }

    public function getAbsUrl()
    {
        if ($this->template && $this->template instanceof BaseTemplate) {
            return $this->template->getAbsUrl();
        }

        if(\Yii::$app instanceof \yii\web\Application) {
            return \Yii::$app->urlManager->hostInfo;
        }
        else {
            return \Yii::$app->urlManager->baseUrl;
        }
    }

    public function getAbsImgPath()
    {
        if ($this->template && $this->template instanceof BaseTemplate) {
            return $this->template->getAbsImgPath();
        }

        return $this->absUrl.\Yii::getAlias('@web_common').'/img/sands_grid';
    }

    public function makeAbsUrl($url)
    {
        if ($this->template && $this->template instanceof BaseTemplate) {
            return $this->template->makeAbsUrl($url);
        }

        if (strpos(strtolower($url), 'http') !== 0 && strpos(strtolower($url), 'mailto:') !== 0 && strpos(strtolower($url), 'tel:') !== 0) {
            $url = $this->absUrl . $url;
        }

        return $url;
    }

    public function getResponseLink($url) {

        if ($this->template && $this->template instanceof BaseTemplate) {
            return $this->template->getResponseLink($url);
        }

        if(empty($url)) return $url;

        return $this->makeAbsUrl($url);
    }

    public function _begin()
    {
        if (\Yii::$app instanceof \yii\web\Application) {
            return parent::_begin();
        }
        return "";
    }

    public function _end()
    {
        if (\Yii::$app instanceof \yii\web\Application) {
            return parent::_end();
        }
        return "";
    }
}