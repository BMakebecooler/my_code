<?php
/** @var \yii\web\View $this */

use modules\shopandshow\models\shares\SsShare;

/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$placeholders = [
    'BANNER_11_1',
    'BANNER_11_2',
    'BANNER_11_3',
    'BANNER_11_4',
    'BANNER_11_5',
];

$banners = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_11_')
    ->byBlockId($blockId)
    ->orderForGrid()
    ->indexBy('id')
    ->fillPlaceholders($placeholders);


?>

<div class="discount-banner-row discount-banner-list" data-type="BLOCK11">
    <div class="row grid-vertical-item flex-box">
        <div class="banner-group col-xs-8">
            <div class="banner-row grid-vertical-item flex-box row" style="margin-bottom: 10px;">
                <div class="banner-item col-xs-5">
                    <?= $this->render('_grid-item', ['banner' => $banners['BANNER_11_1'], 'w' => 200, 'h' => 140]); ?>
                </div>

                <div class="banner-item col-xs-6">
                    <?= $this->render('_grid-item', ['banner' => $banners['BANNER_11_2'], 'w' => 240, 'h' => 140]); ?>
                </div>
            </div>

            <div class="banner-row flex-box row">
                <div class="banner-item col-xs-6">
                    <?= $this->render('_grid-item', ['banner' => $banners['BANNER_11_3'], 'w' => 240, 'h' => 140]); ?>
                </div>

                <div class="banner-item col-xs-5">
                    <?= $this->render('_grid-item', ['banner' => $banners['BANNER_11_4'], 'w' => 200, 'h' => 140]); ?>
                </div>
            </div>
        </div>

        <div class="banner-item col-xs-4">
            <?= $this->render('_grid-item', ['banner' => $banners['BANNER_11_5'], 'w' => 180, 'h' => 290]); ?>
        </div>
    </div>
</div>