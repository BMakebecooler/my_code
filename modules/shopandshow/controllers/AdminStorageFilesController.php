<?php
/**
 * AdminStorageFilesController
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 25.11.2014
 * @since 1.0.0
 */
namespace modules\shopandshow\controllers;

use Yii;
use skeeks\cms\controllers\AdminStorageFilesController as SxAdminStorageFilesController;

/**
 * Class AdminStorageFilesController
 * @package skeeks\cms\controllers
 */
class AdminStorageFilesController extends SxAdminStorageFilesController
{
    public $allowedExtensions = ['jpg','jpeg','png','gif'];

    public function actionUpload()
    {
        $response =
        [
            'success' => false
        ];

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $request = Yii::$app->getRequest();

        $dir = \skeeks\sx\Dir::runtimeTmp();

        $uploader = new \skeeks\widget\simpleajaxuploader\backend\FileUpload("imgfile");
        $file = $dir->newFile()->setExtension($uploader->getExtension());

        $originalName = $uploader->getFileName();

        $uploader->newFileName = $file->getBaseName();
        $result = $uploader->handleUpload($dir->getPath() . DIRECTORY_SEPARATOR, $this->allowedExtensions);

        if (!$result)
        {
            $response["msg"] = $uploader->getErrorMsg();
            return $response;

        }

        $storageFile = Yii::$app->storage->upload($file, array_merge(
            [
                "name" => "",
                "original_name" => $originalName
            ]
        ));


        if ($request->get('modelData') && is_array($request->get('modelData')))
        {
            $storageFile->setAttributes($request->get('modelData'));
        }

        $storageFile->save(false);

        $response["success"] = true;
        $response["file"] = $storageFile;

        return $response;
    }

    public function actionRemoteUpload()
    {
        $response =
            [
                'success' => false
            ];

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        $get = Yii::$app->getRequest();

        $request = Yii::$app->getRequest();

        if (\Yii::$app->request->post('link'))
        {
            $fileInfo = pathinfo(\Yii::$app->request->post('link'));
            if (!in_array($fileInfo['extension'], $this->allowedExtensions)) {
                $response["msg"] = 'Invalid file type';
                return $response;
            }

            $storageFile = Yii::$app->storage->upload(\Yii::$app->request->post('link'), array_merge(
                [
                    "name"          => "",
                    "original_name" => basename($post['link'])
                ]
            ));

            if ($request->post('modelData') && is_array($request->post('modelData')))
            {
                $storageFile->setAttributes($request->post('modelData'));
            }

            $storageFile->save(false);
            $response["success"]  = true;
            $response["file"]     = $storageFile;
            return $response;
        }

        return $response;
    }

}
