<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 21.09.2016
 */

/* @var $this yii\web\View */
/* @var $searchModel \modules\shopandshow\models\searches\ShopOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use modules\shopandshow\models\shop\ShopOrder;

$filter = new \yii\base\DynamicModel([
    'id',
]);
$filter->addRule('id', 'integer');

$filter->load(\Yii::$app->request->get());

if ($filter->id)
{
    $dataProvider->query->andWhere(['id' => $filter->id]);
}

$form = \skeeks\cms\modules\admin\widgets\filters\AdminFiltersForm::begin([
        'action' => '/' . \Yii::$app->request->pathInfo,
    ]);

echo $form->field($searchModel, 'createdAtFrom')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
);
echo $form->field($searchModel, 'createdAtTo')->widget(
    \kartik\date\DatePicker::class,
    [
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]
);

$sourceItems = array_map(
    function($source){
        return ShopOrder::getSourceLabel($source['source'])." [{$source['num']}]";
    },
    $searchModel->getNonEmptySources($dataProvider)
);

$sourceDetailItems = array_map(
    function($source){
        return ShopOrder::getSourceDetailLabel($source['source_detail'])." [{$source['num']}]";
    },
    $searchModel->getNonEmptySourcesDetail($dataProvider)
);

echo $form->field($searchModel, 'source')->listBox(array_merge([''=>'Все'], $sourceItems), ['size' => 1]);
echo $form->field($searchModel, 'source_detail')->listBox(array_merge([''=>'Все'], $sourceDetailItems), ['size' => 1]);

echo $form->field($searchModel, 'priceFrom')->input('number');
echo $form->field($searchModel, 'priceTo')->input('number');

echo $form->field($filter, 'id')->setVisible();

echo $form->field($searchModel, 'canceled')->listBox([
        '' => null,
        'Y' => \Yii::t('skeeks/shop/app', 'Yes'),
        'N' => \Yii::t('skeeks/shop/app', 'No'),
    ], ['size' => 1]);

echo $form->field($searchModel, 'status_code')->listBox(\yii\helpers\ArrayHelper::merge([
        '' => null,
    ], \yii\helpers\ArrayHelper::map(\skeeks\cms\shop\models\ShopOrderStatus::find()->all(), 'code', 'name')), ['size' => 1]);

$form::end();

?>
