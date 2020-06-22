<?php

namespace modules\shopandshow\controllers\mail;

use modules\shopandshow\models\mail\MailSubject;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use yii\base\InvalidConfigException;

/**
 * Class AdminMailSubjectController
 *
 * @package modules\shopandshow\controllers
 */
class AdminMailSubjectController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    /**
     *
     */
    public function init()
    {
        $this->modelClassName = MailSubject::className();
        $this->name = 'Темы рассылки';

        try{
            parent::init();
        } catch (InvalidConfigException $e){
            echo $e->getMessage();
            exit('Exit in file ' . __FILE__ . ' on line ' . __LINE__);
        }
    }

}
