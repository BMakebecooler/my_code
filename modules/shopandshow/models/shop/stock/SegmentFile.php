<?php

namespace modules\shopandshow\models\shop\stock;

use modules\shopandshow\behaviors\files\HasStorageFile;
use skeeks\cms\models\Core;
use skeeks\cms\models\StorageFile;

/**
 * This is the model class for table "ss_segments_files".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $file_id
 * @property date $begin_datetime
 * @property date $end_datetime
 * @property string $name
 *
 * @property StorageFile $file
 */
class SegmentFile extends Core
{

//    public $name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_segments_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'file_id'], 'integer'],
            [['begin_datetime', 'end_datetime'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['file_id'], 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            HasStorageFile::className() =>
                [
                    'class' => HasStorageFile::className(),
                    'fields' => ['file_id']
                ],
        ]);
    }

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_UPDATE, [$this, '_processFile']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'file_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'created_by',
            'updated_by' => 'updated_by',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'file_id' => 'Файл ид',
            'begin_datetime' => 'Дата начала',
            'end_datetime' => 'Дата окончания',
            'name' => 'Назвние',
        ];
    }

    /**
     * Обработка файла
     * @return bool|int
     */
    public function _processFile()
    {
        if ($this->file_id && (
                $this->isAttributeChanged('begin_datetime') ||
                $this->isAttributeChanged('end_datetime')
            )
        ) {
            $updated = SsProductsSegments::updateAll([
                'begin_datetime' => $this->begin_datetime,
                'end_datetime' => $this->end_datetime,
            ], [
                'file_id' => $this->id
            ]);

            return $updated;
        }

        if (!$this->file_id || !$this->isAttributeChanged('file_id')) {
            return false;
        }

        SsProductsSegments::deleteAll([
            'file_id' => $this->id
        ]);

        $file = $this->file->getRootSrc();

        $products = [];

        if (!file_exists($file)) {
            return false;
        } else {
            $rows = file($file);

            foreach ($rows as $row) {

                if (empty($row)) {
                    continue;
                }

                $items = explode(',', $row);

                // нет второго итема => ошибка
                if (count($items) < 2) {
                    continue;
                }

                list($bitrixId, $segment) = $items;

                $bitrixId = trim($bitrixId);
                $segment = trim($segment);

                //Проверяем на возможные дубликаты обновления одних и тех же товаров
                if (!isset($products[$bitrixId])) {
                    $products[$bitrixId] = $segment;
                } else {
                    //Дубль
                }
            }
        }

        $productsInsertedNum = 0;

        if ($products) {
            $productsInsertedNum = $this->loadProductsSegmentation($products);
        }

        return $productsInsertedNum;
    }

    /**
     * Загрузка данных сегментации продуктов в БД
     * @param $products
     * @return int
     */
    private function loadProductsSegmentation($products)
    {
        ksort($products);

        $bitrixMap = \common\lists\Contents::getIdsByBitrixIds(array_keys($products));

        $counter = 0;

        $batchInsert = [];

        foreach ($products as $bitrixId => $segment) {
            $counter++;

            if (isset($bitrixMap[$bitrixId])) {
                $productId = $bitrixMap[$bitrixId];

                $product = [
                    'product_id' => $productId,
                    'bitrix_id' => $bitrixId,
                    'segment' => $segment,
                    'begin_datetime' => $this->begin_datetime,
                    'end_datetime' => $this->end_datetime,
                    'file_id' => $this->id,
                ];

                $batchInsert[] = $product;
            }
        }

        $productsInsertedNum = \Yii::$app->db->createCommand()
            ->batchInsert(SsProductsSegments::tableName(), [
                'product_id',
                'bitrix_id',
                'segment',
                'begin_datetime',
                'end_datetime',
                'file_id'
            ], $batchInsert)
            ->execute();

        return $productsInsertedNum;
    }
}
