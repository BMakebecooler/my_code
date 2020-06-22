<?php
use modules\shopandshow\models\statistic\StatisticsForm;

/* @var $message string */
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model StatisticsForm */

$gmttime = time();

$statistics = $dataProvider->getModels();
$statistics = array_combine(array_map(function ($item) {
    return $item['id'];
}, $statistics), $statistics);

?>
<div class="row">
<?php foreach ($model->airBlockProducts as $airBlockProduct): ?>
    <?php
        /*if ($airBlockProduct->begin_datetime != $airBlockProduct->end_datetime) {
            $airBlockProduct->begin_datetime -= date('Z');
            $airBlockProduct->end_datetime -= date('Z');
        }*/

        $url = \skeeks\cms\helpers\UrlHelper::construct(['statistics/realtime-efir-detail'])
            ->enableAdmin()
            ->addData(['StatisticsForm[airBlockProductTimeId]' => $airBlockProduct->id])
            ->normalizeCurrentRoute()->toString();
    ?>
    <?php $cmsContentElement = \common\lists\Contents::getContentElementById($airBlockProduct->lot_id); ?>
    <div class="hour-item col-xs-1">
        <div class="item-time">
            <?= \Yii::$app->formatter->asTime($airBlockProduct->begin_datetime, 'HH:mm'); ?>
        </div>
        <a href="<?= $url; ?>">
            <div class="item-lot <?= ($gmttime > $airBlockProduct->begin_datetime ? 'efir-done' : ''); ?> <?= $airBlockProduct->id == $model->airBlockProductTimeId ? 'active' : '';?>">
                <?= $cmsContentElement->name; ?>
            </div>
        </a>
        <div class="item-stat text-nowrap">
            <span class="stat-viewed" title="Просмотров за день"><?= $statistics[$airBlockProduct->id]['count_all_viewed']; ?></span> /
            <span class="stat-basket" title="Добавлено в корзину за день"><?= $statistics[$airBlockProduct->id]['count_add_basket_day']; ?></span> /
            <span class="stat-order" title="Заказов за день"><?= $statistics[$airBlockProduct->id]['count_add_order_day']; ?></span>
            <br>
            <span class="stat-basket-convercy" title="Конверсия корзины за день">
                <?= \Yii::$app->formatter->asDecimal(100*$statistics[$airBlockProduct->id]['convercy_add_basket_day'], 2); ?> %
            </span> /
            <span class="stat-order-convercy" title="Конверсия заказов за день">
                <?= \Yii::$app->formatter->asDecimal(100*$statistics[$airBlockProduct->id]['convercy_add_order_day'], 2); ?> %
            </span>
        </div>
    </div>
<?php endforeach; ?>
</div>