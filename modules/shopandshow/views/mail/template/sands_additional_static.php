<?php

/** @var array $data */
/** @var \modules\shopandshow\components\mail\BaseTemplate $template */

define('ABS_URL', $template->absUrl);
define('ABS_IMG_PATH', $template->absImgPath);

?>

<?= $this->render("@modules/shopandshow/views/mail/template/_header.php", ['data' => $data, 'template' => $template]); ?>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tbody>
        <tr>
            <td>
                <p style="text-indent: 1em; font-style: italic;">Главная акция дня!.<br>
                    Только для подписчиков 6 и 7 февраля мы даем эксклюзивную скидку по промокоду!<br>
                    Пройдите по ссылке и выберите понравившиеся товары в рубрике. В корзине введите в окошко ваш промокод и получите скидку.</p>
                <p style="text-indent: 1em; font-style: italic;">Срок действия промокода - 2 дня.<br>
                    Вы можете использовать только один промокод.<br>
                    Приятных покупок!</p>
            </td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td height="14"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td>
                <a href="<?= $template->getResponseLink('/promo/943043-06_02_18_ukrasheniya'); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $template->makeAbsUrl('/v2/common/img/newsletter/180206/7_2-1.jpg'); ?>">
                </a>
            </td>

            <td style="padding: 0 14px">
                <a href="<?= $template->getResponseLink('/promo/943044-06_02_18_moda'); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $template->makeAbsUrl('/v2/common/img/newsletter/180206/7_1-1.jpg'); ?>">
                </a>
            </td>

            <td>
                <a href="<?= $template->getResponseLink('/943045-06_02_18_dom'); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $template->makeAbsUrl('/v2/common/img/newsletter/180206/7_3-1.jpg'); ?>">
                </a>
            </td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td height="14"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td>
                <a href="<?= $template->getResponseLink('/promo/941726-06_02_18_obuv'); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $template->makeAbsUrl('/v2/common/img/newsletter/180206/7_1.jpg'); ?>">
                </a>
            </td>

            <td style="padding: 0 14px">
                <a href="<?= $template->getResponseLink('/promo/943026-06_02_18_kukhnya'); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $template->makeAbsUrl('/v2/common/img/newsletter/180206/7_2.jpg'); ?>">
                </a>
            </td>

            <td>
                <a href="<?= $template->getResponseLink('/promo/941723-06_02_18_krasota'); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $template->makeAbsUrl('/v2/common/img/newsletter/180206/7_3.jpg'); ?>">
                </a>
            </td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td height="14"></td>
        </tr>
    </table>

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
                <a href='<?= $template->getResponseLink('/catalog/moda/komplekty-odezhdy/930443-4315431-004315431/?bid=3939'); ?>'
                   style="display: block;">
                    <img style="display: block;" alt="" width="700" border="0"
                         src="<?= $template->makeAbsUrl('/uploads/all/0e/27/b0/0e27b076280ff9aca03bfe682693881e/sx-filter__common-thumbnails-Thumbnail/bae246f428327eb555baa34074594da4/sx-file.jpg?w=700&h=320&upd=1'); ?>">
                </a>
            </td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td height="22"></td>
        </tr>
    </table>

<?= $this->render("@modules/shopandshow/views/mail/template/_footer.php", ['data' => $data, 'template' => $template]); ?>