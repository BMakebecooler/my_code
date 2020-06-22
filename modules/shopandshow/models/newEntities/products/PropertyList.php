<?php
namespace modules\shopandshow\models\newEntities\products;

use common\helpers\Filter;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\models\ProductProperty;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use yii\db\Exception;

class PropertyList extends CmsContentElementModel
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

        foreach ($this->properties as $property) {
            Job::dump('---');
            Job::dump('PropGuid: '.$property['PropGuid']);
            Job::dump('ItemGuid: '.$property['ItemGuid']);
            Job::dump('PropValue: '.$property['PropValue']);

            /** @var $content \common\models\cmsContent\CmsContent */
            if ($content = Guids::getEntityByGuid($property['PropGuid'])){
                //Получили свойство по коду
                if ($cmsContentProperty = \common\models\CmsContentProperty::findOne(['code' => $content->code])){
                    Job::dump("PropInfo: {$cmsContentProperty->name} [{$cmsContentProperty->code}]");

                    //Проверяем наличие свойства у товара
                    //Если свойство уже есть - 1) Если пришло не пустое значения - обновляем; 2) Если прило пустое - удаляем строку
                    //Если свойства нет - добавляем только если значение не пустое
                    $productProperty = ProductProperty::getElementProperty($this->cmsContentElement->id, $cmsContentProperty->id);

                    if (!$productProperty){
                        $productProperty = new ProductProperty();
                        $productProperty->element_id = $this->cmsContentElement->id;
                        $productProperty->property_id = $cmsContentProperty->id;

                        Job::dump(" > new prop for product");
                    }

                    //Значение для данного свойства
                    $propertyValue = $this->getValueByProp($property);

                    if ($propertyValue){ //Значение есть - сохраняем в любом случае
                        $productProperty->value = $propertyValue;

                        if (!$productProperty->save()){
                            Job::dump("> Error saving product prop. Msg = " . var_export($productProperty->getErrors(), true));
//                            Filter::changeParamProduct($productProperty,$cmsContentProperty,$this->cmsContentElement,'add');
                        }else{
                            Job::dump("Prop updated");
                        }
                    }else{
                        if ($productProperty->isNewRecord){ //Свойства нет, занчение пустое - пропускаем
                            Job::dump("Skip saving prop [Empty for new prop]");
                        }else{ //Свойство уже есть, значение пустое - удаляем
                            try{
//                                Filter::changeParamProduct($productProperty,$cmsContentProperty,$this->cmsContentElement,'delete');
                                $productProperty->delete();
                                Job::dump("Val empty - delete element prop");
                            }catch (Exception $e){
                                Job::dump("> Cant delete element prop. Msg: " . $e->getMessage());
                            }
                        }
                    }

                }else{
                    Job::dump("> Cant find prop by code '{$content->code}'");
                }
            }else{
                Job::dump("> Cant find content for GUID='{$property['PropGuid']}'");
            }
        }

        return true;
    }

    /**
     * @param array $property
     *
     * @return string
     */
    public function getValueByProp(array $property)
    {
        if($property['PropValue'] == 'N') {
            $value = '';
        } else {
            $value = $property['PropValue'];
        }

        if (!empty($property['ItemGuid'])) {
            /** @var $item \common\models\cmsContent\CmsContentElement */
            if (!$item = Guids::getEntityByGuid($property['ItemGuid'])) {
                Job::dump(' cant find item');
                return null;
            }
            $value = $item->id;
        }
        return (string)$value;
    }
}