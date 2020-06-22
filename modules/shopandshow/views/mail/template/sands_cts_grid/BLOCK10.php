<?php
/** @var \modules\shopandshow\models\shares\SsShare[] $banners */
/** @var \modules\shopandshow\components\mail\template\SandsCtsGrid $template */
?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tr>
        <td style="padding-bottom: 14px;">
            <a href='<?= $template->getResponseLink($banners['BANNER_10_1']['URL']); ?>'
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_10_1']['IMG']); ?>">
            </a>
        </td>

        <td rowspan="2" style="padding: 0 14px">
            <a href='<?= $template->getResponseLink($banners['BANNER_10_3']['URL']); ?>'
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_10_3']['IMG']); ?>">
            </a>
        </td>

        <td rowspan="2">
            <a href='<?= $template->getResponseLink($banners['BANNER_10_4']['URL']); ?>'
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_10_4']['IMG']); ?>">
            </a>
        </td>
    </tr>

    <tr>
        <td>
            <a href='<?= $template->getResponseLink($banners['BANNER_10_2']['URL']); ?>'
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_10_2']['IMG']); ?>">
            </a>
        </td>
    </tr>
</table>