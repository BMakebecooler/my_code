<?php
namespace modules\shopandshow\widgets;

use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\StorageFile;
use yii\base\Exception;
use yii\bootstrap\Alert;
use yii\helpers\Json;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\widgets\formInputs\StorageImage as SxStorageImage;

/**
 * @property CmsStorageFile $image
 * Class StorageImage
 */
class StorageImage extends SxStorageImage
{
    private $modelClassName;

    public function init()
    {
        $reflect = new \ReflectionClass($this->model);
        $this->modelClassName = $reflect->getShortName();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        try
        {
            if (!$this->hasModel())
            {
                throw new Exception(\Yii::t('skeeks/cms',"Current widget works only in form with model"));
            }

            echo $this->render('storage-image', [
                'model'         => $this->model,
                'widget'        => $this,
            ]);

        } catch (\Exception $e)
        {
            echo Alert::widget([
                'options' => [
                    'class' => 'alert-warning',
                ],
                'body' => $e->getMessage()
            ]);
        }
    }

    /**
     * @return null|StorageFile
     */
    public function getImage()
    {
        $imageId = $this->model->{$this->attribute};
        if (!$imageId)
        {
            return null;
        }

        return StorageFile::findOne($imageId);
    }

    /**
     * @return string
     */
    public function getJsonString()
    {
        return Json::encode([
            'backendUrl'        => UrlHelper::construct('shopandshow/admin-storage-files/link-to-model')->enableAdmin()->toString(),
            'modelId'           => $this->model->id,
            'modelClassName'    => $this->model->className(),
            'modelAttribute'    => $this->attribute,
        ]);
    }

    public function getModelFieldId()
    {
        return $this->modelClassName.'_'.$this->attribute;
    }

    public function getModelFieldName()
    {
        return sprintf('%s[%s]', $this->modelClassName, $this->attribute);
    }

}
