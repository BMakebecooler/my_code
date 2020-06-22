<?php
/** @var \modules\shopandshow\widgets\grid\DefaultWidget $widget */

use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shop\ShopProduct;

$imageSrc = $widget->image_id ? \skeeks\cms\models\StorageFile::findOne($widget->image_id)->src : '';
$imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => 700, 'h' => 0]));

?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <?= $this->render('_header', [
        'widget' => $widget
    ])?>

    <tr>
        <td valign="top" align="center">
            <a href="<?= $widget->getResponseLink($widget->imageUrl); ?>" style="display:block">
                <img width="100%" src="<?= $widget->makeAbsUrl($imageSrc); ?>" alt="<?= $widget->imageTitle; ?>"
                     style="display:block">
            </a>
        </td>
    </tr>

    <tr>
        <td valign="top" style="padding-top:15px">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <?php
                $imageWidth = 205;
                if ($widget->products)
                    foreach (array_chunk($widget->products, 3) as $row): ?>
                        <tr>
                            <?php foreach ($row as $col => $product_id): ?>
                                <?php
                                /** @var CmsContentElement $cmsContentElement */
                                $cmsContentElement = common\lists\Contents::getContentElementById($product_id);
                                /** @var ShopProduct $shopProduct */
                                $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement);
                                $shopProductImageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest(
                                    $cmsContentElement->image->src,
                                    new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0])
                                );
                                ?>
                                <td valign="top" align="center"
                                    style="font-size:16px;line-height:22px; <?= $col == 1 ? 'padding:0 43px 0 42px;' : ''; ?>">
                                    <a href="<?= $widget->getResponseLink($cmsContentElement->url); ?>"
                                       style="display:block;margin-bottom:10px">
                                        <img width="<?= $imageWidth; ?>"
                                             src="<?= $widget->makeAbsUrl($shopProductImageSrc); ?>"
                                             alt="<?= $cmsContentElement->getLotName(); ?>" style="display:block">
                                    </a>

                                    <p style="margin:0 0 2px; font-size: 15px;">
                                        <? if ($shopProduct->isDiscount()) : ?>
                                            <span style="color:#f04374"><?= $shopProduct->getBasePriceMoney(); ?>
                                                руб.</span>
                                            <span><s><?= $shopProduct->getMaxPriceMoney(); ?> руб.</s></span>
                                        <? else: ?>
                                            <span style="color:#f04374"><?= $shopProduct->getBasePriceMoney(); ?>
                                                руб.</span>
                                        <? endif; ?>
                                    </p>

                                    <p style="margin:0">
                                        <a href="<?= $widget->getResponseLink($cmsContentElement->url); ?>"
                                           style="text-decoration: none; color: #3d3f42;">
                                            <?= $cmsContentElement->getLotName(); ?>
                                        </a>
                                    </p>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
            </table>
        </td>
    </tr>

    <?php if ($widget->button): ?>
        <tr>
            <td valign="top" align="center" style="padding-top:22px;padding-bottom:25px">
                <a href="<?= $widget->getResponseLink($widget->buttonUrl); ?>"
                   style="display:inline-block;width:180px;height:35px;background-color:#970330;border-radius:3px;color:#ffffff;font-size:18px;line-height:35px;text-align:center;text-decoration:none">
                    <?= $widget->buttonTitle; ?>
                </a>
            </td>
        </tr>
    <?php endif; ?>
</table>
