<?php
use modules\shopandshow\models\statistic\StatisticsForm;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model StatisticsForm */

$action = \skeeks\cms\helpers\UrlHelper::construct(['statistics/realtime-efir-detail'])
    ->enableAdmin()
    ->normalizeCurrentRoute()->toString();
?>

<?php $form = ActiveForm::begin(['method' => 'GET', 'id' => 'statistic-form', 'action' => $action]); ?>

<section class="onair-schedule">
    <?= $form->field($model, 'airBlockId')->dropDownList(
            $model->getScheduleList(),
            [
                'options' => [
                    $model->getScheduleActiveBlockId() => ['class' => 'active']
                ]
            ]
        )->label(false); ?>
</section>

<section class="hour-items">
    <?= $this->render('_realtime_efir_hour_items', ['dataProvider' => $dataProvider, 'model' => $model]); ?>
</section>

<? if ($model->airBlockProductTimeId): ?>
    <?php $cmsContentElement = \common\lists\Contents::getContentElementById($model->airDayProductTime->lot_id); ?>

    <section class="lot-detail">
        <h2>Выбранный лот: <b><?= $cmsContentElement->name; ?></b></h2>
        <? /* todo статистика карточки товара: https://4mbuag.axshare.com/#g=1&p=home */ ?>
    </section>

    <section class="lot-graphs">
        <?= $this->render('_realtime_efir_graphs', ['model' => $model, 'cmsContentElement' => $cmsContentElement]); ?>
    </section>
<? endif; ?>


<?php $form::end() ?>

<?php
$this->registerCss(<<<CSS
    .grid-view tr.active_row td {
        background-color: #dff0d8 !important;
    }
    
    .grid-view tr td.nowrap {
        white-space: nowrap;
    }
    
    #statisticsform-airblockid {
        width: 300px;
    }
    #statisticsform-airblockid option.active {
        color: red;
    }

    .hour-items .hour-item {
        margin: 3px;  
    }

    .hour-items .hour-item .item-lot {
        height: 110px;
        border: 1px solid black;
        overflow: hidden;  
        padding: 3px;
    }

    .hour-items .hour-item .item-lot.efir-done {
        background-color: lightgrey;
    }

    .hour-items .hour-item .item-lot.active {
        background-color: lightsalmon;
    }
CSS
);

$this->registerJs(<<<JS
    $(function() {
        $(document, '#statisticsform-airblockid').on('change', function() {
            $('#statistic-form').submit();
        });
    });
JS
);
?>

