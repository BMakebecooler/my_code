<?php
/** @var \yii\web\View $this */

use modules\shopandshow\models\shares\SsShare;

/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_12_1',
    'BANNER_12_2',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_12_')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);

?>
<div class="discount-banner-row discount-banner-list">
    <div class="row grid-vertical-item">
        <div class="col-xs-6">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_12_1'], 'w' => 340, 'h' => 340]); ?>
        </div>

        <div class="col-xs-6">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_12_2'], 'w' => 340, 'h' => 340]); ?>
        </div>
    </div>
</div>