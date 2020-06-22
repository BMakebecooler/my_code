<?php

namespace modules\shopandshow\controllers\stock;

use modules\shopandshow\models\shop\stock\SegmentFile;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
/**
 * Class FilesController
 * @package modules\shopandshow\controllers
 */
class FilesController extends AdminModelEditorController
{

    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = SegmentFile::className();
        $this->name = 'Сток';
        $this->modelShowAttribute = "id";

        parent::init();
    }


    public function actionUpload()
    {
        return $this->render('upload');
    }

}
