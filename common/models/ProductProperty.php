<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.02.19
 * Time: 11:20
 */

namespace common\models;

//use skeeks\cms\models\CmsContentElementProperty;
//use common\models\cmsContent\CmsContentProperty;

use common\models\generated\models\CmsContentProperty;

class ProductProperty extends CmsContentElementProperty
{

    public static function getElementProperty($elementId, $propertyId)
    {
        return self::findOne(['element_id' => $elementId, 'property_id' => $propertyId]);
    }

    public static function getPropertyTypeByGuid($guid)
    {
        $type = false;
        switch ($guid) {
            case '62E18FAAAE9F1E5FE0538201090A587C' :
                $type = '';
                break; //Не показывать на сайте
            case '62E18FAAAE9E1E5FE0538201090A587C' :
                $type = '';
                break; //Не планируется поступление
            case '62E18FAAAE9D1E5FE0538201090A587C' :
                $type = '';
                break; //Показывать на главной
            case '62E18FAAAE9C1E5FE0538201090A587C' :
                $type = '';
                break; //РЕЙТИНГ : Количество голосов
            case '62E18FAAAE9B1E5FE0538201090A587C' :
                $type = '';
                break; //РЕЙТИНГ : Сумма голосов
            case '62E18FAAAE9A1E5FE0538201090A587C' :
                $type = '';
                break; //РЕЙТИНГ : Итоговый рейтинг
            case '62E18FAAAE991E5FE0538201090A587C' :
                $type = '';
                break; //Бесплатная доставка
            case '62E18FAAAE981E5FE0538201090A587C' :
                $type = '';
                break; //Дата съемки
            case '62E18FAAAE971E5FE0538201090A587C' :
                $type = '';
                break; //Не передавать текст в плашку
            case '62E18FAAAE961E5FE0538201090A587C' :
                $type = '';
                break; //Сегодня в эфире
            case '62E18FAAAE951E5FE0538201090A587C' :
                $type = '';
                break; //Продажа ювелирных изделий не поштучно
            case '62E18FAAAE941E5FE0538201090A587C' :
                $type = '';
                break; //Товар в каталоге
            case '62E18FAAAE931E5FE0538201090A587C' :
                $type = '';
                break; //Вес модификаций различается
            case '62E18FAAAE921E5FE0538201090A587C' :
                $type = '';
                break; //ПОИСК : Цена
            case '62E18FAAAE911E5FE0538201090A587C' :
                $type = '';
                break; //СОРТИРОВКА (Эфир)
            case '62E18FAAAE901E5FE0538201090A587C' :
                $type = '';
                break; //СОРТИРОВКА (Популярность)
            case '62E18FAAAE8F1E5FE0538201090A587C' :
                $type = '';
                break; //СОРТИРОВКА (Распродажа)
            case '62E18FAAAE8E1E5FE0538201090A587C' :
                $type = '';
                break; //Не выгружать в яндекс маркет
            case '62E18FAAAE8D1E5FE0538201090A587C' :
                $type = '';
                break; //Наличие товара
            case '62E18FAAAE8C1E5FE0538201090A587C' :
                $type = '';
                break; //Хит (популярность)
            case '72D67C21801BC11AE0538201090AB50A' :
                $type = '';
                break; //Применение
            case '72D67C218019C11AE0538201090AB50A' :
                $type = '';
                break; //Характеристики
            case '72D67C21801AC11AE0538201090AB50A' :
                $type = '';
                break; //Комплектация
            case '72D67C21801CC11AE0538201090AB50A' :
                $type = '';
                break; //Торговые преимущества
            case '72D67C21801DC11AE0538201090AB50A' :
                $type = '';
                break; //Рекомендации по уходу
            case '72D67C21801EC11AE0538201090AB50A' :
                $type = '';
                break; //Меры предосторожности
            case '72D67C21801FC11AE0538201090AB50A' :
                $type = '';
                break; //Состав (неформальное описание)
            case '72D67C218020C11AE0538201090AB50A' :
                $type = '';
                break; //Состояние лота (неформальное описание)
            case '72D67C218021C11AE0538201090AB50A' :
                $type = '';
                break; //Технические детали
            case '72D67C218022C11AE0538201090AB50A' :
                $type = '';
                break; //УТП
            case '72D67C218023C11AE0538201090AB50A' :
                $type = '';
                break; //Наличие в МСК (неформальное описание)
            case '72D67C218024C11AE0538201090AB50A' :
                $type = '';
                break; //Срок наличия в МСК (неформальное описание)
            case '72D67C218025C11AE0538201090AB50A' :
                $type = '';
                break; //Срок и условия гарантии
            case '72D67C218026C11AE0538201090AB50A' :
                $type = '';
                break; //Описание модификаций
            case '72D67C218027C11AE0538201090AB50A' :
                $type = '';
                break; //Модель (описание)
            case '72D67C218028C11AE0538201090AB50A' :
                $type = '';
                break; //Результаты тестирования
            case '72D67C21802AC11AE0538201090AB50A' :
                $type = '';
                break; //Дополнительный критерий выбора 1
            case '72D67C218029C11AE0538201090AB50A' :
                $type = '';
                break; //Дополнительный критерий выбора 2
            case '72D67C21802BC11AE0538201090AB50A' :
                $type = '';
                break; //Текст для плашек
            case '72D67C21802CC11AE0538201090AB50A' :
                $type = '';
                break; //Информация для Production
            case '72D67C21802DC11AE0538201090AB50A' :
                $type = '';
                break; //Условия поставщика (описание)
            case '72D67C21802EC11AE0538201090AB50A' :
                $type = '';
                break; //Наименование товара
            case '72D67C21802FC11AE0538201090AB50A' :
                $type = '';
                break; //Описание сроков и условий поставки
        }
        return $type;
    }

    public static function savePropByCode($elemId, $propCode, $value)
    {
        $propType = new CmsContentProperty();
        $propType = $propType->findOne(['code' => $propCode]);

        try {
            $prop = ProductProperty::getElementProperty($elemId, $propType->id);

            //Если нужно установить пустое значение - то только как удаление
            //Ну а если ни свойства нет ни значения - то и удалять ничего не надо
            if (empty($value)) {
                if ($prop){
                    $prop->delete();
                }
            }else{
                if (!$prop){
                    $prop = new ProductProperty();
                    $prop->element_id = $elemId;
                    $prop->property_id = $propType->id;
                }

                $prop->value = $value;
                $prop->save();
            }

            unset($prop);
            return true;
        } catch (\Exception $e) {
            \yii::error($e->getMessage(), 'model-properties');
        }
        unset($prop);
        return false;
    }

    /** Получаем свойства с непустыми значениями для элемента (элементов)
     *  Для списка элементов свойства групируются что бы получить все свойства которые встречаются в этих элементах
     *  Используется прежде всего в ModificationsWidget для исключения выборки заведомо пустых свойств модификаций
     *
     * @param $ids - массив идентификаторов сущностей, свойства которых необходимо обработать
     * @return array|generated\models\CmsContentElementProperty[]
     */
    public static function getNonEmptyGrouped($ids)
    {
        $props = self::find()
            ->select("
                prop.id, 
                prop.name, 
                prop.code, 
                prop.content_id,
                GROUP_CONCAT(props_vals.value) AS property_values"
            )
            ->alias('props_vals')
            ->leftJoin(CmsContentProperty::tableName() . " AS prop", "prop.id=props_vals.property_id")
            ->where([
                'props_vals.element_id' => (array)$ids
            ])
            ->andWhere(['not', ['props_vals.value' => null]])
            ->andWhere(['!=', 'props_vals.value', ''])
            ->groupBy('props_vals.property_id')
            ->asArray();

        return $props->all();
    }

//    public static function getPropertiesQuery($id, $props = false)

    /** Получение свойств по их коду для сущности
     *
     * @param $id - id сущности для которой ищем свойства
     * @param array|string $props - коды свойств которые необходимо найти
     * @return query\CmsContentElementPropertyQuery
     */
    public static function getByCodeQuery($id, $props = [])
    {
        $propsQuery = self::find()
            ->alias('propVals')
            ->select(['props.code', 'props.name prop_name', 'propVals.*'])
            ->andWhere([
                //'props.content_id' => Product::LOT,
                'propVals.element_id' => $id
            ])
            ->leftJoin(\common\models\CmsContentProperty::tableName() . ' AS props', "propVals.property_id=props.id");

        if ($props){
            $propsQuery->andWhere(['props.code' => (array)$props]);
        }

        return $propsQuery;
    }

    public static function getValueByCode($productId, $code)
    {
        return ($prop = self::getByCodeQuery($productId, $code)->one()) ? trim($prop->value) : '';
    }
}