<?php
namespace modules\shopandshow\models\newEntities\products;

use common\models\cmsContent\CmsContentElement;
use common\models\CmsContentProperty;
use common\models\ProductProperty;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use yii\helpers\Json;

class PropertiesCollectionList extends CmsContentElementModel
{
    public $properties = [];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'Глобальный идентификатор',
        ];
    }

    public function setCmsContentElement(CmsContentElement $contentElement)
    {
        $this->cmsContentElement = $contentElement;

        $this->setAttributes([
            'guid' => $contentElement->guid->getGuid(),
        ]);
    }

    public function setPropertyList(array $propertyList = [])
    {
        $this->properties = $propertyList;
    }

    /**
     *
     * @return bool
     */
    public function addData()
    {
        if (!$this->properties) {
            Job::dump(' empty properties list');
            return true;
        }

        $techDetailsProp = CmsContentProperty::findByCode('TECHNICAL_DETAILS');

        $elementProperty = ProductProperty::getElementProperty($this->cmsContentElement->id, $techDetailsProp->id);

        if (!$elementProperty){
            $elementProperty = new ProductProperty();
            $elementProperty->element_id = $this->cmsContentElement->id;
            $elementProperty->property_id = $techDetailsProp->id;
        }

        $elementProperty->value = Json::encode($this->properties);

        if (!$elementProperty->save()){
            \Yii::error("ProductID={$this->cmsContentElement->id}, error save element property. Error: " . var_export($elementProperty->getErrors(), true), 'modules\shopandshow\models\newEntities\products\PropertiesCollectionList\addData');
        }else{
            return true;
        }

        return false;
    }
}