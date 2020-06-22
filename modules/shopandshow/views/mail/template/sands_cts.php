<?php

use common\thumbnails\Thumbnail;

/** @var array $data */
/** @var \modules\shopandshow\components\mail\BaseTemplate $template */

define('ABS_URL', $template->absUrl);
define('ABS_IMG_PATH', $template->absImgPath);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <title><?= $data['SUBJECT']; ?></title>
</head>
<body style="margin:0;padding:0;background:#f1f1f1; font-size: 16px;">
<table border="0" cellpadding="10" cellspacing="0" width="750" align="center" style="border-spacing: 0">
    <tbody>
    <tr>
        <td valign="top" align="right" width="600">
        </td>
    </tr>
    </tbody>
</table>
<table width="752" align="center" cellpadding="0" cellspacing="0"
       style="border:1px solid #cccccc;background:#fff;border-spacing: 0;">
    <tbody>
    <tr>
        <td>
            <table cellpadding="10" cellspacing="0" border="0" style="width: 100%">
                <tbody>
                <tr>
                    <td style="text-align: left; padding-left:12px; padding-right:15px;">
                        <a href='<?= $template->getResponseLink(ABS_URL); ?>'
                           style="display: block;">
                            <img src="<?= ABS_IMG_PATH; ?>/main-logo.jpg" alt="LOGO" border="0"
                                 width="288" height="70">
                        </a>
                    </td>
                    <td style="text-align:left;padding-right:12px">
                        <a href='<?= $template->getResponseLink('/onair/'); ?>'
                           class="daria-goto-anchor" style="display: block;">
                            <img alt="LIVE TV" width="78" height="47" border="0"
                                 src="<?= ABS_IMG_PATH; ?>/live-tv.jpg">
                        </a>
                    </td>
                    <td style="text-align:right;padding-right:28px">
                        <p style="margin: 0; font-family: Arial, sans-serif;">
                            <span style="font-size: 30px; color: #57514b; text-transform: uppercase;">8 (800) <span>301-60-10</span></span>
                            <br>
                            <span style="font-size: 18px; color: #57514b;">Бесплатно и круглосуточно</span>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table cellpadding="10" cellspacing="0" border="0" style="width:100%; background: #f04374">
                <tbody>
                <tr>
                    <td style="padding:16px 6px;">
                        <table cellpadding="0" cellspacing="0" border="0"
                               style="width:100%; font: 18px/normal Arial, sans-serif; text-align: center;">
                            <tbody>
                            <tr>
                                <?php
                                $trees = $template->getTreeMenuList();
                                $count = sizeof($trees);
                                ?>

                                <?php foreach ($trees as $i => $tree): ?>
                                    <td style="padding: 0 7px; <?= ($count == $i + 1 ? '' : 'border: none; border-right: 2px solid white;'); ?>">
                                        <a style="color: white; text-decoration: none;"
                                           href='<?= $template->getResponseLink($tree->url); ?>'><?= $tree->name; ?></a>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height: 15px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td>
                        <a href='<?= $template->getResponseLink($data['PROMO_BANNER2']['URL']); ?>'
                           style="display: block;">
                            <img style="display: block;" alt="" width="750" border="0"
                                 src="<?= $template->makeAbsUrl($data['PROMO_BANNER2']['IMG']); ?>">
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height: 15px;"></td>
                </tr>
                </tbody>
            </table>

            <?php if (is_array($data['MAIN_SMALL_BANNERS']) && !empty($data['MAIN_SMALL_BANNERS'])): ?>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tbody>
                    <tr>
                        <td>
                            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                                <tbody>
                                <tr>
                                    <td style="height: 30px;"></td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="line-height: normal; text-align: center; padding-bottom: 15px" colspan="3">
                            <p style="font: 30px Arial, sans-serif; margin: 0; color: #f04374;">Сегодня в эфире</p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <a href='<?= $template->getResponseLink($data['MAIN_SMALL_BANNERS'][0]['URL']); ?>'
                               style="display: block;">
                                <img style="display: block;" alt="" width="240" height="237" border="0"
                                     src="<?= $template->makeAbsUrl($data['MAIN_SMALL_BANNERS'][0]['IMG']); ?>">
                            </a>
                        </td>

                        <td style="padding: 0 15px">
                            <a href='<?= $template->getResponseLink($data['MAIN_SMALL_BANNERS'][1]['URL']); ?>'
                               style="display: block;">
                                <img style="display: block;" alt="" width="240" height="237" border="0"
                                     src="<?= $template->makeAbsUrl($data['MAIN_SMALL_BANNERS'][1]['IMG']); ?>">
                            </a>
                        </td>

                        <td>
                            <a href='<?= $template->getResponseLink($data['MAIN_SMALL_BANNERS'][2]['URL']); ?>'
                               style="display: block;">
                                <img style="display: block;" alt="" width="240" height="237" border="0"
                                     src="<?= $template->makeAbsUrl($data['MAIN_SMALL_BANNERS'][2]['IMG']); ?>">
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height: 15px;"></td>
                </tr>
                </tbody>
            </table>

            <?php if (is_array($data['SALE_BANNERS']) && !empty($data['SALE_BANNERS'])): ?>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tbody>

                    <tr>
                        <td>
                            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                                <tbody>
                                <tr>
                                    <td style="height: 30px;"></td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="line-height: normal; text-align: center; padding-bottom: 15px" colspan="2">
                            <p style="font: 30px Arial, sans-serif; margin: 0; color: #f04374;">Выгодные предложения</p>
                        </td>
                    </tr>

                    <tr>
                        <td rowspan="2" style="padding: 0 15px 0 0">
                            <a href='<?= $template->getResponseLink($data['SALE_BANNERS']['BIG']['URL']); ?>'
                               style="display: block;">
                                <img style="display: block;" alt="" width="495" height="305" border="0"
                                     src="<?= $template->makeAbsUrl($data['SALE_BANNERS']['BIG']['IMG']); ?>">
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                                <tbody>
                                <tr>
                                    <td>
                                        <a href='<?= $template->getResponseLink($data['SALE_BANNERS']['SMALL_TOP']['URL']); ?>'
                                           style="display: block;">
                                            <img style="display: block;" alt="" width="240" height="145" border="0"
                                                 src="<?= $template->makeAbsUrl($data['SALE_BANNERS']['SMALL_TOP']['IMG']); ?>">
                                        </a>
                                    </td>
                                </tr>

                                <tr>
                                    <td height="15"></td>
                                </tr>

                                <tr>
                                    <td>
                                        <a href='<?= $template->getResponseLink($data['SALE_BANNERS']['SMALL_BOTTOM']['URL']); ?>'
                                           style="display: block;">
                                            <img style="display: block;" alt="" width="240" height="145" border="0"
                                                 src="<?= $template->makeAbsUrl($data['SALE_BANNERS']['SMALL_BOTTOM']['IMG']); ?>">
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height: 15px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td>
                        <a href='<?= $template->getResponseLink($data['PROMO_BANNER']['URL']); ?>'
                           style="display: block;">
                            <img style="display: block;" alt="" width="750" height="76" border="0"
                                 src="<?= $template->makeAbsUrl($data['PROMO_BANNER']['IMG']); ?>">
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height: 15px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td>
                        <a href='<?= $template->getResponseLink($data['MAIN_SMALL_BANNERS2'][0]['URL']); ?>'
                           style="display: block;">
                            <img style="display: block;" alt="" width="240" height="240" border="0"
                                 src="<?= $template->makeAbsUrl($data['MAIN_SMALL_BANNERS2'][0]['IMG']); ?>">
                        </a>
                    </td>

                    <td style="padding: 0 15px">
                        <a href='<?= $template->getResponseLink($data['MAIN_SMALL_BANNERS2'][1]['URL']); ?>'
                           style="display: block;">
                            <img style="display: block;" alt="" width="240" height="240" border="0"
                                 src="<?= $template->makeAbsUrl($data['MAIN_SMALL_BANNERS2'][1]['IMG']); ?>">
                        </a>
                    </td>

                    <td>
                        <a href='<?= $template->getResponseLink($data['MAIN_SMALL_BANNERS2'][2]['URL']); ?>'
                           style="display: block;">
                            <img style="display: block;" alt="" width="240" height="240" border="0"
                                 src="<?= $template->makeAbsUrl($data['MAIN_SMALL_BANNERS2'][2]['IMG']); ?>">
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height: 15px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                            <tbody>
                            <tr>
                                <td style="height: 30px;"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="line-height: normal; text-align: center; padding-bottom: 15px" colspan="3">
                        <p style="font: 30px Arial, sans-serif; margin: 0; color: #f04374;">Цена только сегодня</p>
                        <p style="font: 18px/24px Arial, sans-serif; margin: 0; color: #57514b;">Каждый день мы
                            представляем 1 товар по супер-цене.</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href='<?= $template->getResponseLink($data['CTS_BANNER']['URL']); ?>'
                           style="display: block;">
                            <img style="display: block;" alt="" width="750" border="0"
                                 src="<?= $template->makeAbsUrl($data['CTS_BANNER']['IMG']); ?>">
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height:15px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
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
                        <p style="font:28px Arial, sans-serif;margin:0;color:#ff187e">Хиты продаж</p>
                    </td>
                </tr>

                <tr>
                    <?php foreach (array_chunk($data['SALE'], 3) as $row): ?>
                        <table cellpadding="10" cellspacing="0" border="0" style="width:100%; text-align: center">
                            <tbody>
                            <tr>
                                <?php foreach ($row as $product):
                                    $title = '[' . $product->relatedPropertiesModel->getAttribute('LOT_NUM') . '] ' . $product->getLotName();
                                    ?>
                                    <td style="font: 12px Arial, sans-serif; text-align: center;">
                                        <a href='<?= $template->getResponseLink($product->url); ?>' target="_blank">
                                            <img src='<?= $template->makeAbsUrl(\Yii::$app->imaging->thumbnailUrlOnRequest($product->image->src, new Thumbnail([
                                                'w' => 230,
                                                'h' => 230,
                                            ]))); ?>'
                                                 alt="<?= $title; ?>"
                                                 width="230"
                                                 height="230">
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
                                                        <span style="font:21px Arial, sans-serif;color:#ff187e">
                                                            <?= $shopProduct->getBasePriceMoney(); ?> руб.
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="font:21px Arial, sans-serif;color:#ff187e">
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
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                            <tbody>
                            <tr>
                                <td style="border: none; border-top: 7px solid #f04374; padding-bottom: 10px"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
            <!---->
        </td>
    </tr>

    <tr>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height:34px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="color: #4c4c4c; font-family: Arial, sans-serif; font-size: 14px; line-height: 18px; text-align: center;">
                        Телеканал Shop&Show<br>Интересно смотреть, удобно выбирать, выгодно заказывать.
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height:15px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;" align="center">
                <tbody>
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" border="0" align="center">
                            <tbody>
                            <tr>
                                <td style="padding: 0 13px;">
                                    <a href="<?= $template->getResponseLink(ABS_URL); ?>">
                                        <img style="border: none;" src="<?= ABS_IMG_PATH; ?>/social_link_1.png" alt="">
                                    </a>
                                </td>

                                <td style="padding: 0 13px;">
                                    <a href="<?= $template->getResponseLink('http://vk.com/shopandshow'); ?>">
                                        <img style="border: none;" src="<?= ABS_IMG_PATH; ?>/social_link_2.png" alt="">
                                    </a>
                                </td>

                                <td style="padding: 0 13px;">
                                    <a href="<?= $template->getResponseLink('http://ok.ru/shopandshow'); ?>">
                                        <img style="border: none;" src="<?= ABS_IMG_PATH; ?>/social_link_3.png" alt="">
                                    </a>
                                </td>

                                <td style="padding: 0 13px;">
                                    <a href="<?= $template->getResponseLink('http://www.youtube.com/channel/UC3ZSro00SmKj2DzrY0OPwbQ'); ?>">
                                        <img style="border: none;" src="<?= ABS_IMG_PATH; ?>/social_link_4.png" alt="">
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height:36px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="20" cellspacing="0" border="0" style="width:100%; height: 130px; background: #F2F2F2">
                <tbody>
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                            <tbody>
                            <tr>
                                <td style="padding-right: 20px;">
                                    <img src="<?= ABS_IMG_PATH; ?>/letter_footer_1.png" alt="">
                                </td>

                                <td>
                                    <p style="font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; color: #4c4c4c;">
                                        Не отвечайте на это письмо!
                                        По всем вопросам Вы можете написать
                                        на <a href="mailto:clients@shopandshow.ru" style="color: #256aa3;">clients@shopandshow.ru</a>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>

                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                            <tbody>
                            <tr>
                                <td style="padding-right: 20px;">
                                    <img src="<?= ABS_IMG_PATH; ?>/letter_footer_2.png" alt="">
                                </td>

                                <td>
                                    <p style="font-family: Arial, sans-serif; font-size: 14px; line-height: 20px; color: #4c4c4c;">
                                        Вы получили это письмо,
                                        потому что подписаны на рассылку
                                        интернет-магазина <a href='<?= $template->getResponseLink(ABS_URL); ?>'
                                                             style="color: #256aa3;">shopandshow.ru</a>
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td>
                        <p style="margin: 30px 0 34px; font-family: Arial, sans-serif; font-size: 11px; line-height: 16px; color: #4c4c4c; text-align: center">
                            Данное письмо не является офертой. Все цены действительны на момент совершения рассылки.<br>
		                        Общество с ограниченной ответственностью «МаркетТВ», ОГРН: 1137746389505<br>
                            Фактический/Юридический адрес: Российская Федерация, 109029, город Москва,<br>
                            Сибирский проезд, дом 2, строение 10. Телефон: <a href="tel:88003016010"
                                                                              style="color: #256aa3; text-decoration-style: dashed;">8
                                (800) 301-60-10</a>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td style="height:5px;"></td>
    </tr>
    </tbody>
</table>
</body>
</html>
