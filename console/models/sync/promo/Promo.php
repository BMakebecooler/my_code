<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 16.08.2017
 * Time: 13:24
 */

namespace console\models\sync\promo;

use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\shopdiscount\Configuration;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValueForCount;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValueForBasketElementsQty;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValueForCtsPlusOne;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValueForLots;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValueForSection;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValueForSum;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationValueForUsers;
use modules\shopandshow\models\shop\shopdiscount\Entity;
use modules\shopandshow\models\shop\SsShopDiscountLogic;

/**
 * Class BoDiscount
 *
 * @package console\models\sync\promo
 */
class Promo extends \yii\base\Component
{
    /** bo fields */
    public $id;
    public $name;
    public $period = [];
    public $code;
    public $img;
    public $cancel;
    public $condition = [];
    public $logic = [];

    /** @var PromoManager */
    public $manager;

    /** @var Promo[] */
    private $_relatedPromo = [];
    /** @var ShopDiscount */
    private $_shopDiscount;

    /**
     * Создает новый или получает объект из БД
     *
     * @return ShopDiscount
     */
    public function getShopDiscount()
    {
        if ($this->_shopDiscount) {
            return $this->_shopDiscount;
        }

        $this->_shopDiscount = ShopDiscount::findOne(['bo_id' => $this->id]);
        if (empty($this->_shopDiscount)) {
            $this->_shopDiscount = new ShopDiscount();
            $this->_shopDiscount->site_id = \Yii::$app->cms->site->id;
            $this->_shopDiscount->currency_code = 'RUB';
            $this->_shopDiscount->max_discount = 0;
            $this->_shopDiscount->bo_id = $this->id;
        }
        
        $this->_shopDiscount->active = 'Y';
        $this->_shopDiscount->active_from = strtotime($this->period['from']);
        $this->_shopDiscount->active_to = strtotime($this->period['to']);
        $this->_shopDiscount->last_discount = $this->cancel ? 'Y' : 'N';
        $this->_shopDiscount->value_type = $this->getValueType();
        $this->_shopDiscount->value = $this->getValue();
        $this->_shopDiscount->name = $this->getName();
        $this->_shopDiscount->code = $this->getCode();
        if($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_GIFT) {
            $this->_shopDiscount->gift = $this->_shopDiscount->value;
        }

        return $this->_shopDiscount;
    }

    /**
     * определяет тип акции
     *
     * @return string
     * @throws \Exception
     */
    public function getValueType()
    {
        $logic = $this->logic[0]['class'];
        if ($logic == '\SandsPromo\GiftLogic') {
            return ShopDiscount::VALUE_TYPE_GIFT;
        }
        elseif ($logic == '\SandsPromo\DeliverySaleLogic') {
            return ShopDiscount::VALUE_TYPE_DELIVERY;
        }
        elseif ($logic == '\SandsPromo\SubscribeCouponLogic') {
            return ShopDiscount::VALUE_TYPE_F;
        } // Обычная скидка или лестница скидок
        elseif ($logic == '\SandsPromo\SaleLogic') {
            if (sizeof($this->_relatedPromo)) {
                return ShopDiscount::VALUE_TYPE_LADDER;
            }

            $type = $this->logic[0]['params']['type'];
            if ($type == 'P') {
                return ShopDiscount::VALUE_TYPE_P;
            }

            return ShopDiscount::VALUE_TYPE_F;
        }
        // Скидка на наименьшую сумму позиции корзины
        elseif ($logic == '\SandsPromo\SaleLogicMinPrice') {
            return ShopDiscount::VALUE_TYPE_BASKET_QTY;
        }
        else {
            throw new \Exception("Логика {$logic} не поддерживается");
        }
    }

    /**
     * Получает значение скидки
     *
     * @return int
     * @throws \yii\base\ErrorException
     */
    public function getValue()
    {
        $params = @$this->logic[0]['params'];
        if ($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_GIFT) {
            /*
            [params] => Array
                (
                    [forBasketQuantity] => 1 // checkbox
                    [giftsNormalGroup] => Array
                        (
                            [0] => 2333124
                        )
                    [0] => Array ...
                )
            */
            $giftId = null;
            // в списках giftsNormalGroup и giftsJewerlyGroup лежат разные модификации одного подарка
            // todo: в текущей реализации берем только одну модификацию, хотя по хорошему брать надо все, а в уже в клиентском коде дарить рандомный
            if (isset($params['giftsNormalGroup'])) {
                $giftId = end($params['giftsNormalGroup']);
            } elseif (isset($params['giftsJewerlyGroup'])) {
                $giftId = end($params['giftsJewerlyGroup']);
            }

            if(empty($giftId)) {
                // todo: где подарок? Что делать с такой акцией?
                $this->_shopDiscount->delete();
                throw new \yii\base\ErrorException('Не найден подарок для акции '.$this->id.': '.print_r($params, true));
            }

            $shopProduct = \common\lists\Contents::getContentElementByBitrixId($giftId, [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);
            if(empty($shopProduct)) {
                // todo: где подарок? Что делать с такой акцией?
                $this->_shopDiscount->delete();
                throw new \yii\base\ErrorException('Не найден shopProduct по bitrix_id '.$giftId);
            }

            return $shopProduct->id;

        }
        elseif ($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_DELIVERY) {
            return $params['sum'];
        }
        elseif ($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_LADDER) {
            return 0;
        }
        elseif ($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_BASKET_QTY) {
            return 0;
        }
        else {
            return $params['sale'];
        }
    }

    /**
     * Подходят ли атрибуты акции под категорию лестницы скидок
     *
     * @return bool
     */
    public function isLadder()
    {
        if ($this->logic[0]['class'] != '\SandsPromo\SaleLogic') {
            return false;
        }

        if ($this->haveCondition('\SandsPromo\ForSum') || $this->haveCondition('\SandsPromo\ForSumRange')) {
            return true;
        }

        return false;
    }

    /**
     * Проверяет наличие условия
     *
     * @param string $conditionName
     *
     * @return bool
     */
    public function haveCondition($conditionName)
    {
        foreach ($this->condition as $condition) {
            if ($condition['class'] == $conditionName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Получает значение условия вилки суммы для лестницы скидок
     *
     * @return bool
     */
    public function getForSumValue()
    {
        foreach ($this->condition as $condition) {
            if ($condition['class'] == '\SandsPromo\ForSumRange') {
                return $condition['params']['from'];
            }
        }

        return false;
    }

    /**
     * добавляет список связанных акций лестницы скидок
     *
     * @param Promo $promo
     */
    public function addRelatedPromo(Promo $promo)
    {
        $this->_relatedPromo[$promo->id] = $promo;
    }

    /**
     * Вычисляет название акции
     *
     * @return mixed|string
     */
    public function getName()
    {
        if ($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_LADDER) {
            return $this->buildStringFromRelated('name');
        }

        return $this->name;
    }

    /**
     * Вычисляет код акции
     *
     * @return mixed|string
     */
    public function getCode()
    {
        if ($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_LADDER) {
            return $this->buildStringFromRelated('code');
        }

        return $this->code;
    }

    /**
     * Формирует новую строку из диффа 2 строк
     *
     * @param $attr
     *
     * @return mixed|string
     */
    protected function buildStringFromRelated($attr)
    {
        $mainValue = $this->{$attr};
        $relatedValue = reset($this->_relatedPromo)->{$attr};

        $diff = new \GorHill\FineDiff\FineDiff($mainValue, $relatedValue, \GorHill\FineDiff\FineDiff::$wordGranularity);
        $result = $this->renderValueFromDiff($mainValue, $diff);

        // если по словам разбить не удалось (обычно такое бывает в code)
        if (empty($result)) {
            $diff = new \GorHill\FineDiff\FineDiff($mainValue, $relatedValue, \GorHill\FineDiff\FineDiff::$characterGranularity);
            $result = $this->renderValueFromDiff($mainValue, $diff);
        }

        // убираем в конце лишнюю фигню после диффа, остаточные пробелы, цифры, дефисы
        $result = preg_replace('/[0-9\s\-]+$/', '', $result);

        return $result;
    }

    /**
     * рендер текстового представления диффа
     *
     * @param $mainValue
     * @param $diff
     *
     * @return string
     */
    protected function renderValueFromDiff($mainValue, $diff)
    {
        $result = '';
        $in_offset = 0;
        foreach ($diff->edits as $edit) {
            $n = $edit->getFromLen();
            if ($edit instanceof \GorHill\FineDiff\FineDiffCopyOp) {
                $result .= substr($mainValue, $in_offset, $n);
            }
            $in_offset += $n;
        }

        return $result;
    }

    /**
     * формирует доп.условия акции
     *
     * @throws \yii\base\ErrorException
     */
    public function calculateConditions()
    {

        // для подарков иногда условие хранится в логике
        if($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_GIFT) {
            $this->fixGiftConditionLogic();
        }

        foreach ($this->condition as $condition) {
            $class = explode('\\', $condition['class']);
            $class = array_pop($class);
            $func = 'condition'.$class;

            // пропускаем акции, привязанные к конкретным юзерам (тестовые)
            /*if($class == 'ForUsers') {
                $this->_shopDiscount->delete();
                return true;
            }*/

            if (!method_exists($this, $func)) {
                $this->_shopDiscount->delete();
                throw new \yii\base\ErrorException("{$func} not implemented yet ");
                continue;
            }

            // конфигурация уже загружена. Чистим
            if(!empty($this->_shopDiscount->configurations)) {
                foreach ($this->_shopDiscount->configurations as $configuration) $configuration->delete();
            }

            // пересчет лестницы скидок
            if($this->_shopDiscount->value_type == ShopDiscount::VALUE_TYPE_LADDER && ($class == 'ForSum' || $class == 'ForSumRange')) {
                // чистим ранее загруженные
                if(!empty($this->_shopDiscount->shopDiscountLogics)) {
                    foreach ($this->_shopDiscount->shopDiscountLogics as $shopDiscountLogic) $shopDiscountLogic->delete();
                }

                $this->calculateLadderCondition($condition, $this->_shopDiscount);
                $class = 'EmptyCondition';
                $func = 'condition'.$class;
            }

            if($class == 'ForLotGroupCount') $class = 'ForLots';
            elseif($class == 'ForQuantity') $class = 'ForCount';
            elseif($class == 'ForLots' && isset($condition['params']['checkPlusOneBasket']) && $condition['params']['checkPlusOneBasket']) {
                $class = 'ForCtsPlusOne';
            }

            $entity = Entity::findOne(['class' => $class]);
            if (!$entity) {
                throw new \yii\base\ErrorException("entity for $class not found");
            }
            $configuration = new Configuration([
                'shop_discount_id' => $this->_shopDiscount->id,
                'shop_discount_entity_id' => $entity->id
            ]);
            $configuration->save(false);

            //$this->manager->controller->stdout("$func params ".print_r($condition['params'], true));
            call_user_func([$this, $func], $configuration, $condition['params']);
        }
    }

    public function fixGiftConditionLogic()
    {
        $params = @$this->logic[0]['params'];
        // если в логике указан тип - добавляем условие по категории
        if(array_key_exists('type', $params)) {
            // убираем условие "Без условией" если было
            foreach ($this->condition as $i => $condition) {
                if($condition['class'] == '\SandsPromo\EmptyCondition') {
                    unset($this->condition[$i]);
                }
            }

            $type = $params['type'];
            if($type == 'OnlyJewerly') {
                $this->condition[] = [
                    'class' => '\SandsPromo\ForSection',
                    'params' => [
                        'sections_site' => \common\lists\TreeList::getJewelryList()
                    ]
                ];
            }
            elseif($type == 'OnlyNormal') {
                $this->condition[] = [
                    'class' => '\SandsPromo\ForSection',
                    'params' => [
                        'sections_site' => \common\lists\TreeList::getNotJewelryList()
                    ]
                ];
            }
        }
    }

    /*
    +EmptyCondition
    ForBrands
    +ForCount
    +ForLots
    ForPromoCode
    +ForQuantity
    +ForSection
    +ForSum
    -ForSumRange
    +ForUsers
    ForCTS
    ForJew
    ForSales
     */

    /**
     * пересчитывает условия лестницы скидок
     * @param              $condition
     * @param ShopDiscount $shopDiscount
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function calculateLadderCondition($condition, ShopDiscount $shopDiscount) {
        $value = isset($condition['params']['sum']) ? $condition['params']['sum'] : $condition['params']['from'];
        $logic = $this->logic[0]['params'];

        $configurationModel = new SsShopDiscountLogic([
            'shop_discount_id' => $shopDiscount->id,
            'logic_type' => SsShopDiscountLogic::LOGIC_TYPE_BASKET,
            'value' => $value,
            'discount_type' => $logic['type'] == 'P' ? SsShopDiscountLogic::DISCOUNT_TYPE_PERCENT : SsShopDiscountLogic::DISCOUNT_TYPE_FIXED,
            'discount_value' => $logic['sale']
        ]);

        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }

        // и по связанным акциям
        foreach ($this->_relatedPromo as $relatedPromo) {
            foreach ($relatedPromo->condition as $condition) {
                $class = explode('\\', $condition['class']);
                $class = array_pop($class);
                if($class == 'ForSum' || $class == 'ForSumRange') {
                    $relatedPromo->calculateLadderCondition($condition, $shopDiscount);
                }
            }
        }

        return true;
    }

    protected function conditionEmptyCondition()
    {
        return true;
    }

    protected function conditionForSum(Configuration $configuration, $params)
    {
        $configurationModel = new ConfigurationValueForSum([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $params['sum']
        ]);


        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }

        return true;
    }

    protected function conditionForSection(Configuration $configuration, $params)
    {
        if(array_key_exists('sections_site', $params)) {
            $sections = $params['sections_site'];
        }
        else {
            $sections = \skeeks\cms\models\CmsTree::find()->andWhere(['bitrix_id' => $params['sections']])->select('id')->asArray()->column();
        }

        $configurationModel = new ConfigurationValueForSection([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $sections
        ]);

        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }

        return true;
    }

    protected function conditionForLots(Configuration $configuration, $params)
    {
        // в bo модифицировали логику ForLots, теперь если есть ключ checkPlusOneBasket, то это логика цтс+лот
        if (isset($params['checkPlusOneBasket']) && $params['checkPlusOneBasket']) {
            return $this->conditionForCtsPlusOne($configuration, $params);
        }
        
        $lots = \common\models\cmsContent\CmsContentElement::find()->andWhere(['bitrix_id' => $params['lotsId']])->select('id')->asArray()->column();

        /*if(sizeof($lots) != sizeof($params['lotsId'])) {
            $this->_shopDiscount->delete();
            throw new \yii\db\Exception('Не удалось найти все лоты ');
        }*/

        $configurationModel = new ConfigurationValueForLots([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $lots
        ]);

        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }
        
        return true;
    }

    protected function conditionForCtsPlusOne(Configuration $configuration, $params)
    {
        $lots = \common\models\cmsContent\CmsContentElement::find()->andWhere(['bitrix_id' => $params['lotsIdPlusOne']])->select('id')->asArray()->column();

        /*if(sizeof($lots) != sizeof($params['lotsIdPlusOne'])) {
            $this->_shopDiscount->delete();
            throw new \yii\db\Exception('Не удалось найти все лоты ');
        }*/

        $configurationModel = new ConfigurationValueForCtsPlusOne([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $lots
        ]);

        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }

        return true;
    }

    protected function conditionForLotGroupCount(Configuration $configuration, $params)
    {
        $searchLots = \common\helpers\ArrayHelper::arrayFlatten($params['lotsGroup']);
        $lots = \common\models\cmsContent\CmsContentElement::find()
            ->andWhere(['bitrix_id' => $searchLots])
            ->select('id')->asArray()->column();

        /*if(sizeof($searchLots) != sizeof($lots)) {
            $this->_shopDiscount->delete();
            throw new \yii\db\Exception('Не удалось найти все лоты ');
        }*/

        $configurationModel = new ConfigurationValueForLots([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $lots
        ]);

        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }

        return true;
    }

    protected function conditionForCount(Configuration $configuration, $params)
    {
        $configurationModel = new ConfigurationValueForCount([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $params['itemCount']
        ]);

        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }

        return true;
    }

    protected function conditionForQuantity(Configuration $configuration, $params)
    {
        return $this->conditionForCount($configuration, $params);
    }

    protected function conditionForBasketElementsQty(Configuration $configuration, $params)
    {
        // пока поддерживается только режим "больше" (остальные вообще неясно зачем нужны)
        if ($params['equalCountType'] != 'more') {
            throw new \yii\db\Exception('не поддерживается режим equalCountType = '.$params['equalCountType']);
        }

        $configurationModel = new ConfigurationValueForBasketElementsQty([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $params['compareQuantity']
        ]);

        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }

        return true;
    }

    protected function conditionForUsers(Configuration $configuration, $params)
    {
        $configurationModel = new ConfigurationValueForUsers([
            'shop_discount_configuration_id' => $configuration->id,
            'value' => $params['usersID']
        ]);

        if(!$configurationModel->save()){
            throw new \yii\db\Exception('Не удалось сохранить значения условий');
        }

        return true;
    }
}