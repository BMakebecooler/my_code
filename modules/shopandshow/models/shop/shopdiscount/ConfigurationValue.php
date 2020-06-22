<?php
namespace modules\shopandshow\models\shop\shopdiscount;

use modules\shopandshow\models\shop\ShopBasket;
use yii\db\Exception;

/**
 * This is the model class for table "shop_discount_values".
 *
 * @property integer $id
 * @property integer $shop_discount_configuration_id
 * @property string $value
 *
 * @property Configuration $configuration
 */
class ConfigurationValue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_shop_discount_values}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['shop_discount_configuration_id', 'integer'],
            ['shop_discount_configuration_id', 'exist', 'skipOnError' => true, 'targetClass' => Configuration::className(), 'targetAttribute' => ['shop_discount_configuration_id' => 'id']],
            ['value', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_discount_configuration_id' => 'ID конфигурации',
            'value' => 'Значение'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfiguration()
    {
        return $this->hasOne(Configuration::className(), ['id' => 'shop_discount_configuration_id']);
    }

    /**
     * @return string
     */
    public function getLinkedValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function formatOutput($value)
    {
        return $value;
    }

    /**
     * Возвращает полное имя класса для типа значений из БД
     * @param string $class
     *
     * @return string
     */
    public static function getClassNameByEntityClass($class)
    {
        return get_called_class().$class;
    }

    /**
     * Возвращает вьюху для рендера этого типа значений
     * @param string $class
     *
     * @return string
     */
    public static function getViewNameByEntityClass($class)
    {
        return 'entity_' . strtolower($class);
    }

    /**
     * Сохраняет в БД массив значений в виде новых записей для каждого значения
     * @param bool $runValidation
     * @param null $attributeNames
     *
     * @return bool
     * @throws Exception
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if(is_array($this->value) && sizeof($this->value)) {
            foreach ($this->value as $value) {
                $model = new static([
                    'shop_discount_configuration_id' => $this->shop_discount_configuration_id,
                    'value' => $value
                ]);
                if(!$model->save()) throw new Exception('Не удалось сохранить значение конфигурации');
            }
            return true;
        }
        if(empty($this->value)) throw new Exception('Не указано значение конфигурации ' . get_called_class());
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * Проверяет заданное значение в дочерней реализации <instanceof ConfigurationValue> в условии <Configuration> для элемента корзины  <ShopBasket>
     * @param Configuration $configuration
     * @param ShopBasket    $shopBasket
     *
     * @throws Exception
     */
    public static function validateCondition(Configuration $configuration, ShopBasket $shopBasket)
    {
        throw new Exception('Cant call this method from parent object, use child realisations');
    }
}
