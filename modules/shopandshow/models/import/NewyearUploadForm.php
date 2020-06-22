<?php

namespace modules\shopandshow\models\import;

use skeeks\cms\models\CmsTreeProperty;
use yii\web\UploadedFile;

class NewyearUploadForm extends \yii\base\Model
{
    /**
     * @var UploadedFile file attribute
     */
    public $file;
    public $tree_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['tree_id'], 'integer'],
            [['tree_id'], 'required'],
            [['file'], 'file', 'skipOnEmpty' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'tree_id' => 'Категория меню',
            'file' => 'CSV файл',
        ];
    }

    /**
     * Отдает список новогодних разделов
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getTrees()
    {
        $newYearTreeItems = \common\models\Tree::find()->where('dir like "newyear/%"')->all();

        return $newYearTreeItems;
    }

    /**
     * Загружает связанные товары из загруженного файла
     */
    public function import()
    {
        $data = @file($this->file->tempName);
        if (empty($data)) {
            return 'Не удалось распознать файл';
        }

        // данные из файла
        $result = [];
        foreach ($data as $row) {
            if (empty($row)) {
                continue;
            }

            $items = preg_split('/[;,\t]/', $row);
            list($lot, $priority) = $items;

            if (!preg_match('/^[\d\-\s]+$/', $lot)) {
                return 'Некорректный номер лота: '.$lot;
            }
            $bitrixId = ltrim(str_replace(['[', ']', '-', ' '], '', $lot), '0');
            $result[$bitrixId] = trim($priority);
        }

        // соответствие bitrix_id -> id
        $cmsContentElementIdmap = \common\lists\Contents::getIdsByBitrixIds(array_keys($result));
        if (sizeof($cmsContentElementIdmap) != sizeof($result)) {
            return 'Не найдены лоты '.print_r(array_diff(array_keys($result), array_keys($cmsContentElementIdmap)), true);
        }

        $tree = \common\models\Tree::findOne($this->tree_id);
        if (!$tree) {
            return 'Раздел не найден по id: '.$this->tree_id;
        }

        $property = $tree->relatedPropertiesModel->getRelatedProperty('savedProducts');
        // чистим старые записи
        CmsTreeProperty::deleteAll(['property_id' => $property->id, 'element_id' => $tree->id]);

        foreach ($result as $bitrixId => $priority) {
            $enum = new CmsTreeProperty();
            $enum->element_id = $tree->id;
            $enum->property_id = $property->id;
            $enum->value = $cmsContentElementIdmap[$bitrixId];
            $enum->value_num = $priority;

            if (!$enum->save()) {
                return print_r($enum->getErrors(), true);
            }
        }

        return 'Данные загружены';
    }
}