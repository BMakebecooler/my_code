<?php
/** @var \yii\web\View $this */

use modules\shopandshow\models\shares\SsShare;

/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_7_1',
    'BANNER_7_2',
    'BANNER_7_3',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_7_')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);

?>
<div class="discount-banner-row discount-banner-list">
    <div class="row grid-vertical-item">
        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_7_1'], 'w' => 220, 'h' => 220]); ?>
        </div>

        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_7_2'], 'w' => 220, 'h' => 220]); ?>
        </div>

        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_7_3'], 'w' => 220, 'h' => 220]); ?>
        </div>
    </div>
</div>