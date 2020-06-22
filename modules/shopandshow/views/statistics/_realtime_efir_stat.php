<?php
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$data = [
    'all' => [
        'viewed' => 0,
        'basket' => 0,
        'order' => 0,
    ],
    'questions' => [
        'viewed' => 0,
        'basket' => 0,
        'order' => 0,
    ],
    'no_questions' => [
        'viewed' => 0,
        'basket' => 0,
        'order' => 0,
    ],
    'effect' => 0
];
$statistics = $dataProvider->getModels();

$productIds = array_unique(\yii\helpers\ArrayHelper::getColumn($statistics, 'product_id', false));
$questions = \common\models\cmsContent\ContentElementFaq::find()
    ->select('id, element_id')
    ->andWhere(['element_id' => $productIds])
    ->indexBy('element_id')
    ->asArray()->all();

$cached = [];
foreach ($statistics as $i => $row) {
    if (isset($cached[$row['product_id']])) continue;

    $cached[$row['product_id']] = true;

    $data['all']['viewed'] += $row['count_all_viewed'];
    $data['all']['basket'] += $row['count_add_basket_day'];
    $data['all']['order'] += $row['count_add_order_day'];

    if (array_key_exists($row['product_id'], $questions)) {
        $data['questions']['viewed'] += $row['count_all_viewed'];
        $data['questions']['basket'] += $row['count_add_basket_day'];
        $data['questions']['order'] += $row['count_add_order_day'];
    }
    else {
        $data['no_questions']['viewed'] += $row['count_all_viewed'];
        $data['no_questions']['basket'] += $row['count_add_basket_day'];
        $data['no_questions']['order'] += $row['count_add_order_day'];
    }
}
if ($data['all']) {
    $data['all']['basket_conversy'] = $data['all']['viewed'] ? round(100 * $data['all']['basket'] / $data['all']['viewed'], 2) : 0;
    $data['all']['order_conversy'] = $data['all']['viewed'] ? round(100 * $data['all']['order'] / $data['all']['viewed'], 2) : 0;
}

if ($data['questions']) {
    $data['questions']['basket_conversy'] =
        $data['questions']['viewed'] ? round(100 * $data['questions']['basket'] / $data['questions']['viewed'], 2) : 0;
    $data['questions']['order_conversy'] =
        $data['questions']['viewed'] ? round(100 * $data['questions']['order'] / $data['questions']['viewed'], 2) : 0;
}

if ($data['no_questions']) {
    $data['no_questions']['basket_conversy'] =
        $data['no_questions']['viewed'] ? round(100 * $data['no_questions']['basket'] / $data['no_questions']['viewed'], 2) : 0;
    $data['no_questions']['order_conversy'] =
        $data['no_questions']['viewed'] ? round(100 * $data['no_questions']['order'] / $data['no_questions']['viewed'], 2) : 0;
}

if ($data['questions'] && $data['questions']['order_conversy']) {
    $data['effect'] = $data['questions']['order'] - ($data['questions']['order'] / $data['questions']['order_conversy'])
        * $data['no_questions']['order_conversy'];
}
else {
    $data['effect'] = 0;
}

if (!$data['all']) {
    return '';
}

?>

<div class="row">
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Средняя конверсия в корзину:
        </span>
        <span class="item-value">
            <?= \Yii::$app->formatter->asDecimal($data['all']['basket_conversy'], 2); ?> %
        </span>
    </div>
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Средняя конверсия в заказы:
        </span>
        <span class="item-value">
            <?= \Yii::$app->formatter->asDecimal($data['all']['order_conversy'], 2); ?> %
        </span>
    </div>
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Продажи товаров:
        </span>
        <span class="item-value">
            <?= $data['all']['order']; ?>
        </span>
    </div>
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Экономический эффект от вопросов:
        </span>
        <span class="item-value">
            <?= \Yii::$app->formatter->asDecimal($data['effect']); ?>
        </span>
    </div>
</div>
<div class="row">
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Товары без вопросов:
        </span>
        <span class="item-value">
            <?= \Yii::$app->formatter->asDecimal($data['no_questions']['basket_conversy'], 2); ?> %
        </span>
    </div>
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Товары без вопросов:
        </span>
        <span class="item-value">
            <?= \Yii::$app->formatter->asDecimal($data['no_questions']['order_conversy'], 2); ?> %
        </span>
    </div>
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Товары без вопросов:
        </span>
        <span class="item-value">
            <?= $data['no_questions']['order']; ?>
        </span>
    </div>
</div>
<div class="row">
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Товары с вопросами:
        </span>
        <span class="item-value">
            <?= \Yii::$app->formatter->asDecimal($data['questions']['basket_conversy'], 2); ?> %
        </span>
    </div>
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Товары с вопросами:
        </span>
        <span class="item-value">
            <?= \Yii::$app->formatter->asDecimal($data['questions']['order_conversy'], 2); ?> %
        </span>
    </div>
    <div class="stat-item col-xs-3">
        <span class="item-name">
            Товары с вопросами:
        </span>
        <span class="item-value">
            <?= $data['questions']['order']; ?>
        </span>
    </div>
</div>