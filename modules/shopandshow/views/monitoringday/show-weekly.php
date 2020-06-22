<?php

use modules\shopandshow\models\monitoringday\PlanWeekly;
use modules\shopandshow\models\shop\ShopProduct;
use yii\bootstrap\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model PlanWeekly */

?>

    <div class="h3">Еженедельный отчет</div>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
]); ?>

<?= $form->field($model, 'date_from')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
); ?>

<?= $form->field($model, 'date_to')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
); ?>

<?= $form->field($model, 'email'); ?>

<?= Html::submitButton("Показать", [
    'class' => 'btn btn-primary',
    'onclick' => '$("#submitType").val('.$model::SUBMIT_HTML.');'
]); ?>

<?= Html::submitButton("Отправить на указанный email", [
    'class' => 'btn btn-primary',
    'onclick' => '$("#submitType").val('.$model::SUBMIT_EMAIL.');'
]); ?>

<?= $form->field($model, 'submitType', ['options' => ['tag' => false]])->hiddenInput(['id' => 'submitType'])->label(false); ?>

<? if ($model->submitType != $model::SUBMIT_HTML): ?>
    <?php ActiveForm::end(); ?>
    <?php return; ?>
<? endif; ?>

    <table class="table table-bordered table-sm">
        <!-- шапка -->
        <thead>
        <tr>
            <th></th>
            <th colspan="<?= $model->numDays * 2; ?>" style="color: red">
                ОТЧЕТ, <?= $model::formatDate($model->date_from); ?> - <?= $model::formatDate($model->date_to); ?>
                <br>
                <b>Средний доход в день за последние <?= PlanWeekly::BIG_DATA_DAYS; ?> дней:</b>
                <?= \Yii::$app->formatter->asDecimal($model->getBigData('orders_avg_sum'), 1); ?> руб.
            </th>
        </tr>
        <tr>
            <th>
                <small>Дата</small>
            </th>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2"><?= $model::formatDate($plan->date); ?></th>
            <? endforeach; ?>
        </tr>
        <tr>
            <th>
                <small>День недели</small>
            </th>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2"><?= $model::getDayOfWeek($plan->date); ?></th>
            <? endforeach; ?>
            <th>Итого</th>
        </tr>
        <tr>
            <th>Итого, в день сайт (план)</th>
            <? $sum_plan = 0; ?>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2"><?= \Yii::$app->formatter->asDecimal($plan->sum_plan); ?> руб.</th>
                <? $sum_plan += $plan->sum_plan; ?>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($sum_plan); ?> руб.</th>
        </tr>
        <tr>
            <th>Итого, в день сайт (факт)</th>
            <? $sum_fact = 0; ?>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('orders_sum', $plan->date), 1); ?> руб.</th>
                <? $sum_fact += $model->getData('orders_sum', $plan->date); ?>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($sum_fact, 1); ?> руб.</th>
        </tr>
        <tr>
            <th>% от плана (сайт) - факт</th>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2"><?= \Yii::$app->formatter->asPercent($model->getData('orders_sum', $plan->date) / $plan->sum_plan, 2); ?></th>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asPercent($sum_fact / $sum_plan, 2); ?></th>
        </tr>
        <tr>
            <th>% по сравнению со средним значением дохода за последние 4 недели</th>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2"><?= \Yii::$app->formatter->asPercent($model->getData('orders_sum', $plan->date) / $model->getBigData('orders_avg_sum', 1), 2); ?></th>
            <? endforeach; ?>
            <th>&nbsp;</th>
        </tr>
        <tr>
            <th>Сумма доставки</th>
            <? $sum_delivery = 0; ?>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('orders_delivery_sum', $plan->date)); ?> руб.</th>
                <? $sum_delivery += $model->getData('orders_delivery_sum', $plan->date); ?>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($sum_delivery); ?> руб.</th>
        </tr>
        <tr>
            <th>Маржа (руб)</th>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('marge', $plan->date)); ?> руб.</th>
            <? endforeach; ?>
            <th>
                <?= \Yii::$app->formatter->asDecimal($model->getData('marge')); ?> руб.
            </th>
        </tr>
        <tr>
            <th>Маржа (%)</th>
            <? foreach ($model->plans as $plan): ?>
                <th colspan="2">
                    <?= \Yii::$app->formatter->asPercent($model->getData('marge', $plan->date) / $model->getData('orders_sum', $plan->date, 1), 1); ?>
                </th>
            <? endforeach; ?>
            <th>
                <?= \Yii::$app->formatter->asPercent($model->getData('marge') / $model->getData('orders_sum', null, 1), 1); ?>
            </th>
        </tr>
        <?php if ($model->getPlansEfir()): ?>
            <tr>
                <th>Итого, в день эфир (план)</th>
                <? $sum_plan = 0; ?>
                <? foreach ($model->getPlansEfir() as $plan): ?>
                    <th colspan="2"><?= \Yii::$app->formatter->asDecimal($plan->sum_plan); ?> руб.</th>
                    <? $sum_plan += $plan->sum_plan; ?>
                <? endforeach; ?>
                <th><?= \Yii::$app->formatter->asDecimal($sum_plan); ?> руб.</th>
            </tr>
            <tr>
                <th>Итого, в день эфир (факт)</th>
                <? $sum_fact = 0; ?>
                <? foreach ($model->getPlansEfir() as $plan): ?>
                    <th colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('efir_total_sum', $plan->date)); ?> руб.</th>
                    <? $sum_fact += $model->getData('efir_total_sum', $plan->date); ?>
                <? endforeach; ?>
                <th><?= \Yii::$app->formatter->asDecimal($sum_fact); ?> руб.</th>
            </tr>
            <tr>
                <th>% от плана (эфир) - факт</th>
                <? foreach ($model->getPlansEfir() as $plan): ?>
                    <th colspan="2"><?= \Yii::$app->formatter->asPercent($model->getData('efir_total_sum', $plan->date) / $plan->sum_plan, 2); ?></th>
                <? endforeach; ?>
                <th><?= \Yii::$app->formatter->asPercent($sum_fact / $sum_plan, 2); ?></th>
            </tr>
        <?php endif; ?>
        </thead>

        <tbody>
        <tr>
            <td>Кол-во проданных товаров</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('baskets_quantity', $plan->date)); ?></td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('baskets_quantity')); ?></th>
        </tr>
        <tr>
            <td>Кол-во заказов</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('orders_count', $plan->date)); ?></td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('orders_count')); ?></th>
        </tr>
        <tr>
            <td>Средний чек</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('orders_sum', $plan->date) / $model->getData('orders_count', $plan->date, 1), 2); ?> руб.</td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('orders_sum') / $model->getData('orders_count', null, 1), 2); ?> руб.</th>
        </tr>
        <tr>
            <td>Среднее кол-во товаров в чеке</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('baskets_quantity', $plan->date) / $model->getData('orders_count', $plan->date, 1), 2); ?></td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('baskets_quantity') / $model->getData('orders_count', null, 1), 2); ?></th>
        </tr>

        <!-- акции -->
        <tr>
            <td colspan="<?= $model->numDays * 2 + 1; ?>"></td>
        </tr>
        <tr>
            <td>1. Акция сайта</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2">
                    <?
                    echo "&mdash; ";
                    if ($shopDiscounts = $model->getData('discounts', $plan->date, [])) {
                        echo join("<br>\n&mdash; ", array_column($shopDiscounts, 'name'));
                    }
                    ?>
                </td>
            <? endforeach; ?>
        </tr>
        <tr>
            <td>Акция эфира</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2">
                    <?
                    echo "&mdash; ";
                    if ($airActions = $model->getData('air_actions', $plan->date, [])) {
                        echo join("<br>\n&mdash; ", $airActions);
                    }
                    ?>
                </td>
            <? endforeach; ?>
        </tr>

        <!-- цтс -->
        <tr>
            <td colspan="<?= $model->numDays * 2 + 1; ?>"></td>
        </tr>
        <tr>
            <td>2. Цтс</td>
            <td colspan="<?= $model->numDays * 2; ?>"></td>
        </tr>
        <tr>
            <td>какой</td>
            <? foreach ($model->plans as $plan): ?>
                <?
                /** @var \common\models\cmsContent\CmsContentElement $cts */
                $cts = $model->getData('cts', $plan->date, null);
                ?>
                <td colspan="2"><?= $cts ? sprintf('%s [%s]', $cts->name, $cts->relatedPropertiesModel->getAttribute('LOT_NUM')) : '&mdash;'; ?></td>
            <? endforeach; ?>
        </tr>
        <tr>
            <td>цена</td>
            <? foreach ($model->plans as $plan) {
                /** @var ShopProduct $ctsProduct */
                $ctsProduct = $model->getData('cts_product', $plan->date, null);
                if ($ctsProduct) {
                    echo '<td colspan="2">'.\Yii::$app->formatter->asDecimal($ctsProduct->getProductPriceByType('TODAY')->price).' руб.</td>';
                }
                else {
                    echo '<td colspan="2">&mdash;</td>';
                }
            }
            ?>
        </tr>
        <tr>
            <td>доля ЦТС в эфире (норма - 20%)</td>
            <? foreach ($model->plans as $plan) {
                echo '<td colspan="2">'.\Yii::$app->formatter->asPercent($model->getData('efir_cts_sum', $plan->date) / $model->getData('efir_total_sum', $plan->date, 1), 1).'</td>';
            }
            ?>
            <th><?= \Yii::$app->formatter->asPercent($model->getData('efir_cts_sum') / $model->getData('efir_total_sum', null, 1), 1); ?></th>
        </tr>
        <tr>
            <td>доля ЦТС на сайте (ср. знач - 10%)</td>
            <? foreach ($model->plans as $plan) {
                echo '<td colspan="2">'.\Yii::$app->formatter->asPercent($model->getData('cts_sum', $plan->date) / $model->getData('orders_sum', $plan->date, 1), 1).'</td>';
            }
            ?>
            <th><?= \Yii::$app->formatter->asPercent($model->getData('cts_sum') / $model->getData('orders_sum', null, 1), 1); ?></th>
        </tr>
        <tr>
            <td>отклонение от нормы - факт</td>
            <? foreach ($model->plans as $plan) {
                echo '<td colspan="2">'.\Yii::$app->formatter->asPercent($model->getData('cts_sum', $plan->date) / $model->getData('orders_sum', $plan->date, 1) - 0.1, 1).'</td>';
            }
            ?>
            <th><?= \Yii::$app->formatter->asPercent($model->getData('cts_sum') / $model->getData('orders_sum', null, 1) - 0.1, 1); ?></th>
        </tr>
        <tr>
            <td>отклонение от нормы - прогноз</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2">(не реализовано)</td>
            <? endforeach; ?>
        </tr>
        <tr>
            <td>сумма дохода по ЦТС САЙТ</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('cts_sum', $plan->date)); ?> руб.</td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('cts_sum')); ?> руб.</th>
        </tr>
        <tr>
            <td>сумма дохода по ЦТС ЭФИР</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2"><?= \Yii::$app->formatter->asDecimal($model->getData('efir_cts_sum', $plan->date)); ?> руб.</td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('efir_cts_sum')); ?> руб.</th>
        </tr>

        <!-- по категориям -->
        <tr>
            <td colspan="<?= $model->numDays * 2 + 1; ?>"></td>
        </tr>
        <tr>
            <td>3. По категориям</td>
            <td colspan="<?= $model->numDays * 2; ?>"></td>
        </tr>
        <?php
        $dailyAirBlock = $model->getDailyAirBlocks();
        ?>
        <? foreach ($model->getCategories() as $rootCategory): ?>
            <tr>
                <td><?= $rootCategory->name; ?></td>
                <? $categorySum = 0; ?>
                <? foreach ($model->plans as $plan): ?>
                    <?
                    $sumForCategory = $model->getSumForCategory($plan->date, $rootCategory->id);
                    $categorySum += $sumForCategory;
                    $isCategoryOnair = array_key_exists($rootCategory->id, $dailyAirBlock[$plan->date]);
                    ?>
                    <td<?= $isCategoryOnair ? ' style="background: #90EE90"' : '' ?>>
                        <?= \Yii::$app->formatter->asDecimal($sumForCategory, 0); ?>
                    </td>
                    <td>
                        <?= \Yii::$app->formatter->asDecimal($sumForCategory - $model->getCategoryAvg($rootCategory->id, $isCategoryOnair, $dailyAirBlock[$plan->date]), 0); ?>
                    </td>
                <? endforeach; ?>
                <th><?= \Yii::$app->formatter->asDecimal($categorySum); ?></th>
            </tr>
            <? foreach ($model->getCategories($rootCategory->id) as $childCategory): ?>
                <tr>
                    <td style="color: gray; font-size: 10px;"> - <?= $childCategory->name; ?></td>
                    <? $categorySum = 0; ?>
                    <? foreach ($model->plans as $plan): ?>
                        <?
                        $sumForCategory = $model->getSumForCategory($plan->date, $childCategory->id);
                        $categorySum += $sumForCategory;
                        ?>
                        <td colspan="2" style="color: gray; font-size: 10px;"><?= \Yii::$app->formatter->asDecimal($sumForCategory); ?></td>
                    <? endforeach; ?>
                    <th><?= \Yii::$app->formatter->asDecimal($categorySum); ?></th>
                </tr>
            <? endforeach; ?>
        <? endforeach; ?>

        <!-- по МП рубрикам -->
        <tr>
            <td colspan="<?= $model->numDays * 2 + 1; ?>"></td>
        </tr>
        <tr>
            <? $airBlocks = $model->getAirBlocksData(); ?>
            <td rowspan="<?= sizeof($airBlocks)+1; ?>">4. Объём часов по рубрикам</td>
        </tr>
        <? foreach ($airBlocks as $row => $items): ?>
            <tr>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2"><?= isset($items[$plan->date]) ? key($items[$plan->date]) . ' : ' . current($items[$plan->date]) : ''; ?></td>
            <? endforeach; ?>
            </tr>
        <? endforeach; ?>

        <!-- по товарам из эфира -->
        <tr>
            <td colspan="<?= $model->numDays * 2 + 1; ?>"></td>
        </tr>
        <tr>
            <td>5. Продажи товаров из прямого эфира</td>
            <td colspan="<?= $model->numDays * 2; ?>"></td>
        </tr>

        <tr>
            <td>Продажи товаров из ПЭ</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2">
                    <?= \Yii::$app->formatter->asDecimal($model->getData('baskets_onair_sum', $plan->date)); ?> руб. / <?= $model->getData('baskets_onair_quantity', $plan->date); ?>
                    шт.
                </td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('baskets_onair_sum')); ?> руб. / <?= $model->getData('baskets_onair_quantity'); ?> шт.</th>
        </tr>
        <tr>
            <td>Продажи товаров из акций (кроме ПЭ)</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2">
                    <?= \Yii::$app->formatter->asDecimal($model->getData('baskets_onbanner_sum', $plan->date)); ?> руб. / <?= $model->getData('baskets_onbanner_quantity', $plan->date); ?>
                    шт.
                </td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('baskets_onbanner_sum')); ?> руб. / <?= $model->getData('baskets_onbanner_quantity'); ?> шт.</th>
        </tr>
        <tr>
            <td>Продажи остальное</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2">
                    <?= \Yii::$app->formatter->asDecimal($model->getData('baskets_other_sum', $plan->date)); ?> руб. / <?= $model->getData('baskets_other_quantity', $plan->date); ?>
                    шт.
                </td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('baskets_other_sum')); ?> руб. / <?= $model->getData('baskets_other_quantity'); ?> шт.</th>
        </tr>
        <tr>
            <td>% продаж товаров ПЭ от общего дохода сайта</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2">
                    <?= \Yii::$app->formatter->asPercent($model->getData('baskets_onair_sum', $plan->date) / $model->getData('orders_sum', $plan->date, 1), 1); ?>
                </td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asPercent($model->getData('baskets_onair_sum') / $model->getData('orders_sum', null, 1), 1); ?></th>
        </tr>
        <tr>
            <td>Трафик (сеансы)</td>
            <? foreach ($model->plans as $plan): ?>
                <td colspan="2">
                    <?= \Yii::$app->formatter->asDecimal($model->getData('sessions', $plan->date)); ?>
                </td>
            <? endforeach; ?>
            <th><?= \Yii::$app->formatter->asDecimal($model->getData('sessions')); ?></th>
        </tr>
        </tbody>
    </table>

<?php ActiveForm::end(); ?>