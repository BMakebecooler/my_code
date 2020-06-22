<?php

namespace modules\shopandshow\models\shop\shopdiscount;

use modules\shopandshow\models\shop\ShopDiscount;

/**
 * This is the model class for table "shop_discount_configuration".
 *
 * @property integer $id
 * @property integer $shop_discount_id
 * @property integer $shop_discount_entity_id
 * @property ConfigurationValue[] $values
 *
 * @property Entity $entity
 * @property ShopDiscount $shopDiscount
 */
class Configuration extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_shop_discount_configuration}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_discount_id', 'shop_discount_entity_id'], 'integer'],
            [['shop_discount_id'], 'exist', 'skipOnError' => true,
                'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['shop_discount_id' => 'id']],
            [['shop_discount_entity_id'], 'exist', 'skipOnError' => true,
                'targetClass' => Entity::className(), 'targetAttribute' => ['shop_discount_entity_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_id' => 'Shop Discount Id',
            'shop_discount_entity_id' => 'Тип условия',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntity()
    {
        return $this->hasOne(Entity::className(), ['id' => 'shop_discount_entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscount()
    {
        return $this->hasOne(ShopDiscount::className(), ['id' => 'shop_discount_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValues()
    {
        $configurationClassname = ConfigurationValue::getClassNameByEntityClass($this->entity->class);
        $relations = $this->hasMany($configurationClassname, ['shop_discount_configuration_id' => 'id'])->indexBy('id')->inverseOf('configuration');

        return $relations;
    }
}
