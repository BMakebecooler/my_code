<?php

namespace modules\shopandshow\controllers\tools;

use modules\shopandshow\models\tools\Redirect;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorCreateAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * Class RedirectsController
 * @package modules\shopandshow\controllers\tools
 */
class RedirectsController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = Redirect::class;
        $this->name = 'Редиректы';
        $this->modelShowAttribute = 'id';

        parent::init();
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'create' =>
                    [
                        'class' => AdminModelEditorCreateAction::class,
                        'name' => \Yii::t('skeeks/cms', 'Add'),
                        "icon" => "glyphicon glyphicon-plus",
                        "callback" => [$this, 'actionCreate'],
                    ],
            ]
        );
    }

    public function actionCreate()
    {
        $model = new Redirect();

        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post())) {

            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->upload()) {
                if ($model->processFile()) {
                    return $this->redirect($this->indexUrl);
                }
            }
        }

        return $this->render('_form', ['model' => $model]);
    }
}
