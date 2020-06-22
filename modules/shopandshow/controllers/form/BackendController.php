<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */
namespace modules\shopandshow\controllers\form;

use modules\shopandshow\components\task\SendRrEmailTaskHandler;
use modules\shopandshow\models\task\SsTask;
use modules\shopandshow\models\users\UserEmail;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\FileHelper;
use skeeks\cms\relatedProperties\models\RelatedElementModel;
use skeeks\cms\relatedProperties\models\RelatedPropertiesModel;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use skeeks\modules\cms\form2\controllers\BackendController as SxBackendController;
use Yii;

/**
 * Class BackendController
 */
class BackendController extends SxBackendController
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * @param Form2Form $modelForm
     * @param RelatedPropertiesModel $validateModel
     * @param Form2FormSend $modelFormSend
     * @return bool
     */
    protected function _beforeSave(Form2Form $modelForm, RelatedPropertiesModel $validateModel, Form2FormSend $modelFormSend)
    {
        // доп. обработка для формы на конкурс
        if ($modelForm->code == 'konkurs2-form') {
            $uploadedFile = \yii\web\UploadedFile::getInstance($validateModel, 'photo');
            if (!$uploadedFile || !$uploadedFile->size) {
                return false;
            }

            try {
                // check image is valid image
                $image = getimagesize($uploadedFile->tempName);
                if (!$image || !$image[0] || !$image[1]) return false;
            } catch (\Exception $e) {
                return false;
            }

            $file = \Yii::$app->storage->upload($uploadedFile->tempName, [
                'name' => $uploadedFile->name
            ]);

            $validateModel->setAttribute('photo', (string)$file->id);
        }

        return true;
    }

    /**
     * @param Form2Form $modelForm
     * @param RelatedPropertiesModel $validateModel
     * @param Form2FormSend $modelFormSend
     */
    protected function _afterSave(Form2Form $modelForm, RelatedPropertiesModel $validateModel, Form2FormSend $modelFormSend)
    {
        //Для подписчиков на обычную рассылку ставим задание для отправки им приветственного письма
        //Исключение - подписка за купон 500р (проверяем по детальному источнику)
        $subscribersFrom = Form2Form::findOne(['code' => 'subscribers']);

        if ($subscribersFrom && $modelForm->id == $subscribersFrom->id && $email = $validateModel->getAttribute('email')){
            //TODO Что бы не отправлять письма по несколько раз - проверим наличие мыла в подписке

            $emailSourceDetail = Yii::$app->request->post('email_source_detail');

            if ($emailSourceDetail && $emailSourceDetail != UserEmail::SOURCE_DETAIL_PROMOCODE_DESKTOP){
                SsTask::createNewTask(
                    SendRrEmailTaskHandler::className(),
                    [
                        'email' => $email,
                        'template'  => 'welcome'
                    ]
                );
            }
        }

        UserEmail::addToBase($validateModel->getAttribute('email'),
            ['source' => Yii::$app->request->post('email_source'), 'source_detail' => Yii::$app->request->post('email_source_detail')]);
        return;
    }
}