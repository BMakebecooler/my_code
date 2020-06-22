<?php
namespace modules\shopandshow\models\shop\shopdiscount;


/**
 * This is the model class for table "shop_discount_entity".
 *
 * @property integer $id
 * @property string $name
 * @property string $class
 *
 * @property Configuration[] $configurations
 */
class Entity extends \yii\db\ActiveRecord
{
    const CLASS_DELIVERYSALE = 'DeliverySaleLogic';
    const LOOKBOOK_ENTITY = 'ForLookbook';
    const LOTS_ENTITY = 'ForLots';
    const SECTION_ENTITY = 'ForSection';
    const SUM_ENTITY = 'ForSum';
    const CTS_PLUS_LOTS_ENTITY = 'ForCtsPlusOne';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_shop_discount_entity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'class'], 'required'],
            [['name', 'class'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'class' => 'Class'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfigurations()
    {
        return $this->hasMany(Configuration::className(), ['shop_discount_entity_id' => 'id'])->indexBy('id')->inverseOf('entity');
    }
}
