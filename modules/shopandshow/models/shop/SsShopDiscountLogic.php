<?php
namespace modules\shopandshow\models\shop;

/**
 * This is the model class for table "ss_shop_discount_logic".
 *
 * @property integer $id
 * @property integer $shop_discount_id
 * @property string $logic_type
 * @property string $value
 * @property string $discount_type
 * @property string $discount_value
 *
 * @property ShopDiscount $shopDiscount
 */
class SsShopDiscountLogic extends \yii\db\ActiveRecord
{

    const LOGIC_TYPE_FIXED = 'F';
    const LOGIC_TYPE_BASKET = 'B';
    const LOGIC_TYPE_QUANTITY = 'Q';

    const DISCOUNT_TYPE_FIXED = 'F';
    const DISCOUNT_TYPE_PERCENT = 'P';

    public $flag_delete;

    public static function getLogicTypes()
    {
        return [
            self::LOGIC_TYPE_BASKET => 'Стоимость корзины',
            self::LOGIC_TYPE_FIXED => 'Стоимость товара',
            self::LOGIC_TYPE_QUANTITY => 'Кол-во позиций товара',
        ];
    }

    public static function getDiscountTypes()
    {
        return [
            self::DISCOUNT_TYPE_PERCENT => 'В процентах',
            self::DISCOUNT_TYPE_FIXED => 'Фиксированная сумма',
        ];
    }

    public function initDefaultValues()
    {
        foreach ($this->rules() as $rule) {
            if($rule[1] == 'default') {
                if(!is_array($rule[0])) $rule[0] = [$rule[0]];
                foreach ($rule[0] as $attr) {
                    $this->{$attr} = $rule['value'];
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_shop_discount_logic}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_discount_id'], 'integer'],
            [['shop_discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopDiscount::className(), 'targetAttribute' => ['shop_discount_id' => 'id']],
            [['logic_type', 'discount_type'], 'string', 'max' => 1],
            [['value', 'discount_value'], 'number'],
            [['logic_type', 'discount_type', 'value', 'discount_value'], 'required'],
            [['logic_type'], 'default', 'value' => self::LOGIC_TYPE_BASKET],
            [['discount_type'], 'default', 'value' => self::DISCOUNT_TYPE_PERCENT],
            [['value', 'discount_value'], 'default', 'value' => 0],
            ['flag_delete', 'safe'],
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
            'logic_type' => 'Тип условия',
            'value' => 'Порог скидки',
            'discount_type' => 'Тип скидки',
            'discount_value' => 'Значение скидки'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscount()
    {
        return $this->hasOne(ShopDiscount::className(), ['id' => 'shop_discount_id']);
    }
}
