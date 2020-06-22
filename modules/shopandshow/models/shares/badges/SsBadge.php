<?php

namespace modules\shopandshow\models\shares\badges;


use common\helpers\Strings;
use common\lists\Contents;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use modules\shopandshow\behaviors\files\HasStorageFile;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\StorageFile;
use yii\behaviors\TimestampBehavior;
use yii\db\AfterSaveEvent;

/**
 * This is the model class for table "ss_badges".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $begin_datetime
 * @property integer $end_datetime
 * @property integer $image_id
 * @property integer $image_id_product_card
 * @property string $name
 * @property string $code
 * @property string $active
 * @property string $url
 * @property string $description
 *
 * @property SsBadgeProduct[] $relatedProducts
 *
 * @property SsBadgeProduct[] $badgeProducts
 * @property CmsContentElement $product
 * @property StorageFile $image
 */
class SsBadge extends \yii\db\ActiveRecord
{
    public $relatedProducts;
    public $updateFile;
    public $updateProducts = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_badges';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'relatedProducts', 'updateProducts', 'updateFile'], 'safe'],
            [['begin_datetime', 'end_datetime'], 'required'],
            [['begin_datetime', 'end_datetime', 'image_id', 'image_id_product_card' ], 'integer'],
            [['name', 'code', 'active'], 'string', 'max' => 256],
            [['description'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 1056]
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
                    'fields' => ['image_id'],
                    'isUpdatedFile' => true,
                ],
            HasStorageFile::className() =>
                [
                    'class' => HasStorageFile::className(),
                    'fields' => ['image_id_product_card'],
                    'isUpdatedFile' => true,
                ],
            TimestampBehavior::className() =>
                [
                    'class' => TimestampBehavior::className(),
                ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создан',
            'updated_at' => 'Изменен',
            'begin_datetime' => 'Дата начала',
            'end_datetime' => 'Дата окончания',
            'image_id' => 'Изображение в каталоге',
            'image_id_product_card' => 'Изображение в карточке товара',
            'name' => 'Название',
            'description' => 'Описание',
            'code' => 'Код акции',
            'url' => 'Ссылка',
            'active' => 'Активность',
            'relatedProducts' => 'Товары в акции',
            'updateFile' => 'Импорт из файла .csv',
        ];
    }

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_UPDATE, [$this, '_afterUpdate']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, '_afterUpdate']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImageProductCard()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_id_product_card']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBadgeProducts()
    {
        return $this->hasMany(SsBadgeProduct::className(), ['badge_id' => 'id']);
    }

    /**
     * @return int|string
     */
    public function getProductsCount()
    {
        return $this->hasMany(SsBadgeProduct::className(), ['badge_id' => 'id'])->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CmsContentElement::className(), ['bitrix_id' => 'bitrix_product_id'])
            ->andWhere(['content_id' => [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]]);
    }

    /**
     * Сохраняет список продуктов через веб форму
     * @param AfterSaveEvent $event
     */
    public function _afterUpdate(AfterSaveEvent $event)
    {
        // если метод вызывался в форме, а не в каком-нибудь консольном контроллере
        if ($this->updateProducts) {
            $this->updateBadgeProducts();
        }

    }

    /**
     * Обновляет список связанных продуктов
     */
    protected function updateBadgeProducts()
    {
        // загрузка из файла
        $this->updateFile = \yii\web\UploadedFile::getInstance($this, 'updateFile');
        if ($this->updateFile) {
            //Удаление свойств "Текстовая плашка"
            $this->clearProductsBadgeText();
            echo '<div class="alert alert-info">' . $this->importFromFile() . '</div>';
            return;
        }

        // загрузка из формы
        $currentProducts = $this->getBadgeProducts()->indexBy('product_id')->asArray()->all();
        if (!$this->relatedProducts) $this->relatedProducts = [];
        $this->relatedProducts = array_combine($this->relatedProducts, $this->relatedProducts);

        $addProductsList = array_diff_key($this->relatedProducts, $currentProducts);
        $delProductsList = array_diff_key($currentProducts, $this->relatedProducts);

        if ($delProductsList) {
            if (!SsBadgeProduct::deleteAll(['badge_id' => $this->id, 'product_id' => array_keys($delProductsList)])) {
                echo 'Не удалось удалить выбранные товары из акции';
            };
        }

        foreach ($addProductsList as $product_id) {
            $ssBadgeProduct = new SsBadgeProduct();
            $ssBadgeProduct->badge_id = $this->id;
            $ssBadgeProduct->product_id = $product_id;
            $ssBadgeProduct->bitrix_id = Contents::getContentElementById($product_id)->bitrix_id;

            if (!$ssBadgeProduct->save()) {
                var_dump($ssBadgeProduct->getErrors());
            }
        }
    }

    /**
     * Загружает связанные товары из загруженного файла
     */
    protected function importFromFile()
    {
        $data = @file($this->updateFile->tempName);
        if (empty($data)) {
            $this->addError('updateFile', 'Не удалось распознать файл');
            return $this->getFirstError('updateFile');
        }

        // данные из файла
        $result = [];
        foreach ($data as $row) {
            if (empty($row)) {
                continue;
            }

            $badgeTextTop = '';
            $badgeTextBottom = '';
            $items = preg_split('/[;,\t]/', $row);

            //Вторым/третьим знаением может быть передан текстовый бейдж
            if (count($items) > 1) {
                list($lot, $badgeTextTop, $badgeTextBottom) = $items;
            } else {
                $lot = trim($items[0]);
            }

            $bitrixId = (int)Strings::onlyInt($lot);
            $result[$bitrixId] = [
                'badge_text_top' => trim($badgeTextTop),
                'badge_text_bottom' => trim($badgeTextBottom),
            ];
        }

        // соответствие bitrix_id -> id
        $cmsContentElementIdmap = \common\lists\Contents::getIdsByBitrixIds(array_keys($result));
        if (sizeof($cmsContentElementIdmap) != sizeof($result)) {
            $this->addError('updateFile', 'Не найдены лоты: ' . join(', ', array_diff(array_keys($result), array_keys($cmsContentElementIdmap))));
            return $this->getFirstError('updateFile');
        }

        if (($res = SsBadgeProduct::deleteAll(['badge_id' => $this->id])) === false) {
            $this->addError('updateFile', 'Не удалось удалить старые данные');
            return $this->getFirstError('updateFile');
        }

        //Выясним ID свойства текстовой плашки т.к. пригодится ниже
        $badgeTextProps = $this->getBadgeTextProps();

        $badgeTextTopPropId = $badgeTextProps['badge_text_top'] ?: 0;
        $badgeTextBottomPropId = $badgeTextProps['badge_text_bottom'] ?: 0;

        foreach ($result as $bitrixId => $badgeTexts) {
            $badgeTextTop = $badgeTexts['badge_text_top'];
            $badgeTextBottom = $badgeTexts['badge_text_bottom'];

            $product = Contents::getContentElementByBitrixId($bitrixId, [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);

            //Если указан текстовый бэйдж - запишем его значение.
            //Верх
            if ($badgeTextTop && $badgeTextTopPropId){
                $productBadgeText = CmsContentElementProperty::findOne(['element_id' => $product->id, 'property_id' => $badgeTextTopPropId]);

                if (!$productBadgeText){
                    $productBadgeText = new CmsContentElementProperty();
                    $productBadgeText->element_id = $product->id;
                    $productBadgeText->property_id = $badgeTextTopPropId;
                }

                $productBadgeText->value = $badgeTextTop;
                $productBadgeText->save();
            }

            //Низ
            if ($badgeTextBottom && $badgeTextBottomPropId){
                $productBadgeText = CmsContentElementProperty::findOne(['element_id' => $product->id, 'property_id' => $badgeTextBottomPropId]);

                if (!$productBadgeText){
                    $productBadgeText = new CmsContentElementProperty();
                    $productBadgeText->element_id = $product->id;
                    $productBadgeText->property_id = $badgeTextBottomPropId;
                }

                $productBadgeText->value = $badgeTextBottom;
                $productBadgeText->save();
            }

            $ssBadgeProduct = new SsBadgeProduct();
            $ssBadgeProduct->badge_id = $this->id;
            $ssBadgeProduct->product_id = $product->id;
            $ssBadgeProduct->bitrix_id = $bitrixId;

            if (!$ssBadgeProduct->save()) {
                var_dump($ssBadgeProduct->getErrors());
            }
        }

        return 'Данные из файла загружены';
    }

    /**
     * Получение свойств Текст плашки (верх и низ)
     * @return array
     */
    public function getBadgeTextProps(){
        return CmsContentProperty::find()
            ->where([
                'content_id' => PRODUCT_CONTENT_ID,
                'code' => ['badge_text_top', 'badge_text_bottom']
            ])
            ->asArray()
            ->indexBy('code')
            ->column();
    }

    /**
     * Очистка свойства "Текста плашки" (верх и низ) для связанных товаров
     * @return int
     */
    public function clearProductsBadgeText(){

        return CmsContentElementProperty::updateAll(
            ['value' => ''],
            [
                'property_id' => $this->getBadgeTextProps(),
                'element_id' => $this->getBadgeProducts()->select(['product_id'])->asArray()->column()
            ]
        );
    }
}
