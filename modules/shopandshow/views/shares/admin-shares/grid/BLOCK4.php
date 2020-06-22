<?php
/** @var \yii\web\View $this */

use modules\shopandshow\models\shares\SsShare;

/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_4_1',
    'BANNER_4_2',
    'BANNER_4_3',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_4_')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);

?>
<div class="discount-banner-row discount-banner-list">
    <div class="row grid-vertical-item">
        <div class="col-xs-6">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_4_1'], 'w' => 340, 'h' => 290]); ?>
        </div>

        <div class="col-xs-6">
            <div class="grid-vertical-item" style="margin-bottom: 10px;">
                <?= $this->render('_grid-item', ['banner' => $banners['BANNER_4_2'], 'w' => 340, 'h' => 140]); ?>
            </div>

            <div class="grid-vertical-item">
                <?= $this->render('_grid-item', ['banner' => $banners['BANNER_4_3'], 'w' => 340, 'h' => 140]); ?>
            </div>
        </div>
    </div>
</div>