<?php

namespace modules\shopandshow\models\common;

use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\Product;
use common\models\Tree;
use common\models\user\User;
use modules\shopandshow\models\shop\ShopOrder;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\base\Component;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "contact_data_bitrix_users".
 *
 * @property integer $id
 * @property string $guid
 * @property integer $entity_type
 *
 * @property User $user
 */
class Guid extends \yii\db\ActiveRecord
{

    /**
     * Сущность пользователя
     */
    const ENTITY_TYPE_USER = 1;

    /**
     * Сущность заказа
     */
    const ENTITY_TYPE_ORDER = 2;


    /**
     * Сущность элемента контента
     */
    const ENTITY_TYPE_CMS_CONTENT_ELEMENT = 3;

    /**
     * Сущность контента
     */
    const ENTITY_TYPE_CMS_CONTENT = 4;

    /**
     * Сущность дерева
     */
    const ENTITY_TYPE_CMS_TREE = 5;

    /**
     * Сущность типа цен
     */
    const ENTITY_TYPE_PRICE_TYPE = 6;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_guids';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'entity_type'], 'integer'],
            [['guid'], 'string', 'max' => 64],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'guid' => 'guid',
            'entity_type' => 'entity_type',
        ];
    }

    /**
     *
     * @param Component $object
     * @param null $guid
     * @return bool
     */
    public static function saveGuid(Component $object, $guid = null)
    {

        if ($object->hasProperty(GuidBehavior::ATTRIBUTE_GUID_ID)) {

            //Если пришел явный ГУИД, надо не просто создать такой как указано, а предварительно проверить,
            //возможно такой уже есть и надо просто обновить связь объекта с ГУИДом

            $objectGuidIdOld = $object->{GuidBehavior::ATTRIBUTE_GUID_ID};

            //Если старый ГУИД есть - а в новом пришла пустота - пропускаем какие либо действия
            if ($objectGuidIdOld && !$guid){
                return false;
            }else{
                if ($guid){
                    $newGuid = self::findOne(['guid' => $guid]);
                }

                if (empty($newGuid)){
                    $newGuid = new self();
                    $newGuid->guid = $guid ?: $newGuid->generate();
                    $newGuid->entity_type = $newGuid->getEntityTypeByObject($object);
                }

                if ($newGuid->isNewRecord){

                    $guidSaved = false;

                    try {
                        $newGuid->save();
                        $guidSaved = true;
                    } catch (\Exception $e) {
                        if (\common\helpers\App::isConsoleApplication()){
                            echo "ERROR SAVE GUID: {$e->getMessage()}" . PHP_EOL;
                        }
                    }

                    if ($guidSaved) {
                        $object->{GuidBehavior::ATTRIBUTE_GUID_ID} = $newGuid->id;
                    } else {
//                var_dump($newGuid->getErrors());
                        $object->addError(GuidBehavior::ATTRIBUTE_GUID_ID, print_r($newGuid->getErrors(), true));

                        throw new \RuntimeException('failed to save guid ' . print_r($newGuid->getErrors(), true));
                    }
                }else{
                    $object->{GuidBehavior::ATTRIBUTE_GUID_ID} = $newGuid->id;
                }

                //Если не останавливать при равенстве то сваливается в вечную рекурсию
                if ($object->{GuidBehavior::ATTRIBUTE_GUID_ID} != $objectGuidIdOld) {
                    if (!$object->save(true, [GuidBehavior::ATTRIBUTE_GUID_ID])) {
                        //                var_dump($object->getErrors());

                        throw new \RuntimeException('failed to save guid_id attribute ' . print_r($object->getErrors(), true));
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Получить тип сущности по объекту
     * @param Component $object
     * @return int
     */
    protected function getEntityTypeByObject(Component $object)
    {
        if ($object instanceof ShopOrder || $object instanceof \common\models\ShopOrder) {
            return self::ENTITY_TYPE_ORDER;
        } elseif ($object instanceof User || $object instanceof \common\models\User) {
            return self::ENTITY_TYPE_USER;
        } elseif ($object instanceof CmsContentElement || $object instanceof Product) {
            return self::ENTITY_TYPE_CMS_CONTENT_ELEMENT;
        } elseif ($object instanceof CmsContent) {
            return self::ENTITY_TYPE_CMS_CONTENT;
        } elseif ($object instanceof Tree) {
            return self::ENTITY_TYPE_CMS_TREE;
        } elseif ($object instanceof ShopTypePrice || $object instanceof \common\models\ShopTypePrice) {
            return self::ENTITY_TYPE_PRICE_TYPE;
        }
    }

    /**
     * Получить тип сущности
     * @return Component|ActiveRecord|boolean
     */
    public function getEntity()
    {
        if ($this->entity_type === self::ENTITY_TYPE_USER) {
            return User::class;
        } elseif ($this->entity_type === self::ENTITY_TYPE_ORDER) {
            return ShopOrder::class;
        } elseif ($this->entity_type === self::ENTITY_TYPE_CMS_CONTENT_ELEMENT) {
            return CmsContentElement::class;
        } elseif ($this->entity_type === self::ENTITY_TYPE_CMS_CONTENT) {
            return CmsContent::class;
        } elseif ($this->entity_type === self::ENTITY_TYPE_CMS_TREE) {
            return Tree::class;
        } elseif ($this->entity_type === self::ENTITY_TYPE_PRICE_TYPE) {
            return ShopTypePrice::class;
        }

        return false;
    }


    protected function generate()
    {

        /** Добавляем счетчик чтобы не положить базу зацикленным селектом */
        $attempts = 0;

        do {

            ++$attempts;

            /** В описании функции сказано что РЕДКО, но может вернуться false */
            $data = openssl_random_pseudo_bytes(16);

            if (is_bool($data) && $data === false)
                continue;

            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        } while (
            $attempts < 3
        );

        /** Теперь у нас есть уникальный GUID */

        return strtoupper(str_replace('-', '', vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4))));
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

}
