<?php

namespace modules\shopandshow\models\tools;

use yii\web\UploadedFile;

/**
 * This is the model class for table "ss_redirects".
 *
 * @property integer $id
 * @property string $from
 * @property string $to
 */
class Redirect extends \yii\db\ActiveRecord
{

    /**
     * @var UploadedFile
     */
    public $file;

    private $filePath = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_redirects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'number'],
            [['from', 'to'], 'string'],
            [['file'], 'file', 'skipOnEmpty' => false], //, 'extensions' => 'csv'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'from' => 'Откуда',
            'to' => 'Куда',
            'file' => 'Список редиректов в формате csv',
        ];
    }

    /**
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            $this->filePath = sys_get_temp_dir() . '/' . $this->file->baseName . '.' . $this->file->extension;

            $this->file->saveAs($this->filePath);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool|int
     */
    public function processFile()
    {
        if (!file_exists($this->filePath)) {
            return false;
        }

        $rows = file($this->filePath);

        $result = [];

        foreach ($rows as $row) {

            if (empty($row)) {
                continue;
            }

            $items = explode(',', $row);

            if (count($items) < 2) {
                continue;
            }

            list($from, $to) = $items;

            $result[] = [
                'from' => trim($from),
                'to' => trim($to),
            ];
        }

        //Не будем затирать, просто добавим, удалить можно и выборочно
        //self::deleteAll();

        $redirectInsertedNum = \Yii::$app->db->createCommand()
            ->batchInsert(self::tableName(), [
                'from',
                'to'
            ], $result)
            ->execute();

        return $redirectInsertedNum;


    }
}