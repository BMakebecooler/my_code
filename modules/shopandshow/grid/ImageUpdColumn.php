<?php
namespace modules\shopandshow\grid;

use common\thumbnails\Thumbnail;
use skeeks\cms\grid\ImageColumn as SxImageColumn;


/**
 * Class ImageUpdColumn
 */
class ImageUpdColumn extends SxImageColumn
{
    public $thumbConfig = [
        'w' => 50,
        'h' => 50,
    ];

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->relationName && $file = $model->{$this->relationName})
        {
            $this->thumbConfig[Thumbnail::NO_CACHE_PARAM] = $file->updated_at;

            $src            = \Yii::$app->imaging->getImagingUrl($file->src, new Thumbnail($this->thumbConfig));
            $originalSrc    = sprintf('%s?%s=%s', $file->src,Thumbnail::NO_CACHE_PARAM, $file->updated_at);
        } else
        {
            $src            = \Yii::$app->cms->noImageUrl;
            $originalSrc    = $src;
        }


        return "<a href='" . $originalSrc . "' class='sx-fancybox sx-img-link-hover' title='Увеличить' data-pjax='0'>
                    <img src='" . $src . "' style='width: " . $this->maxWidth . "px;' />
                </a>";
    }
}