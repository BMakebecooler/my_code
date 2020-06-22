<?php
/** @var \modules\shopandshow\models\shares\SsShare[] $banners */
/** @var \modules\shopandshow\components\mail\template\SandsCtsGrid $template */

// для него нет новых размеров
?>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tbody>
    <tr>
        <td style="padding-right: 14px">
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom: 14px;">
                <tr>
                    <td style="padding-right: 14px;">
                        <a href='<?= $template->getResponseLink($banners['BANNER_11_1']['URL']); ?>'
                           style="display: block;">
                            <img border="0" style="display: block; width: 235px; height: 178px;"
                                 src="<?= $template->makeAbsUrl($banners['BANNER_11_1']['IMG']); ?>">
                        </a>
                    </td>

                    <td>
                        <a href='<?= $template->getResponseLink($banners['BANNER_11_2']['URL']); ?>'
                           style="display: block;">
                            <img border="0" style="display: block; width: 313px; height: 178px;"
                                 src="<?= $template->makeAbsUrl($banners['BANNER_11_2']['IMG']); ?>">
                        </a>
                    </td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="padding-right: 14px;">
                        <a href='<?= $template->getResponseLink($banners['BANNER_11_3']['URL']); ?>'
                           style="display: block;">
                            <img border="0" style="display: block; width: 313px; height: 178px;"
                                 src="<?= $template->makeAbsUrl($banners['BANNER_11_3']['IMG']); ?>">
                        </a>
                    </td>

                    <td>
                        <a href='<?= $template->getResponseLink($banners['BANNER_11_4']['URL']); ?>'
                           style="display: block;">
                            <img border="0" style="display: block; width: 235px; height: 178px;"
                                 src="<?= $template->makeAbsUrl($banners['BANNER_11_4']['IMG']); ?>">
                        </a>
                    </td>
                </tr>
            </table>
        </td>

        <td>
            <a href='<?= $template->getResponseLink($banners['BANNER_11_5']['URL']); ?>'
               style="display: block;">
                <img border="0" style="display: block; width: 172px; height: 371px;"
                     src="<?= $template->makeAbsUrl($banners['BANNER_11_5']['IMG']); ?>">
            </a>
        </td>
    </tr>
    </tbody>
</table>