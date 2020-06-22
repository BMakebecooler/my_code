<?php
/** @var \modules\shopandshow\widgets\grid\CtsWidget $widget */

$imageSrc = $widget->image_id ? \skeeks\cms\models\StorageFile::findOne($widget->image_id)->src : '';
$imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => 700, 'h' => 0]));
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td valign="top">
            <?php if ($widget->header): // TODO: Утвердить и привести хэдер к такому же виду как и в остальных вьюшках ?>
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4;">
                <tr>
                    <td valign="top" style="padding: 15px 11px;">
                        <p style="margin: 0; font-size: 18px; line-height: 22px;"><?= $widget->header; ?></p>
                    </td>
                </tr>
            </table>
            <?php endif; ?>


            <?php if ($widget->timer): ?>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td valign="top">
                        <img src="<?= $widget->makeAbsUrl('/shopandshow/mail/timer/'); ?>" alt="В наличии по спец цене">
                    </td>
                </tr>
            </table>

            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="height: 5px;"></td>
                </tr>
            </table>
            <?php endif; ?>

            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #efefef;">
                <tr>
                    <td valign="top" align="center">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl); ?>" target="_blank" style="display: block;">
                            <img width="100%" src="<?= $widget->makeAbsUrl($imageSrc); ?>" alt="<?= $widget->imageTitle; ?>" style="display: block;">
                        </a>
                    </td>
                </tr>

                <?php if ($widget->description): ?>
                <tr>
                    <td valign="top" style="padding: 14px 10px 18px; color: #3b3937; font-size: 14px; line-height: 20px;">
                        <p style="margin: 0 0 4px;"><?= $widget->description; ?></p>
                        <?php if ($widget->descriptionColored): ?>
                        <p style="margin: 0; <?= $widget->descriptionColor ? 'color: '.$widget->descriptionColor.';' : ''; ?>"><?= $widget->descriptionColored; ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>

                <?php if ($widget->button): ?>
                <tr>
                    <td valign="top" align="center" style="padding: 0 0 15px;">
                        <a href="<?= $widget->getResponseLink($widget->buttonUrl); ?>" target="_blank" style="display: inline-block; width: 180px; height: 35px; background-color:  #970330; border-radius: 3px; color:  #ffffff; font-size: 18px; line-height: 35px; text-align: center; text-decoration: none;">
                            <?= $widget->buttonTitle; ?>
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
</table>
