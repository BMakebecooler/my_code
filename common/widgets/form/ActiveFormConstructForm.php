<?php
namespace common\widgets\form;

use skeeks\cms\modules\admin\traits\AdminActiveFormTrait;
use skeeks\modules\cms\form2\models\Form2Form;

class ActiveFormConstructForm extends \skeeks\cms\base\widgets\ActiveForm
{
    use AdminActiveFormTrait;
    //use ActiveFormAjaxSubmitTrait;

    public $afterValidateCallback = "";
    public $ajaxProvider = "jquery";

    /**
     * @var Form2Form
     */
    public $modelForm;

    public function __construct($config = [])
    {
        $this->validationUrl                = \skeeks\cms\helpers\UrlHelper::construct('shopandshow/form/backend/validate')->toString();
        $this->action                       = \skeeks\cms\helpers\UrlHelper::construct('shopandshow/form/backend/submit')->toString();

        $this->enableAjaxValidation         = true;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        echo \yii\helpers\Html::hiddenInput("sx-model-value",   $this->modelForm->id);
        echo \yii\helpers\Html::hiddenInput("sx-model",         $this->modelForm->className());
    }

    public function registerJs()
    {
        $afterValidateCallback = $this->afterValidateCallback;
        if ($afterValidateCallback)
        {
            $this->view->registerJs(<<<JS
            
                    var ajaxProvider = '{$this->ajaxProvider}';

                    $('#{$this->id}').on('beforeSubmit', function (event, attribute, message) {
                        return false;
                    });

                    $('#{$this->id}').on('ajaxComplete', function (event, jqXHR, textStatus) {
                        if (jqXHR.status == 403)
                        {
                            sx.notify.error(jqXHR.responseJSON.message);
                        }
                    });

                    $('#{$this->id}').on('afterValidate', function (event, messages) {

                        if (event.result === false)
                        {
                            sx.notify.error('Проверьте заполненные поля в форме');
                            return false;
                        }

                        var Jform = $(this);
                        var blocker = new sx.classes.Blocker('#{$this->id}');
                        blocker.block();
                        
                        var callback = $afterValidateCallback;
                        
                        switch (ajaxProvider){
                            case 'jquery':
                                
                                var formData = new FormData(Jform[0]);
                                
                                var opts = {
                                            url: $(this).attr('action'),
                                            data: formData,
                                            cache: false,
                                            contentType: false,
                                            processData: false,
                                            method: 'POST',
                                            type: 'POST',
                                            dataType: 'json'
                                        };
        
                                //TODO: добавить проверки
                                //callback(Jform, opts);
                                
                                $.ajax(opts);
                                
                                break;
                            case 'skeeks':
                            default:
                                var ajax = sx.ajax.preparePostQuery($(this).attr('action'), $(this).serialize());

                                //TODO: добавить проверки
                                callback(Jform, ajax);
                                
                                ajax.execute();
                        }
                        
                        return false;
                    });

JS
            );


        } else
        {
            $this->view->registerJs(<<<JS

                    $('#{$this->id}').on('beforeSubmit', function (event, attribute, message) {
                        return false;
                    });




                    $('#{$this->id}').on('afterValidate', function (event, messages) {

                        if (event.result === false)
                        {
                            sx.notify.error('Проверьте заполненные поля в форме');
                            return false;
                        }

                        var Jform = $(this);
                        var blocker = new sx.classes.Blocker('#{$this->id}');
                        blocker.block();
                        //var ajax = sx.ajax.preparePostQuery($(this).attr('action'), $(this).serialize());
                        
                        var formData = new FormData(Jform[0]);
                        
                        var opts = {
                                    url: $(this).attr('action'),
                                    data: formData,
                                    cache: false,
                                    contentType: false,
                                    processData: false,
                                    method: 'POST',
                                    type: 'POST',
                                    dataType: 'json'
                                };

                        opts.complete = function(e, data) {
                            blocker.unblock();
                        }
                        //ajax.execute();

                        $.ajax(opts);

                        return false;
                    });

JS
            );
        }

    }

    public function run()
    {
        parent::run();
        $this->registerJs();
    }
}