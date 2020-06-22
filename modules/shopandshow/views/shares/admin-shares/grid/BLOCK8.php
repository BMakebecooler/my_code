<?php
use modules\shopandshow\models\shares\SsShare;

/** @var \yii\web\View $this */
/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_8',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_8')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);

?>
<div class="discount-banner-row discount-banner-list">
    <div class="row grid-vertical-item" style="margin-bottom: 10px">
        <div class="col-xs-12">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_8'], 'w' => 700, 'h' => 180]); ?>
        </div>
    </div>
</div>