<?php
/** @var \yii\web\View $this */

use modules\shopandshow\models\shares\SsShare;

/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_9_1',
    'BANNER_9_2',
    'BANNER_9_3',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_9_')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);

?>
<div class="discount-banner-row discount-banner-list">
    <div class="row grid-vertical-item">
        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_9_1'], 'w' => 220, 'h' => 335]); ?>
        </div>

        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_9_2'], 'w' => 220, 'h' => 335]); ?>
        </div>

        <div class="col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_9_3'], 'w' => 220, 'h' => 335]); ?>
        </div>
    </div>
</div>