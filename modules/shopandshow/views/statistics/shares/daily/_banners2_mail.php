<?php
/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\Shares\SharesStat */

/* @var $dataProvider yii\data\ArrayDataProvider */

$dataProvider = $model->getDataProvider('mail');
$rows = $dataProvider->getModels();
$blockClicks = \common\helpers\ArrayHelper::arraySumColumn($rows, 'block_clicks');

$tableRows = '';
if ($rows) {
    foreach ($rows as $row) {
        $bannerTypeLabel = \modules\shopandshow\models\shares\SsShare::getBannerTypeLabel($row['block_type']);
        $bannerType = "<strong>{$row['block_type']}</strong>" . ($bannerTypeLabel ? "<div>{$bannerTypeLabel}</div>" : '');

        $tableRows .= "
        <tr>
            <td style='background-color: #fff; text-align: center; padding: 1px 2px;'>{$row['block_row_num']}</td>
            <td style='background-color: #fff; text-align: center; padding: 1px 2px; font-size: 13px;'>{$bannerType}</td>
            <td style='background-color: #fff; text-align: center; padding: 1px 2px;'>{$row['banner_1']}</td>
            <td style='background-color: #fff; text-align: center; padding: 1px 2px;'>{$row['banner_2']}</td>
            <td style='background-color: #fff; text-align: center; padding: 1px 2px;'>{$row['banner_3']}</td>
            <td style='background-color: #fff; text-align: center; padding: 3px 2px;'>{$row['banner_4']}</td>
            <td style='background-color: #fff; text-align: center; padding: 3px 2px;'>{$row['banner_5']}</td>
        </tr>";
    }
}

$html = "

<div style='font-size: 16px; padding: 6px 12px;'>
    <div>Суммарное кол-во кликов по всем баннерам: <strong>". \Yii::$app->formatter->asDecimal($blockClicks) ."</strong></div>
</div>

<table style='background-color: #000; border-collapse: separate;' cellspacing='1' cellpadding='0'>
    <thead>
        <tr>
            <th style='background-color: #fff;'>Ряд</th>
            <th style='background-color: #fff;'>Тип блока</th>
            <th style='background-color: #fff;'>Баннер 1</th>
            <th style='background-color: #fff;'>Баннер 2</th>
            <th style='background-color: #fff;'>Баннер 3</th>
            <th style='background-color: #fff;'>Баннер 4</th>
            <th style='background-color: #fff;'>Баннер 5</th>
        </tr>
    </thead>
    <tbody>
{$tableRows}
    </tbody>
</table>";

echo $html;

?>