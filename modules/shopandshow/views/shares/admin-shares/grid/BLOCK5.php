<?php
/** @var \yii\web\View $this */

use modules\shopandshow\models\shares\SsShare;

/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_5_1',
    'BANNER_5_2',
    'BANNER_5_3',
    'BANNER_5_4',
    'BANNER_5_5',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_5_')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);

?>
<div class="discount-banner-row discount-banner-list">
    <div class="row grid-vertical-item" style="margin-bottom: 10px">
        <div class="col-xs-6">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_5_1'], 'w' => 340, 'h' => 200]); ?>
        </div>

        <div class="col-xs-6">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_5_2'], 'w' => 340, 'h' => 200]); ?>
        </div>
    </div>

    <div class="row grid-vertical-item">
        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_5_3'], 'w' => 220, 'h' => 140]); ?>
        </div>

        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_5_4'], 'w' => 220, 'h' => 140]); ?>
        </div>

        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_5_5'], 'w' => 220, 'h' => 140]); ?>
        </div>
    </div>
</div>