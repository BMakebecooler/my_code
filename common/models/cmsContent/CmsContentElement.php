<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 23.03.17
 * Time: 14:05
 */

namespace common\models\cmsContent;

use common\helpers\ArrayHelper;
use modules\shopandshow\behaviors\SeoPageName;
use modules\shopandshow\models\behaviors\property\HasRelatedProperties;
use modules\shopandshow\models\common\GuidBehavior;
use modules\shopandshow\models\common\StorageFile;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\shop\SsShopProductPrice;
use modules\shopandshow\models\traits\SsContentElement;
use skeeks\cms\components\Cms;

use skeeks\cms\models\CmsContentElement as SXCmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\shop\models\ShopProductPrice;


/**
 * @property SXCmsContentElement|CmsContentElement $product
 * @property int $bitrix_id
 * @property int $count_children
 * @property int $rest
 * @property SsShopProductPrice $price
 * @property string $is_base
 * @property string $kfss_id
 * @property int $count_images
 * @property ShopProduct $shopProduct
 *
 * @property CmsContentElement[] $relatedElements
 * @property StorageFile[] $imagesByCards
 * @property StorageFile $image
 * @property GuidBehavior $guid
 * @property CmsContentProperty[] $adminRelatedProperties
 * @property RelatedPropertiesModel[] $relatedAdminPropertiesModel
 *
 * @property string $new_guid;
 * @property string $new_lot_num;
 * @property string $new_lot_name;
 * @property string $new_characteristics;
 * @property string $new_technical_details;
 * @property string $new_product_kit;
 * @property string $new_advantages;
 * @property string $new_advantages_addons;
 * @property string $new_not_public;
 * @property int $new_quantity;
 * @property int $new_rest;
 * @property int $new_price_active;
 * @property int $new_price;
 * @property int $new_price_old;
 * @property int $new_discount_percent;
 * @property int $new_brand_id;
 * @property int $new_season_id;
 * @property float $new_rating;
 *
 * @method getGuid
 *
 */
class CmsContentElement extends SXCmsContentElement
{
    //Маппинг свойств из обмена в свойства таблицы
    //guid свойства -> название колонки в таблице
    public static $propsMappingSrc = [
        //Прочие свойства

        //Типы медиа контента
//        ['guid' => '62968471C9770BAFE0538201090ACE60', 'column' => '', 'Фото'],
//        ['guid' => '62968471C9780BAFE0538201090ACE60', 'column' => '', 'Сертификаты'],
//        ['guid' => '62968471C9790BAFE0538201090ACE60', 'column' => '', 'Предметное фото'],
        ['guid' => '62968471C97A0BAFE0538201090ACE60', 'column' => '', 'Главное фото'],
//        ['guid' => '62968471C97B0BAFE0538201090ACE60', 'column' => '', 'Баннер - ЦТС (большой)'],
//        ['guid' => '62968471C97C0BAFE0538201090ACE60', 'column' => '', 'Баннер - Промо (большой)'],
//        ['guid' => '62968471C97D0BAFE0538201090ACE60', 'column' => '', 'Баннер - Промо (кубики)'],
        ['guid' => '62968471C97E0BAFE0538201090ACE60', 'column' => '', 'Видео (Youtube)'],
        ['guid' => '62968471C97F0BAFE0538201090ACE60', 'column' => '', 'Видео (Youtube) ЦП'],
        ['guid' => '62968471C9800BAFE0538201090ACE60', 'column' => '', 'Видео (Youtube) ЦТС'],
        ['guid' => '62968471C9810BAFE0538201090ACE60', 'column' => '', 'Видео (Youtube) ШШ'],
        ['guid' => '62968471C9820BAFE0538201090ACE60', 'column' => '', 'Видео (Youtube) Цена "Распродажа"'],
//        ['guid' => '62968471C9830BAFE0538201090ACE60', 'column' => '', 'Детальное фото'],
//        ['guid' => '8359007BBDA6902FE0538201090A70FE', 'column' => '', 'Техническое фото'],

        //Типы свойств по лотам и модификациям
        ['guid' => '62E18FAAAE9F1E5FE0538201090A587C', 'column' => 'new_not_public', 'Не показывать на сайте'],
//        ['guid' => '62E18FAAAE9E1E5FE0538201090A587C', 'column' => '', 'Не планируется поступление'],
//        ['guid' => '62E18FAAAE9D1E5FE0538201090A587C', 'column' => '', 'Показывать на главной'],
//        ['guid' => '62E18FAAAE9C1E5FE0538201090A587C', 'column' => '', 'РЕЙТИНГ : Количество голосов'],
//        ['guid' => '62E18FAAAE9B1E5FE0538201090A587C', 'column' => '', 'РЕЙТИНГ : Сумма голосов'],
//        ['guid' => '62E18FAAAE9A1E5FE0538201090A587C', 'column' => '', 'РЕЙТИНГ : Итоговый рейтинг'],
//        ['guid' => '62E18FAAAE991E5FE0538201090A587C', 'column' => '', 'Бесплатная доставка'],
//        ['guid' => '62E18FAAAE981E5FE0538201090A587C', 'column' => '', 'Дата съемки'],
//        ['guid' => '62E18FAAAE971E5FE0538201090A587C', 'column' => '', 'Не передавать текст в плашку'],
        ['guid' => '62E18FAAAE961E5FE0538201090A587C', 'column' => '', 'Сегодня в эфире'],
//        ['guid' => '62E18FAAAE951E5FE0538201090A587C', 'column' => '', 'Продажа ювелирных изделий не поштучно'],
//        ['guid' => '62E18FAAAE941E5FE0538201090A587C', 'column' => '', 'Товар в каталоге'],
//        ['guid' => '62E18FAAAE931E5FE0538201090A587C', 'column' => '', 'Вес модификаций различается'],
//        ['guid' => '62E18FAAAE921E5FE0538201090A587C', 'column' => '', 'ПОИСК : Цена'],
        ['guid' => '62E18FAAAE911E5FE0538201090A587C', 'column' => '', 'СОРТИРОВКА (Эфир)'],
        ['guid' => '62E18FAAAE901E5FE0538201090A587C', 'column' => '', 'СОРТИРОВКА (Популярность)'],
        ['guid' => '62E18FAAAE8F1E5FE0538201090A587C', 'column' => '', 'СОРТИРОВКА (Распродажа)'],
//        ['guid' => '62E18FAAAE8E1E5FE0538201090A587C', 'column' => '', 'Не выгружать в яндекс маркет'],
        ['guid' => '62E18FAAAE8D1E5FE0538201090A587C', 'column' => '', 'Наличие товара'],
//        ['guid' => '62E18FAAAE8C1E5FE0538201090A587C', 'column' => '', 'Хит (популярность)'],

        //Типы описательных свойств по лотам и модификациям
        ['guid' => '72D67C21801BC11AE0538201090AB50A', 'column' => '', 'Применение'],
        ['guid' => '72D67C218019C11AE0538201090AB50A', 'column' => 'new_characteristics', 'Характеристики'],
        ['guid' => '72D67C21801AC11AE0538201090AB50A', 'column' => 'new_product_kit', 'Комплектация'],
        ['guid' => '72D67C21801CC11AE0538201090AB50A', 'column' => '', 'Торговые преимущества'],
//        ['guid' => '72D67C21801DC11AE0538201090AB50A', 'column' => '', 'Рекомендации по уходу'],
//        ['guid' => '72D67C21801EC11AE0538201090AB50A', 'column' => '', 'Меры предосторожности'],
//        ['guid' => '72D67C21801FC11AE0538201090AB50A', 'column' => '', 'Состав (неформальное описание)'],
//        ['guid' => '72D67C218020C11AE0538201090AB50A', 'column' => '', 'Состояние лота (неформальное описание)'],
        ['guid' => '72D67C218021C11AE0538201090AB50A', 'column' => 'new_technical_details', 'Технические детали'],
//        ['guid' => '72D67C218022C11AE0538201090AB50A', 'column' => '', 'УТП'],
//        ['guid' => '72D67C218023C11AE0538201090AB50A', 'column' => '', 'Наличие в МСК (неформальное описание)'],
//        ['guid' => '72D67C218024C11AE0538201090AB50A', 'column' => '', 'Срок наличия в МСК (неформальное описание)'],
//        ['guid' => '72D67C218025C11AE0538201090AB50A', 'column' => '', 'Срок и условия гарантии'],
//        ['guid' => '72D67C218026C11AE0538201090AB50A', 'column' => '', 'Описание модификаций'],
//        ['guid' => '72D67C218027C11AE0538201090AB50A', 'column' => '', 'Модель (описание)'],
//        ['guid' => '72D67C218028C11AE0538201090AB50A', 'column' => '', 'Результаты тестирования'],
//        ['guid' => '72D67C21802AC11AE0538201090AB50A', 'column' => '', 'Дополнительный критерий выбора 1'],
//        ['guid' => '72D67C218029C11AE0538201090AB50A', 'column' => '', 'Дополнительный критерий выбора 2'],
//        ['guid' => '72D67C21802BC11AE0538201090AB50A', 'column' => '', 'Текст для плашек'],
//        ['guid' => '72D67C21802CC11AE0538201090AB50A', 'column' => '', 'Информация для Production'],
//        ['guid' => '72D67C21802DC11AE0538201090AB50A', 'column' => '', 'Условия поставщика (описание)'],
        ['guid' => '72D67C21802EC11AE0538201090AB50A', 'column' => '', 'Наименование товара'],
//        ['guid' => '72D67C21802FC11AE0538201090AB50A', 'column' => '', 'Описание сроков и условий поставки'],
    ];

    // АБ тест для старых и молодых.
    //const IMAGE_AGE_STARUHI_ATTR = 'image_id';
    //const IMAGE_AGE_MOLODIE_ATTR = 'image_full_id';

    //const IMAGE_AGE_STARUHI_CODE = 's';
    //const IMAGE_AGE_MOLODIE_CODE = 'm';

    use SsContentElement;

    public $noGuidAutoGenerate = true;

    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['bitrix_id', 'count_children', 'kfss_id', 'count_images'], 'integer'],
            [['is_base'], 'string', 'max' => 1],

            //Lot Props
//            [['new_guid'], 'string', 'max' => 32],
//            [['new_lot_num', 'new_lot_name', 'new_characteristics', 'new_technical_details', 'new_product_kit', 'new_advantages', 'new_advantages_addons'], 'string'],
//            [['new_not_public'], 'string', 'max' => 1],
//            [['new_quantity', 'new_rest', 'new_price_active', 'new_price', 'new_price_old', 'new_discount_percent', 'new_brand_id', 'new_season_id'], 'integer', 'min' => 0],
//            [['new_rating'], 'number', 'min' => 0],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {

        $parentsBehaviors = parent::behaviors();

        ArrayHelper::remove($parentsBehaviors, \skeeks\cms\models\behaviors\SeoPageName::className());

        return ArrayHelper::merge($parentsBehaviors, [
            GuidBehavior::className() => GuidBehavior::className(),
            SeoPageName::className() =>
                [
                    'class' => SeoPageName::className(),
                    'generatedAttribute' => 'code',
                    'maxLength' => \Yii::$app->cms->element_max_code_length,
                ],
            HasRelatedProperties::className() =>
                [
                    'class' => HasRelatedProperties::className(),
                    'relatedElementPropertyClassName' => CmsContentElementProperty::className(),
                    'relatedPropertyClassName' => CmsContentProperty::className(),
                ],
        ]);
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'is_base' => 'Признак фейковой модификации',
            'kfss_id' => 'Локальный ID в KFSS',
            'count_images' => 'Кол-во изображений',
        ]);
    }

    /**
     * @return $this|SXCmsContentElement
     */
    public function getProduct()
    {
        if ($this->isLot()) {
            return $this;
        } elseif ($this->isOffer()) {

            $parent = $this->parentContentElement;

            if ($parent) {
                $parent = $parent->parentContentElement;
            }

            return $parent;

        } elseif ($this->isCard()) {

            $parent = $this->parentContentElement;

            return $parent;
        }
    }

    /**
     * Получить ShopProduct для товара
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct(){
        return $this->hasOne(ShopProduct::className(), ['id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProductPrices()
    {
        return $this->hasMany(\common\models\ShopProductPrice::className(), ['product_id' => 'id']);
//        return $this->hasMany(ShopProductPrice::className(), ['product_id' => 'id']);
    }

    /**
     * Получить фотографии
     * @return StorageFile[]
     */
    public function getImagesByCards()
    {
        //Выбираем ВСЕ! Фото связанные с лотом и карточками, фильтровать будем на выводе
        if ($this->isLot()) {
            $sql = <<<SQL
                SELECT DISTINCT files.*,cce_image.content_element_id,cce_image.priority
                FROM cms_storage_file AS files 
                INNER JOIN (
                    SELECT cce.image_id AS storage_file_id, cce.id AS content_element_id, 0 AS priority
                      FROM cms_content_element AS cce
                      WHERE (cce.parent_content_element_id = :pce_id OR cce.id = :pce_id)
                        AND cce.image_id IS NOT NULL
                    UNION
                    SELECT storage_file_id, content_element_id, priority
                      FROM cms_content_element_image 
                      WHERE content_element_id IN (
                        SELECT cce.id
                          FROM cms_content_element AS cce
                          WHERE (cce.parent_content_element_id = :pce_id OR cce.id = :pce_id)
                      )
                ) AS cce_image ON files.id = cce_image.storage_file_id
                GROUP BY cce_image.storage_file_id
                ORDER BY cce_image.priority
SQL;

            return StorageFile::getDb()->cache(function ($db) use ($sql) {
                return StorageFile::findBySql($sql, [
                    ':pce_id' => $this->id,
                ])->all();
            }, MIN_10);

        } elseif ($this->isCard()) {
            $sql = <<<SQL
            SELECT DISTINCT files.*, cce_image.content_element_id,cce_image.priority
            FROM cms_storage_file AS files 
            INNER JOIN (
                SELECT image_id AS storage_file_id, id AS content_element_id, 0 AS priority
                  FROM cms_content_element
                  WHERE id = :cce_id 
                  AND image_id IS NOT NULL
                UNION    
                SELECT storage_file_id, content_element_id, priority
                  FROM cms_content_element_image 
                  WHERE content_element_id  = :cce_id
            ) AS cce_image ON files.id = cce_image.storage_file_id
            ORDER BY cce_image.priority
SQL;

            return StorageFile::getDb()->cache(function ($db) use ($sql) {
                return StorageFile::findBySql($sql, [
                    ':cce_id' => $this->id,
                ])->all();
            }, MIN_10);
        }

        return [];
    }

    /**
     * Получение базовой модификации для лота
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getBaseModification()
    {
        if ($this->content_id == PRODUCT_CONTENT_ID) {
            $card = CmsContentElement::find()
                ->select('id')
                ->where([
                    'content_id' => CARD_CONTENT_ID,
                    'parent_content_element_id' => $this->id,
                    'active' => Cms::BOOL_Y
                ])
                ->one();

            $offer = CmsContentElement::find()
                ->where([
                    'content_id' => OFFERS_CONTENT_ID,
                    'parent_content_element_id' => $card,
                    'is_base' => Cms::BOOL_Y,
                    'active' => Cms::BOOL_Y
                ])
                ->one();

            return $offer;
        }

        return false;
    }

    /**
     * Все возможные свойства связанные с моделью
     * @return \yii\db\ActiveQuery
     */
    public function getAdminRelatedProperties()
    {
        return $this->hasMany(CmsContentProperty::className(), ['content_id' => 'id'])
            ->andWhere('is_admin_show =:is_admin_show', [':is_admin_show' => Cms::BOOL_Y])
            ->via('cmsContent')
            ->orderBy(['priority' => SORT_ASC]);
    }


    public function getCard()
    {
        if ($this->parent_content_element_id) {
            return $this->parentContentElement;
        }

        return $this;
    }

    public function isSale(){
        return $this->isAvailable() && $this->isShowProduct();
    }

    public function isAvailable()
    {
        return $this->new_quantity > 0;
    }


    public function isQuantityLow()
    {
        return ($this->new_quantity <= 3);
    }

    public function isQuantityEnough()
    {
        return ($this->new_quantity > 3 && $this->new_quantity <= 15);
    }

    public function isQuantityMany()
    {
        return ($this->new_quantity > 15);
    }

}