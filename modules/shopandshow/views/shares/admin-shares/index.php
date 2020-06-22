<?php

use modules\shopandshow\models\shares\SsShare;

/* @var $message string */
/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

<?php
//$reloadUrl = \skeeks\cms\helpers\UrlHelper::construct("/" . $this->context->id . '/' . $this->context->action->id)->enableAdmin()->setRoute('reload-banners')->normalizeCurrentRoute()->toString();
//echo \yii\bootstrap\Html::a('Перезаливка баннеров', [$reloadUrl], ['class' => 'btn btn-primary']);

$ssShareTypes = \modules\shopandshow\models\shares\SsShareType::find()->all();
$ssShareTypes = \common\helpers\ArrayHelper::map($ssShareTypes, 'code', 'description');

$dataProvider->setSort([
    'attributes' => [
        'id' => [
            'asc' => ['id' => SORT_ASC],
            'desc' => ['id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'begin_datetime' => [
            'asc' => ['begin_datetime' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['begin_datetime' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'end_datetime' => [
            'asc' => ['end_datetime' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['end_datetime' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'banner_type' => [
            'asc' => ['banner_type' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['banner_type' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'name' => [
            'asc' => ['name' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['name' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'code' => [
            'asc' => ['code' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['code' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'count_click' => [
            'asc' => ['count_click' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['count_click' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
        'count_click_email' => [
            'asc' => ['count_click_email' => SORT_ASC, 'id' => SORT_ASC],
            'desc' => ['count_click_email' => SORT_DESC, 'id' => SORT_DESC],
            'default' => SORT_DESC
        ],
    ],
    'defaultOrder' => [
        'begin_datetime' => SORT_DESC
    ]
]);

?>

    <br><br>

<?php echo $this->render('_search', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider
]); ?>

<?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'pjax' => $pjax,
    'adminController' => null, //\Yii::$app->controller,
    'enabledCheckbox' => false,
    'settingsData' =>
        [
            'order' => SORT_DESC,
            'orderBy' => "begin_datetime"
        ],
    'rowOptions' => function (SsShare $ssShare) {
        if ($ssShare->begin_datetime < time() && time() < $ssShare->end_datetime) {
            return ['class' => 'active_share'];
        }
    },
    'columns' =>
        [
            [
                'class' => \skeeks\cms\modules\admin\grid\ActionColumn::className(),
                'controller' => \Yii::$app->createController('/shopandshow/shares/admin-shares')[0],
            ],
            'id',
            [
                'attribute' => 'image_id',
                'class' => \modules\shopandshow\grid\ImageUpdColumn::className()
            ],
            [
                'attribute' => 'begin_datetime',
                'class' => \skeeks\cms\grid\DateTimeColumnData::className(),
            ],
            [
                'attribute' => 'end_datetime',
                'class' => \skeeks\cms\grid\DateTimeColumnData::className(),
            ],
            //'bitrix_info_block_id',
            [
                'attribute' => 'banner_type',
                'format' => 'raw',
                'value' => function (SsShare $ssShare) use ($ssShareTypes) {
                    return $ssShareTypes[$ssShare->banner_type] . '<br>(' . $ssShare->banner_type . ')';
                }
            ],
            [
                'attribute' => 'share_schedule_id',
                'format' => 'raw',
                'value' => function (SsShare $ssShare) {
                    $schedule = $ssShare->shareSchedule;
                    if ($schedule) {
                        return $schedule->getDisplayName();
                    }
                    return null;
                }
            ],
            'name',
            'code',
            [
                'attribute' => 'active',
                'class' => \skeeks\cms\grid\BooleanColumn::className(),
            ],
            [
                'attribute' => 'url',
                'format' => 'raw',
                'value' => function (SsShare $ssShare) {
                    if ($url = $ssShare->getUrl()) {
                        return \yii\bootstrap\Html::a(
                            'Перейти', $url,
                            ['target' => '_blank', 'data-pjax' => 0]
                        );
                    }

                    return '';

                }
            ],
            'bitrix_product_id',
            [
                'format' => 'raw',
                'label' => 'Лотов в акции',
                'value' => function (SsShare $share) {
                    return $share->productsCount;
                }
            ],
            [
                'format' => 'raw',
                'label' => 'Экономика',
                'value' => function (SsShare $ssShare) {

                    $sql = <<<SQL
                        SELECT count_order_product, summ_order, count_add_basket
                        FROM `ss_shares_selling` AS t

                        LEFT JOIN ( 
                            SELECT share_id, count(sell.id) AS count_order_product, ROUND(SUM(spp.price)) AS summ_order 
                            FROM ss_shares_selling AS sell
                            INNER JOIN ss_shop_product_prices AS spp ON spp.product_id = sell.product_id
                            WHERE sell.status = :status_order AND sell.share_id = :share_id
                        ) AS sell ON sell.share_id = t.share_id

                        LEFT JOIN ( 
                            SELECT sell_basket.share_id, count(sell_basket.id) AS count_add_basket
                            FROM ss_shares_selling AS sell_basket
                            WHERE sell_basket.status = :status_basket AND sell_basket.share_id = :share_id
                        ) AS sell_basket ON sell_basket.share_id = t.share_id

                        WHERE t.share_id = :share_id
                        GROUP BY t.share_id
SQL;

                    $data = \Yii::$app->db->createCommand($sql, [
                        ':share_id' => $ssShare->id,
                        ':status_order' => \modules\shopandshow\models\shares\SsShareSeller::STATUS_ORDER,
                        ':status_basket' => \modules\shopandshow\models\shares\SsShareSeller::STATUS_ADD_PRODUCT_BASKET,
                    ])->queryOne();

                    if ($data && array_filter($data)) {
                        return sprintf('%s шт./%s руб. (добавили в корзину:%s)', $data['count_order_product'], $data['summ_order'], $data['count_add_basket']);
                    }

                    return ':-(';
                }
            ],

            'count_click',
            'count_click_email',
        ]
]); ?>

<? $pjax::end(); ?>

<?php
$this->registerCss('
    tr.active_share td {
        background-color: #dff0d8 !important;
    }
');
?>