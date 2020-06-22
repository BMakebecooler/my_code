<?php
/** @var \modules\shopandshow\widgets\grid\Block9Widget $widget */
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
                    $imageWidth = 224;
                    ?>
                    <?php
                    $imageSrc = $widget->image_id_0 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_0)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td valign="top" style="font-size: 16px; line-height: 22px;">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[0] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[0]; ?>" style="display: block;">
                        </a>
                    </td>

                    <?php
                    $imageSrc = $widget->image_id_1 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_1)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td valign="top" style="font-size: 16px; line-height: 22px; padding: 0 14px;">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[1] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[1]; ?>" style="display: block;">
                        </a>
                    </td>

                    <?php
                    $imageSrc = $widget->image_id_2 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_2)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td valign="top" style="font-size: 16px; line-height: 22px;">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[2] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[2]; ?>" style="display: block;">
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
