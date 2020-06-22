<?php
/** @var \modules\shopandshow\models\shares\SsShare[] $banners */
/** @var \modules\shopandshow\components\mail\template\SandsCtsGrid $template */
?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td>
            <? if (($src = $template->makeAbsUrl($banners['BANNER_9_1']['IMG'])) && strlen($src) > 48): ?>
                <a href="<?= $template->getResponseLink($banners['BANNER_9_1']['URL']); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $src; ?>">
                </a>
            <? endif; ?>
        </td>

        <td style="padding: 0 14px">
            <? if (($src = $template->makeAbsUrl($banners['BANNER_9_2']['IMG'])) && strlen($src) > 48): ?>

                <a href="<?= $template->getResponseLink($banners['BANNER_9_2']['URL']); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $src; ?>">
                </a>
            <? endif; ?>
        </td>

        <td>
            <? if (($src = $template->makeAbsUrl($banners['BANNER_9_3']['IMG'])) && strlen($src) > 48): ?>
                <a href="<?= $template->getResponseLink($banners['BANNER_9_3']['URL']); ?>"
                   style="display: block;">
                    <img border="0" style="display: block; width: 224px;"
                         src="<?= $src; ?>">
                </a>
            <? endif; ?>
        </td>
    </tr>
    </tbody>
</table>