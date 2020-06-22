<?php

use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopContentElement;
use skeeks\cms\components\Cms;

$this->registerJs(<<<JS

(function(sx, $, _)
{
    new sx.classes.SlickSlider();
})(sx, sx.$, sx._);
JS
);

/** @var \yii\web\View $this */
/** @var SsShare[] $banners */
/** @var $searchDate string */
/** @var $blockId integer */

$share = SsShare::find()
    ->active()
    ->byDate($searchDate)
    ->byBannerType('BANNER_SLIDER')
    ->byBlockId($blockId)
    ->one();

if ($share) {
    $widget = new \common\widgets\shares\blockslider\BannerContentElementWidget([
        'contentElementClass' => ShopContentElement::className(),
        'namespace' => 'BannerContentElementWidget-slider-' . $share->id,
        'viewFile' => '@site/widgets/ContentElementsCms/sliders/products_6_slick',
        'active' => Cms::BOOL_Y,
        'orderBy' => false,
        'groupBy' => true, // Необходимо использовать группировку
        'enabledCurrentTree' => false,
        'content_ids' => [PRODUCT_CONTENT_ID],
        'activeQueryCallback' => function (\yii\db\ActiveQuery $query) use ($share) {

            $query->innerJoin(
                'ss_shares_products',
                'ss_shares_products.product_id = cms_content_element.id AND ss_shares_products.banner_id = :banner_id',
                [':banner_id' => $share->id]
            );
            $query->limit(30);
            $query->orderBy('ss_shares_products.priority');

        }
    ]);

    \Yii::$app->cmsToolbar->initEnabled();
    \Yii::$app->cmsToolbar->enabled = true;
    \Yii::$app->cmsToolbar->editWidgets = \skeeks\cms\components\Cms::BOOL_Y;
    \Yii::$app->cmsToolbar->inited = true;
}

?>

<div class="products-tape" data-type="BLOCKSLIDER">
    <?php if ($share): ?>
        <?= $widget->run(); ?>
    <?php else: ?>
        <a class="banner-item" href="#" target="_blank" title="">
            <span style="display: block; width: 700px; height: 180px;">Слайдер не привязан</span>
        </a>
    <?php endif; ?>

    <?php if ($widget->buttonText): ?>
        <br>
        <div class="tape-control text-center">
            <a href="<?= $widget->buttonUrl ?: $share->getUrl(); ?>"
               class="btn btn-primary"><?= $widget->buttonText; ?></a>
        </div>
    <?php endif; ?>
</div>
