<?php
/** @var \modules\shopandshow\models\shares\SsShare[] $banners */
/** @var \modules\shopandshow\components\mail\template\SandsCtsGrid $template */
?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td style="padding-right: 14px" rowspan="2">
            <a href='<?= $template->getResponseLink($banners['BANNER_4_1']['URL']); ?>'
               style="display: block;">
                <img border="0" style="display: block; width: 343px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_4_1']['IMG']); ?>">
            </a>
        </td>

        <td style="padding-bottom: 14px">
            <a href='<?= $template->getResponseLink($banners['BANNER_4_2']['URL']); ?>'
               style="display: block;">
                <img border="0" style="display: block; width: 343px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_4_2']['IMG']); ?>">
            </a>
        </td>
    </tr>

    <tr>
        <td>
            <a href='<?= $template->getResponseLink($banners['BANNER_4_3']['URL']); ?>'
               style="display: block;">
                <img border="0" style="display: block; width: 343px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_4_3']['IMG']); ?>">
            </a>
        </td>
    </tr>
    </tbody>
</table>