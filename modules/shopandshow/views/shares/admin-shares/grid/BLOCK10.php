<?php
/** @var \yii\web\View $this */

use modules\shopandshow\models\shares\SsShare;

/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_10_1',
    'BANNER_10_2',
    'BANNER_10_3',
    'BANNER_10_4',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_10_')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);

?>

<div class="discount-banner-row discount-banner-list">
    <div class="row grid-vertical-item">
        <div class="col-xs-4">
            <div class="grid-vertical-item" style="margin-bottom:10px;">
                <?= $this->render('_grid-item', ['banner' => $banners['BANNER_10_1'], 'w' => 220, 'h' => 140]); ?>
            </div>

            <div class="grid-vertical-item">
                <?= $this->render('_grid-item', ['banner' => $banners['BANNER_10_2'], 'w' => 220, 'h' => 140]); ?>
            </div>
        </div>

        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_10_3'], 'w' => 220, 'h' => 290]); ?>
        </div>

        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_10_4'], 'w' => 220, 'h' => 290]); ?>
        </div>
    </div>
</div>