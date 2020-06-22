<?php
/** @var \modules\shopandshow\models\shares\SsShare[] $banners */
/** @var \modules\shopandshow\components\mail\template\SandsCtsGrid $template */

if (!isset($banners['BANNER_7_1'])) {
    return;
}

?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td>
            <a href="<?= $template->getResponseLink($banners['BANNER_7_1']['URL']); ?>"
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_7_1']['IMG']); ?>">
            </a>
        </td>

        <td style="padding: 0 14px">
            <a href="<?= $template->getResponseLink($banners['BANNER_7_2']['URL']); ?>"
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_7_2']['IMG']); ?>">
            </a>
        </td>

        <td>
            <a href="<?= $template->getResponseLink($banners['BANNER_7_3']['URL']); ?>"
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_7_3']['IMG']); ?>">
            </a>
        </td>
    </tr>
    </tbody>
</table>