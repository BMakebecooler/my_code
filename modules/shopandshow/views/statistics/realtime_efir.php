<?php
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $searchDate integer */

use modules\shopandshow\models\statistic\Statistics;
use yii\widgets\ActiveForm;

$basketAvgConvercy = Statistics::getBasketAvgConvercy($dataProvider);
$orderAvgConvercy = Statistics::getOrderAvgConvercy($dataProvider);
$efirTotal = Statistics::getEfirTotal($dataProvider, $searchDate);

$action = \skeeks\cms\helpers\UrlHelper::construct(['statistics/realtime-efir'])
    ->enableAdmin()
    ->normalizeCurrentRoute()->toString();
?>

<?php $form = ActiveForm::begin(['enableAjaxValidation'=>false, 'method' => 'GET', 'action' => $action]); ?>

    <div class="alert alert-info">
        <div class="form-group">
            <label class="control-label" for="searchdate">Дата активности:</label>
            <?= \kartik\datecontrol\DateControl::widget([
                'class' => 'days',
                'name' => 'searchdate',
                'type' => \kartik\datecontrol\DateControl::FORMAT_DATE,
                'displayFormat' => 'php:Y-m-d',
                'value' => $searchDate,
                //'displayTimezone' => 'GMT'
            ]); ?>
        </div>
        <div class="form-group">
            <?= \yii\helpers\Html::submitButton("Показать", [
                'class' => 'btn btn-primary',
                'onclick' => "return sx.CmsActiveFormButtons.go('apply');",
            ]); ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>

<section class="realtime-efir-stat">
    <? // = $this->render('_realtime_efir_stat', ['dataProvider' => $dataProvider]); ?>
</section>
<br>
<?= \skeeks\cms\modules\admin\widgets\GridView::widget([
    'id' => 'realtime-efir-table',
    'dataProvider' => $dataProvider,
    //'filterModel' => $searchModel,
    'layout' => '{items}',
    'rowOptions' => function ($data) {
        $curtime = time() /*+ date('Z')*/;
        // текущий товар в эфире (GMT+3)
        if ($data['begin_datetime'] <= $curtime && $curtime < $data['end_datetime']) {
            return ['class' => 'active_row'];
        }
        return [];
    },
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'label' => "ЛОТ",
            'format' => 'raw',
            'contentOptions' => ['class' => 'nowrap'],
            'value' => function ($data) {

                $product = \common\lists\Contents::getContentElementById($data["product_id"]);

                $result = '['.$product->relatedPropertiesModel->getAttribute('LOT_NUM').']';

                if ($data['begin_datetime'] == $data['end_datetime']) {
                    $result = '<span style="color: royalblue" title="Кросс">'.$result.'</span>';
                }

                return $result;
            }
        ],
        [
            'label' => "Название",
            'format' => 'raw',
            'contentOptions' => ['class' => 'nowrap'],
            'value' => function ($data) {

                $product = \common\lists\Contents::getContentElementById($data["product_id"]);

                return \yii\bootstrap\Html::a(
                    \yii\helpers\StringHelper::truncate($product->getLotName(), 50),
                    $product->absoluteUrl,
                    ['target' => '_blank', 'data-pjax' => 0]
                );
            }
        ],
        [
            'label' => "Время начала эфира",
            'format' => 'raw',
            'value' => function ($data) {
                $url = \skeeks\cms\helpers\UrlHelper::construct(['statistics/realtime-efir-detail'])
                    ->enableAdmin()
                    ->addData(['StatisticsForm[airBlockProductTimeId]' => $data['id']])
                    ->normalizeCurrentRoute()->toString();

                $realTime = $data['begin_datetime'];
                /*if ($data['begin_datetime'] != $data['end_datetime']) {
                    $realTime = $data['begin_datetime'] - date('Z');
                }*/

                return \yii\bootstrap\Html::a(
                    \Yii::$app->formatter->asTime($realTime),
                    $url,
                    ['title' => 'Просмотр детализации лота']
                );
            }
        ],
        [
            'label' => "Время окончания эфира",
            'format' => 'raw',
            'value' => function ($data) {
                $realTime = $data['end_datetime'];
                /*if ($data['begin_datetime'] != $data['end_datetime']) {
                    $realTime = $data['end_datetime'] - date('Z');
                }*/

                return \Yii::$app->formatter->asTime($realTime);
            }
        ],
        [
            'label' => "Выручка с эфира",
            'format' => 'raw',
            'value' => function ($data) use ($efirTotal) {
                return \Yii::$app->formatter->asDecimal($efirTotal[$data["product_id"]] ?? 0);
            }
        ],
/*        [
            'label' => 'Видео',
            'format' => 'raw',
            'value' => function ($data) {
                $product = \common\lists\Contents::getContentElementById($data["product_id"]);
                return $product->isVideo() ? 'Да' : 'Нет';
            }
        ],*/
        [
            'label' => "Просмотры за день",
            'attribute' => 'count_all_viewed',
        ],
        [
            'label' => "Добавлений в корзину за день",
            'attribute' => 'count_add_basket_day',
        ],
        [
            'label' => "Конверсия добавлений в корзину за день",
            'format' => 'raw',
            'value' => function ($data) use ($basketAvgConvercy) {
                $result = \Yii::$app->formatter->asDecimal($data['convercy_add_basket_day']*100, 2).' %';

                if ($data['convercy_add_basket_day'] < $basketAvgConvercy/2) {
                    $result = "<span style='color:red'>{$result}</span>";
                }

                return $result;
            }
        ],
        [
            'label' => "Заказов за день",
            'attribute' => 'count_add_order_day',
        ],
        [
            'label' => "Конверсия заказов за день",
            'format' => 'raw',
            'value' => function ($data) use ($orderAvgConvercy) {
                $result = \Yii::$app->formatter->asDecimal($data['convercy_add_order_day']*100, 2).' %';

                if ($data['convercy_add_order_day'] < $orderAvgConvercy/2) {
                    $result = "<span style='color:red'>{$result}</span>";
                }

                return $result;
            }
        ],
        [
            'label' => 'Сумма заказов в день',
            'format' => 'raw',
            'value' => function ($data) {
                return \Yii::$app->formatter->asDecimal($data["sum_add_order_day"]);
            }
        ],
        [
            'label' => 'Доля сайта',
            'format' => 'raw',
            'value' => function ($data) use ($efirTotal) {
                if (empty($data["sum_add_order_day"])) return null;

                return \Yii::$app->formatter->asDecimal(100 * $data["sum_add_order_day"] / $efirTotal[$data['product_id']], 2).'%';
            }
        ],
        [
            'label' => "Остаток",
            'attribute' => 'quantity',
        ],
/*        [
            'label' => 'Вопросов',
            'format' => 'raw',
            'value' => function ($data) {
                $questions = \common\models\cmsContent\ContentElementFaq::find()->where(['element_id' => $data["product_id"]])->count();
                return $questions;
            }
        ],*/
    ],

]);
?>

<?php
$this->registerCss(<<<CSS
    .grid-view tr.active_row td {
        background-color: #dff0d8 !important;
    }
    
    .grid-view tr td.nowrap {
        white-space: nowrap;
    }

    .fixed-container{
      width:100%;
      margin:auto;
    }

    table#realtime-efir-table{
      //border-collapse:collapse;
      width:100%;
    }

    .fixed{
      top:50px;
      position:fixed;
      width:auto;
      display:none;
      border:none;
    }
CSS
);

$this->registerJs(<<<JS
    $(function() {
       $.fn.fixMe = function() {
           
          return this.each(function() {
             var self = $(this),
                 t_fixed;
             function init() {
                self.wrap('<div class="fixed-container" />');
                t_fixed = self.clone();
                t_fixed.find("tbody").remove().end().addClass("fixed").insertBefore(self);
                resizeFixed();
             }
             function resizeFixed() {
                t_fixed.find("th").each(function(index) {
                   $(this).css("width",self.find("th").eq(index).outerWidth()+"px");
                });
             }
             function scrollFixed() {
                var offset = $(this).scrollTop(),
                tableOffsetTop = self.offset().top,
                tableOffsetBottom = tableOffsetTop + self.height() - self.find("thead").height();
                if(offset < tableOffsetTop || offset > tableOffsetBottom)
                   t_fixed.hide();
                else if(offset >= tableOffsetTop && offset <= tableOffsetBottom && t_fixed.is(":hidden"))
                   t_fixed.show();
             }
             $(window).resize(resizeFixed);
             $(window).scroll(scrollFixed);
             init();
          });
       };
    });

    $(document).ready(function(){
       $("#realtime-efir-table").fixMe();
    });
JS
);
?>