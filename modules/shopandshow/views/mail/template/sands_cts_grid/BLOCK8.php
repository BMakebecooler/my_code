<?php
/** @var \modules\shopandshow\models\shares\SsShare[] $banners */
/** @var \modules\shopandshow\components\mail\template\SandsCtsGrid $template */
?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td>
            <a href="<?= $template->getResponseLink($banners['BANNER_8']['URL']); ?>" style="display: block;">
                <img border="0" style="display: block; width: 700px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_8']['IMG']); ?>">
            </a>
        </td>
    </tr>
    </tbody>
</table>