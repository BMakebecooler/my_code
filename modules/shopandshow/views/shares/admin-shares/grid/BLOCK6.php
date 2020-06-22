<?php
/** @var \yii\web\View $this */

use modules\shopandshow\models\shares\SsShare;

/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_6',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_6')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);


?>
<div class="discount-banner-row discount-banner-list">
    <div class="row grid-vertical-item" style="margin-bottom: 10px">
        <div class="col-xs-12">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_6'], 'w' => 700, 'h' => 140]); ?>
        </div>
    </div>
</div>