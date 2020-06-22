<?php
use modules\shopandshow\models\shares\SsShare;

use yii\helpers\Html;
use common\helpers\ArrayHelper;
use modules\shopandshow\models\shares\SsShareType;

/** @var SsShare $banner */
/** @var integer $w */
/** @var integer $h */
?>
<? if ($banner instanceof SsShare): ?>
    <a class="banner-item" href="<?= $banner->getUrl(); ?>" target="_blank" title="<?= $banner->name; ?>">
        <img border="0" style="display: block; width: <?= $w; ?>px; height: <?= $h; ?>px;"
             src="<?= $banner->image ? $banner->image->src : "" ?>?upd=<?=$banner->updated_at;?>" alt="<?= $banner->name; ?>">
    </a>
    <?

    preg_match("/BANNER_(\d+)_(\d+)/i", $banner->banner_type, $block_matches);
		$bannerBlock = !empty($block_matches[1]) ? $block_matches[1] : 0;
		$bannerBlockTypeNum = !empty($block_matches[2]) ? $block_matches[2] : 0;

		if ($bannerBlock){
            echo Html::dropDownList(
                "SsShare[{$banner->id}][banner_type]",
                $banner->banner_type,
                ArrayHelper::map(SsShareType::find()->andWhere(['LIKE', 'code', "_{$bannerBlock}_"])->all(), 'code', 'description'),
                [
                        'class' => 'form-control banner-type-select',
                        'data-prev-val' => $banner->banner_type,
                        'title' => 'Тип баннера (позиция по горизонтали)'
                ]
            );
		}
    ?>
<?php else: ?>
    <a class="banner-item" href="#" target="_blank" title="">
        <span style="display: block; width: <?= $w; ?>px; height: <?= $h; ?>px;">Баннер не привязан</span>
    </a>
<?php endif; ?>