<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 02.06.2015
 */

use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\SsShopDiscountLogic;
use skeeks\cms\components\Cms;

/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */
$dataProvider->query->andWhere(['type' => ShopDiscount::TYPE_DEFAULT])->orderBy(new \yii\db\Expression('active_to IS NULL DESC, active_to DESC'));
?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

    <?php echo $this->render('_search', [
        'searchModel'   => $searchModel,
        'dataProvider'  => $dataProvider,
    ]); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
        'dataProvider'      => $dataProvider,
        'filterModel'       => $searchModel,
        'pjax'              => $pjax,
        'adminController'   => \Yii::$app->controller,
        'settingsData' =>
        [
            'order' => SORT_ASC,
            'orderBy' => "priority",
        ],
        'rowOptions'=>function(ShopDiscount $shopDiscount){
            if($shopDiscount->active == Cms::BOOL_Y && $shopDiscount->active_from < time() && time() < $shopDiscount->active_to){
                return ['class' => 'active_promo'];
            }
        },
        'columns'           =>
        [
            'id',

            'name',
            'code',

            [
                'attribute'     => 'value',
                'class'         => \yii\grid\DataColumn::className(),
                'format'        => 'raw',
                'value' => function(ShopDiscount $shopDiscount)
                {
                    $result = $shopDiscount::getValueTypes()[$shopDiscount->value_type]."<br>";
                    if ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_P) {
                        $result .= \Yii::$app->formatter->asPercent($shopDiscount->value / 100);
                    } elseif ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_GIFT) {
                        $result .= $shopDiscount->getGiftTextValue();
                    } elseif ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_LADDER) {
                        $ladderLogicTypes = SsShopDiscountLogic::getLogicTypes();
                        $ladderLogicDiscountTypes = SsShopDiscountLogic::getDiscountTypes();
                        $ladderLogics = $shopDiscount->getShopDiscountLogics()->all();
                        /** @var SsShopDiscountLogic $ladderLogic */
                        foreach ($ladderLogics as $ladderLogic) {
                            $result .= $ladderLogicTypes[$ladderLogic->logic_type] . ' ' . (int)$ladderLogic->value . ' : ' .
                                $ladderLogicDiscountTypes[$ladderLogic->discount_type] . ' ' . (int)$ladderLogic->discount_value . '<br>';
                        }
                    } else {
                        $money = \skeeks\modules\cms\money\Money::fromString((string) $shopDiscount->value, $shopDiscount->currency_code);
                        $result .= \Yii::$app->money->intlFormatter()->format($money);
                    }
                    return $result;
                },
            ],

            [
                'attribute'     => 'configurations',
                'class'         => \yii\grid\DataColumn::className(),
                'format'        => 'raw',
                'value' => function(ShopDiscount $shopDiscount)
                {
                    $result = '';
                    /*$result .= '<b>Доступ: ';

                    $accessRoles = [];
                    if ($roles = \Yii::$app->authManager->roles)
                    {
                        $permission = \Yii::$app->authManager->getPermission($shopDiscount->permissionName);
                        foreach ($roles as $role)
                        {
                            //Если у роли есть это разрешение
                            if (\Yii::$app->authManager->hasChild($role, $permission))
                            {
                                $accessRoles[] = $role->description;
                            }
                        }
                    }

                    if($accessRoles) {
                        $result .= join(', ', $accessRoles);
                    }
                    else {
                        $result .= '<span class="text-danger">Отсутствует</span>';
                    }
                    $result .= '</b><br>';*/

                    if($shopDiscount->configurations)
                    foreach ($shopDiscount->configurations as $configuration) {
                        $result .= " - ".$configuration->entity->name;

                        $values = $configuration->getValues()->all();

                        if ($values) {
                            $result_values = array_reduce($values, function ($result, $value) {
                                /** @var \modules\shopandshow\models\shop\shopdiscount\ConfigurationValue $value */
                                $result .= $value->formatOutput($value->getLinkedValue()).'<br>';

                                return $result;
                            }, '');

                            if ($result_values) {
                                $result .= ': '.\yii\helpers\StringHelper::truncate($result_values, 500);
                            }
                        }

                        $result .= '<br>';

                    }

                    return $result;
                },
            ],

            [
                'attribute'     => 'last_discount',
                'class'         => \skeeks\cms\grid\BooleanColumn::className(),
            ],
            [
                'attribute'     => 'active',
                'class'         => \skeeks\cms\grid\BooleanColumn::className(),
            ],

            [
                'attribute'     => 'active_from',
                'class'         => \skeeks\cms\grid\DateTimeColumnData::className(),
            ],

            [
                'attribute'     => 'active_to',
                'class'         => \skeeks\cms\grid\DateTimeColumnData::className(),
            ],

            [
                'class' => \skeeks\cms\grid\UpdatedByColumn::className(),
                'visible' => false,
            ],

            [
                'class' => \skeeks\cms\grid\UpdatedAtColumn::className(),
                'visible' => false,
            ],
        ],
    ]); ?>

<? $pjax::end(); ?>

<?php
$this->registerCss('
    tr.active_promo td {
        background-color: #dff0d8 !important;
    }
');
?>
