<?php
/** @var \modules\shopandshow\widgets\grid\Block5Widget $widget */
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
                    $imageWidth = 343;
                    $imageSrc = $widget->image_id_0 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_0)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td style="padding-right: 14px">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[0] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img border="0" width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[0]; ?>" style="display: block;">
                        </a>
                    </td>

                    <?php
                    $imageWidth = 343;
                    $imageSrc = $widget->image_id_1 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_1)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td>
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[1] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img border="0" width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[1]; ?>" style="display: block;">
                        </a>
                    </td>
                </tr>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tbody>
                <tr>
                    <td style="height: 14px;"></td>
                </tr>
                </tbody>
            </table>

            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
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

                    <?php
                    $imageWidth = 224;
                    $imageSrc = $widget->image_id_3 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_3)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td style="padding: 0 14px">
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[3] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img border="0" width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[3]; ?>" style="display: block;">
                        </a>
                    </td>

                    <?php
                    $imageWidth = 224;
                    $imageSrc = $widget->image_id_4 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_4)->src : '';
                    $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                    ?>
                    <td>
                        <a href="<?= $widget->getResponseLink($widget->imageUrl[4] ?: ''); ?>" target="_blank"
                           style="display: block;">
                            <img border="0" width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                 alt="<?= $widget->imageTitle[4]; ?>" style="display: block;">
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
