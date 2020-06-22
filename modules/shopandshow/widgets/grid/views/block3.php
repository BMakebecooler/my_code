<?php
/** @var \modules\shopandshow\widgets\grid\Block3Widget $widget */
?>
<table border="0" cellpadding="0" cellspacing="0" width="700">
    <?= $this->render('_header', [
        'widget' => $widget
    ])?>

    <tr>
        <td valign="top" style="padding-top: 15px; padding-bottom: 21px;">

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <?php
                    $imageWidth = 462;
                    $imageSrc = $widget->image_id_0 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_0)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td style="padding-bottom: 14px;" colspan="2">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[0] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img border="0" width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[0]; ?>" style="display: block;">
                        </a>
                    </td>

                    <?php
                    $imageWidth = 224;
                    $imageSrc = $widget->image_id_3 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_3)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td style="padding-left: 14px;" rowspan="2">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[3] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img border="0" width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[3]; ?>" style="display: block;">
                        </a>
                    </td>
                </tr>

                <tr>
                    <?php
                    $imageWidth = 224;
                    $imageSrc = $widget->image_id_1 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_1)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td style="padding-left: 14px;">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[1] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img border="0" width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[1]; ?>" style="display: block;">
                        </a>
                    </td>

                    <?php
                    $imageWidth = 224;
                    $imageSrc = $widget->image_id_2 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_2)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td>
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[2] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img border="0" width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[2]; ?>" style="display: block;">
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
