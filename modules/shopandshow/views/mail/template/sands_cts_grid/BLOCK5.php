<?php
/** @var \modules\shopandshow\models\shares\SsShare[] $banners */
/** @var \modules\shopandshow\components\mail\template\SandsCtsGrid $template */
?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td style="padding-right: 14px">
            <a href="<?= $template->getResponseLink($banners['BANNER_5_1']['URL']); ?>" style="display: block;">
                <img border="0" style="display: block; width: 343px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_5_1']['IMG']); ?>">
            </a>
        </td>

        <td>
            <a href="<?= $template->getResponseLink($banners['BANNER_5_2']['URL']); ?>" style="display: block;">
                <img border="0" style="display: block; width: 343px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_5_2']['IMG']); ?>">
            </a>
        </td>
    </tr>
    </tbody>
</table>

<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td style="height: 14px;"></td>
    </tr>
    </tbody>
</table>

<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td>
            <a href="<?= $template->getResponseLink($banners['BANNER_5_3']['URL']); ?>"
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_5_3']['IMG']); ?>">
            </a>
        </td>

        <td style="padding: 0 14px">
            <a href="<?= $template->getResponseLink($banners['BANNER_5_4']['URL']); ?>"
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_5_4']['IMG']); ?>">
            </a>
        </td>

        <td>
            <a href="<?= $template->getResponseLink($banners['BANNER_5_5']['URL']); ?>"
               style="display: block;">
                <img border="0" style="display: block; width: 224px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_5_5']['IMG']); ?>">
            </a>
        </td>
    </tr>
    </tbody>
</table>