<?php
/**
 * Created by PhpStorm.
 * User: soskov_da
 * Date: 07.07.2017
 * Time: 15:18
 */
namespace modules\shopandshow\models\shop\shopdiscount;

use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopProduct;
use yii\db\Exception;

class ConfigurationGroup
{
    /** @var Configuration[]  */
    private $configurations = [];
    private $class;

    public function __construct($class)
    {
        $fullClass = ConfigurationValue::getClassNameByEntityClass($class);
        if(!class_exists($fullClass)) throw new Exception('Class dont exists: '. $fullClass);

        $this->class = $fullClass;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function add(Configuration $configuration)
    {
        $this->configurations[$configuration->id] = $configuration;
    }

    /**
     * Создает группу конфигураций по категориям
     * @param Configuration[] $configurations
     *
     * @return ConfigurationGroup[]
     */
    public static function createFromArray(array $configurations)
    {
        $configurationGroups = [];
        foreach ($configurations as $configuration) {
            $class = $configuration->entity->class;
            if(!isset($configurationGroups[$class])) {
                $configurationGroups[$class] = new static($class);
            }
            $configurationGroups[$class]->add($configuration);
        }
        return $configurationGroups;
    }

    /**
     * Валидирует всю группу
     * @param ShopBasket $shopBasket
     * @param static[] $groups
     *
     * @return bool
     */
    public function validate(ShopBasket $shopBasket, array $groups = [])
    {
        // если хотя бы одна конфигурация в группе валидна, значит вся группа валидна
        foreach ($this->configurations as $configuration) {
            if (call_user_func([$this->class, 'validateCondition'], $configuration, $shopBasket)) {
                return true;
            }
        }

        // если в условиях акции несколько конфликтующих условий
        if (sizeof($groups) > 1) {
            return $this->validateMultiple($shopBasket, $groups);
        }

        return false;
    }

    /**
     * Проверяет, является ли данная группа условий проверкой наличия купона
     * @return bool
     */
    public function isCoupon() {
        return $this->class == ConfigurationValueForPromoCode::className();
    }

    /**
     * Валидирует всю группу с вложенными условиями
     * @param ShopBasket $shopBasket
     * @param static[] $groups
     *
     * @return bool
     */
    protected function validateMultiple(ShopBasket $shopBasket, array $groups)
    {
        /** @var static $group */
        foreach ($groups as $group) {
            // если проверка на лот провалилась, и есть условие на раздел - чекаем раздел
            if ($this->getClass() == ConfigurationValueForLots::className() && $group->getClass() == ConfigurationValueForSection::className()) {
                return $group->validate($shopBasket, $groups);
            }
        }
        return false;
    }
}