<?php
namespace modules\shopandshow\models\newEntities\products;

use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\ProductProperty;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;

class MainPropList extends CmsContentElementModel
{
    public $BrandGuid;
    public $SeasonGuid;
    public $SizeGuid;
    public $ColorGuid;
    public $ColorAdditionGuid;
    public $Weight;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'Глобальный идентификатор',
            'BrandGuid' => 'Brand Guid',
            'SeasonGuid' => 'Season Guid',
            'SizeGuid' => 'Size Guid',
            'ColorGuid' => 'Color Guid',
            'ColorAdditionGuid' => 'Color Addition Guid', //aka bitrix color | card color
            'Weight' => 'Weight'
        ];
    }

    public function setCmsContentElement(CmsContentElement $contentElement)
    {
        $this->cmsContentElement = $contentElement;

        $this->setAttributes([
            'guid' => $contentElement->guid->getGuid(),
        ]);
    }

    /**
     * @return bool
     */
    public function addData()
    {

        if (!$this->setBrand()) {
        //    return false;
        }

        if (!$this->setSeason()) {
        //    return false;
        }

        if (!$this->setSize()) {
        //    return false;
        }

        if (!$this->setColor()) {
        //    return false;
        }

        if (!$this->setAdditionalColor()) {
        //    return false;
        }

        if (!$this->setWeight()) {
        //    return false;
        }

        return true;
        //return $this->saveRelatedProperties();
    }

    protected function setBrand()
    {
        if (!$this->BrandGuid) return true;

        /** @var $brand CmsContentElement */
        if (!$brand = Guids::getEntityByGuid($this->BrandGuid)) {
            Job::dump(' brand not found');
            return false;
        }

        ProductProperty::savePropByCode($this->cmsContentElement->id,\console\controllers\queues\jobs\dicts\Brand::CODE, (string)$brand->id );
        //$this->relatedPropertiesModel[\console\controllers\queues\jobs\dicts\Brand::CODE] = (string)$brand->id;

        return true;
    }

    protected function setSeason()
    {
        if (!$this->SeasonGuid) return true;

        /** @var $brand CmsContentElement */
        if (!$season = Guids::getEntityByGuid($this->SeasonGuid)) {
            Job::dump(' season not found');
            return false;
        }


        ProductProperty::savePropByCode($this->cmsContentElement->id,\console\controllers\queues\jobs\dicts\Season::CODE, (string)$season->id);
        //$this->relatedPropertiesModel[\console\controllers\queues\jobs\dicts\Season::CODE] = (string)$season->id;

        return true;
    }

    protected function setSize()
    {
        if (!$this->SizeGuid) return true;

        if (!$size = Guids::getEntityByGuid($this->SizeGuid)) {
            Job::dump(' size not found');
            return false;
        }

        // берем все размерные шкалы
        $sizeScales = CmsContent::getDb()->cache(function ($db) {
            return CmsContent::find()->where(['content_type' => self::CONTENT_TYPE_KFSS_INFO_SIZES])->indexBy('code')->all();
        }, MIN_30);

        // берем все текущие атрибуты
        $elementAttrs = $this->cmsContentElement->relatedPropertiesModel->getAttributes();
        foreach ($elementAttrs as $code => $value) {
            // если код атрибута входит в размерные шкалы - очищаем его
            if (array_key_exists($code, $sizeScales) && !empty($value)) {
                ProductProperty::savePropByCode($this->cmsContentElement->id, $code, '');
//                $this->relatedPropertiesModel[$code] = '';
                Job::dump("cleared {$code}");
            }
        }

        Job::dump('size: '.$size->id);

        $sizeScale = CmsContent::findOne($size->content_id);
        Job::dump('sizeScale: '.$sizeScale->code);

        // сохраняем новый код
        ProductProperty::savePropByCode($this->cmsContentElement->id, $sizeScale->code, (string)$size->id);
        //$this->relatedPropertiesModel[$sizeScale->code] = (string)$size->id;

        return true;
    }

    protected function setColor()
    {
        if (!$this->ColorGuid) return true;

        if (!$color = Guids::getEntityByGuid($this->ColorGuid)) {
            Job::dump(' color not found');
            return false;
        }
        Job::dump('color: '.$color->id);

        ProductProperty::savePropByCode($this->cmsContentElement->id,\console\controllers\queues\jobs\dicts\Color::CODE, (string)$color->id);
        //$this->relatedPropertiesModel[\console\controllers\queues\jobs\dicts\Color::CODE] = (string)$color->id;

        return true;
    }


    protected function setAdditionalColor()
    {
        if (!$this->ColorAdditionGuid) return true;

        if (!$color = Guids::getEntityByGuid($this->ColorAdditionGuid)) {
            Job::dump(' additional color not found');
            return false;
        }
        Job::dump('additional color: '.$color->id);

        ProductProperty::savePropByCode($this->cmsContentElement->id,\console\controllers\queues\jobs\dicts\Color::CODE_BX, (string)$color->id);
        //$this->relatedPropertiesModel[\console\controllers\queues\jobs\dicts\Color::CODE_BX] = (string)$color->id;

        return true;
    }

    protected function setWeight()
    {
        if (!$this->Weight) return true;

        ProductProperty::savePropByCode($this->cmsContentElement->id,'VES_PREDVARIT', (string)$this->Weight);
        //$this->relatedPropertiesModel['VES_PREDVARIT'] = (string)$this->Weight;

        return true;
    }
}