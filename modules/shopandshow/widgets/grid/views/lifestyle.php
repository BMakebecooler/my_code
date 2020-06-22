<?php
/** @var \modules\shopandshow\widgets\grid\LifestyleWidget $widget */
?>
<table border="0" cellpadding="0" cellspacing="0" width="700">
    <?= $this->render('_header', [
        'widget' => $widget
    ])?>

    <tr>
        <td valign="top" style="padding-top: 15px; padding-bottom: 21px;">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <?php
                $imageWidth = 205;
                $index = 0;
                $rowCount = ceil($widget->expertCount / 3);
                for ($row = 0; $row < $rowCount; $row++): ?>
                    <tr>
                        <?php for ($col = 0; $col < 3; $col++): ?>
                            <?php
                            $imageAttr = 'image_id_' . $index;
                            $imageSrc = $widget->{$imageAttr} ? \skeeks\cms\models\StorageFile::findOne($widget->{$imageAttr})->src : '';
                            if (!$imageSrc) continue;

                            $imageSrc = \Yii::$app->imaging->thumbnailUrlOnRequest($imageSrc, new \common\thumbnails\Thumbnail(['w' => $imageWidth, 'h' => 0]));

                            ?>
                            <td valign="top"
                                style="font-size: 16px; line-height: 22px; <?= $col == 1 ? 'padding:0 43px 0 42px;' : ''; ?>">
                                <a href="<?= $widget->getResponseLink($widget->imageUrl[$index]); ?>" target="_blank"
                                   style="display: block;">
                                    <img width="<?= $imageWidth; ?>" src="<?= $widget->makeAbsUrl($imageSrc); ?>"
                                         alt="<?= $widget->imageTitle[$index]; ?>" style="display: block;">
                                </a>

                                <?php if (!empty($widget->expertTitle[$index]) || !empty($widget->expertFio[$index])): ?>
                                    <p style="margin: 0 0 14px; padding: 11px 14px; background-color: #f4f4f4; font-size: 14px; line-height: 22px;">
                                        <?= $widget->expertTitle[$index]; ?><br>
                                        <b><?= $widget->expertFio[$index]; ?></b>
                                    </p>
                                <?php endif; ?>

                                <a href="<?= $widget->getResponseLink($widget->expertLinkUrl[$index]); ?>"
                                   target="_blank"
                                   style="color: #256aa3; font-size: 14px; line-height: 22px; text-decoration: underline;">
                                    <?= $widget->expertLinkTitle[$index]; ?>
                                </a>
                            </td>
                            <?php $index++; ?>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </table>
        </td>
    </tr>
</table>
