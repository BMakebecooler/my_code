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
                <td style="padding-right: 14px">
                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%; margin-bottom: 14px;">
                        <tr>
                            <?php
                            $imageWidth = 218;
                            $imageSrc = $widget->image_id_0 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_0)->src : '';
                            $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                            ?>
                            <td style="padding-right: 14px;">
                                <a href="<?= $widget->getResponseLink($widget->imageUrl[0] ?: ''); ?>" target="_blank"
                                   style="display: block;">
                                    <img border="0" width="<?= $imageWidth; ?>"
                                         src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                         alt="<?= $widget->imageTitle[0]; ?>" style="display: block;">
                                </a>
                            </td>

                            <?php
                            $imageWidth = 295;
                            $imageSrc = $widget->image_id_1 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_1)->src : '';
                            $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                            ?>
                            <td>
                                <a href="<?= $widget->getResponseLink($widget->imageUrl[1] ?: ''); ?>" target="_blank"
                                   style="display: block;">
                                    <img border="0" width="<?= $imageWidth; ?>"
                                         src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                         alt="<?= $widget->imageTitle[1]; ?>" style="display: block;">
                                </a>
                            </td>
                        </tr>
                    </table>

                    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                        <tr>
                            <?php
                            $imageWidth = 295;
                            $imageSrc = $widget->image_id_2 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_2)->src : '';
                            $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                            ?>
                            <td style="padding-right: 14px;">
                                <a href="<?= $widget->getResponseLink($widget->imageUrl[2] ?: ''); ?>" target="_blank"
                                   style="display: block;">
                                    <img border="0" width="<?= $imageWidth; ?>"
                                         src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                         alt="<?= $widget->imageTitle[2]; ?>" style="display: block;">
                                </a>
                            </td>

                            <?php
                            $imageWidth = 218;
                            $imageSrc = $widget->image_id_3 ? \skeeks\cms\models\StorageFile::findOne($widget->image_id_3)->src : '';
                            $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));
                            ?>
                            <td>
                                <a href="<?= $widget->getResponseLink($widget->imageUrl[3] ?: ''); ?>" target="_blank"
                                   style="display: block;">
                                    <img border="0" width="<?= $imageWidth; ?>"
                                         src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                         alt="<?= $widget->imageTitle[3]; ?>" style="display: block;">
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>

                <?php
                $imageWidth = 160;
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
            </table>
        </td>
    </tr>
</table>
