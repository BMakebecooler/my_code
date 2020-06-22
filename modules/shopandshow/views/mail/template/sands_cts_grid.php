<?php

use common\thumbnails\Thumbnail;

/** @var \yii\web\View $this*/
/** @var array $data */
/** @var \modules\shopandshow\components\mail\BaseTemplate $template */

define('ABS_URL', $template->absUrl);
define('ABS_IMG_PATH', $template->absImgPath);
?>

<?= $this->render('_header', ['data' => $data, 'template' => $template]); ?>

<? if ($template->getResponseLink($data['PROMO_BANNER2']['URL'])) : ?>
		<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
				<tr>
						<td>
								<a href='<?= $template->getResponseLink($data['PROMO_BANNER2']['URL']); ?>'
								   style="display: block;">
										<img style="display: block;" alt="" width="700" border="0"
										     src="<?= $template->makeAbsUrl($data['PROMO_BANNER2']['IMG']); ?>">
								</a>
						</td>
				</tr>
		</table>
<? endif; ?>

<?
if ($data['PROMO_BANNER']){
    $promoBanners = array_key_exists('URL', $data['PROMO_BANNER']) ? [$data['PROMO_BANNER']] : $data['PROMO_BANNER'];

    foreach ($promoBanners as $promoBanner) {
        if($template->getResponseLink($promoBanner['URL'])){
            ?>
						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
								<tr>
										<td height="34"></td>
								</tr>
						</table>

						<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
								<tr>
										<td>
												<a href='<?= $template->getResponseLink($promoBanner['URL']); ?>'
												   style="display: block;">
														<img style="display: block;" alt="" width="700" border="0"
														     src="<?= $template->makeAbsUrl($promoBanner['IMG']); ?>">
												</a>
										</td>
								</tr>
						</table>
            <?
        }
    }
    ?>
		<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
				<tr>
						<td height="34"></td>
				</tr>
		</table>
		<?
}
?>

<? if(!empty($data['CTS_BANNER'])): //бывает что ЦТС нет, но рассылка по шаблону подходит ?>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td valign="top">
            <img src="<?= ABS_URL; ?>/shopandshow/mail/timer/" alt="В наличии по спец цене">
        </td>
    </tr>

    <tr>
        <td height="5"></td>
    </tr>

    <tr>
        <td>
            <a href='<?= $template->getResponseLink($data['CTS_BANNER']['URL']); ?>'
               style="display: block;">
                <img style="display: block;" alt="" width="700" border="0"
                     src="<?= $template->makeAbsUrl($data['CTS_BANNER']['IMG']); ?>">
            </a>
        </td>
    </tr>
</table>

<? endif; ?>

<?php
if($data['GRID'])
foreach ($data['GRID'] as $grid):
    list($schedule, $banners) = [$grid['schedule'], $grid['banners']];
    ?>
    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td height="34"></td>
        </tr>
    </table>

    <?php if (!empty($schedule->name)): ?>
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
            <tr>
                <td style="line-height: normal; text-align: center; padding: 0 14px 14px"
                    colspan="3">
                    <p style="font: 30px Arial, sans-serif; margin: 0; color: #1592a5;"><?= $schedule->name; ?></p>

                    <? if ($schedule->description): ?>
                        <p style="font: 18px/22px Arial, sans-serif; margin: 0; color: #3b3937;">
                            <?= $schedule->description; ?>
                        </p>
                    <? endif; ?>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <?= $this->render("sands_cts_grid/{$schedule->block_type}", ['template' => $template, 'banners' => $banners]); ?>
<?php endforeach; ?>

<?php if ($data['SALE']): ?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>

    <tr>
        <td>
            <table cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td style="height:30px"></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

    <tr>
        <td align="center" style="line-height: normal; padding-bottom: 15px">
            <p style="font:28px Arial, sans-serif;margin:0;color:#1592a5">Хиты продаж</p>
        </td>
    </tr>

    <tr>
        <td>
        <?php foreach (array_chunk($data['SALE'], 3) as $row): ?>
            <table cellpadding="10" cellspacing="0" border="0"
                   style="width:100%; text-align: center">
                <tbody>
                <tr>
                    <?php foreach ($row as $product):
                        $title = '[' . $product->relatedPropertiesModel->getAttribute('LOT_NUM') . '] ' . $product->getLotName();
                        ?>
                        <td style="font: 12px Arial, sans-serif; text-align: center;">
                            <a href='<?= $template->getResponseLink($product->url); ?>'
                               target="_blank">
                                <img src='<?= $template->makeAbsUrl(\Yii::$app->imaging->thumbnailUrlOnRequest($product->image->src, new Thumbnail([
                                    'w' => 224,
                                    'h' => 224,
                                ]))); ?>'
                                     alt="<?= $title; ?>"
                                     width="224"
                                     height="224">
                            </a>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($row as $product): ?>
                        <td style="vertical-align: top; font: 16px Arial, sans-serif; text-align: center; padding-bottom: 0">
                            <a href='<?= $template->getResponseLink($product->url); ?>'
                               style="color: #333333; text-decoration: none" target="_blank"
                            ><?= $product->getLotName(); ?></a>
                            <br>
                            <span style="font-size: 13px; color: #888888;">Лот: <?= $product->relatedPropertiesModel->getAttribute('LOT_NUM'); ?></span>
                        </td>
                    <?php endforeach; ?>
                </tr>

                <tr>
                    <?php foreach ($row as $product):
                        $shopCmsContentElement = new \modules\shopandshow\models\shop\ShopContentElement($product->toArray());
                        $shopProduct = \modules\shopandshow\models\shop\ShopProduct::getInstanceByContentElement($shopCmsContentElement);
                        ?>
                        <td style="vertical-align:top;padding-top:0">
                            <table cellpadding="10" style="width:100%">
                                <tbody>
                                <tr>
                                    <td align="center">
                                        <?php if ($shopProduct->isDiscount()) : ?>
                                            <span style="font:16px Arial, sans-serif;color:#333333">
                                    <del><?= $shopProduct->maxPrice(); ?> руб.</del>
                                </span>
                                            <span style="font:21px Arial, sans-serif;color:#1592a5">
                                    <?= $shopProduct->getBasePriceMoney(); ?> руб.
                                </span>
                                        <?php else: ?>
                                            <span style="font:21px Arial, sans-serif;color:#1592a5">
                                    <?= $shopProduct->getBasePriceMoney(); ?> руб.
                                </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    <?php endforeach; ?>
                </tr>
                </tbody>
            </table>
        <?php endforeach; ?>
        </td>
    </tr>

    </tbody>
</table>
<?php endif; ?>

<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tr>
        <td height="60"></td>
    </tr>
</table>

<?= $this->render('_footer', ['data' => $data, 'template' => $template]); ?>
