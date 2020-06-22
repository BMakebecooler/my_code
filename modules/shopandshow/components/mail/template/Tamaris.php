<?php

namespace modules\shopandshow\components\mail\template;

use common\thumbnails\CropAndResize;
use common\thumbnails\Thumbnail;
use modules\shopandshow\components\mail\BaseTemplate;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopContentElement;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\shop\models\ShopViewedProduct;

class Tamaris extends BaseTemplate
{
    const IMAGE_WIDTH = 750;

    public $viewFile = '@modules/shopandshow/views/mail/template/tamaris';

    /** @var SsShare[] $banners */
    private $banners = [];

    public function init()
    {
        parent::init();

        /** @var SsShare[] $banners */
        $this->banners = SsShare::find()
            ->andWhere(['between', 'begin_datetime', $this->period_begin, $this->period_end])
            ->andWhere(['not', ['active' => Cms::BOOL_N]])
            ->all();

        if (empty($this->banners)) {
            throw new \Exception('Ни одного баннера на указанную дату не найдено');
        }

        $this->utm = 'utm_source=email&utm_medium=email_tamaris&utm_campaign='.date('Ymd');

        $this->data['PROMO_BANNER2'] = [
            'URL' => '/promo/828114-27_09_17_obuv-tamaris/',
            'IMG' => '/v2/common/img/sands_cts/tamaris-27_09_17.jpg',
        ];
    }


    private function getBannersByTypes($types, $count = false, $resize = false, $resizeParams = [])
    {
        if (!is_array($types)) {
            $types = [$types];
        }
        $banners = array_filter($this->banners, function ($item) use ($types) {
            return in_array($item['banner_type'], $types);
        });

        if($count && sizeof($banners) != $count) {
            throw new \Exception("Найдено баннеров ".sizeof($banners)."/".$count." для типа ".join(',', $types));
        }

        $result = [];
        foreach ($banners as $banner) {
            $result[] = $this->prepareBanner($banner, $resize, $resizeParams);
        }

        if($count == 1) $result = reset($result);

        return $result;
    }

    private function prepareBanner(SsShare $banner, $resize = true, $resizeParams = [])
    {
        $url = $banner->getUrl();
        if ($url instanceof \skeeks\cms\helpers\UrlHelper) {
            $url = $url->toString();
        }

        $image = $banner->image->src;
        if ($banner->banner_type == SsShare::BANNER_TYPE_SANDS_PROMO_CTS2) {
            $image = $this->getCropUrl($banner->image->src);
        }
        elseif ($resize) {
            $image = $this->getThumbnailUrl($banner->image->src, $banner->image->rootSrc, $resizeParams);
        }

        $result = [];
        $result['URL'] = $url;
        $result['IMG'] = $image;

        return $result;
    }

    private function getThumbnailUrl($fileSrc, $filePath, $resizeParams = [])
    {
        if ($resizeParams) {
            $w = $resizeParams['width'];
            $h = $resizeParams['height'];
        } else {
            $fileInfo = getimagesize($filePath);
            $delta = ($fileInfo[0] / $fileInfo[1]);
            $w = self::IMAGE_WIDTH;
            $h = round($w / $delta);
        }

        return \Yii::$app->imaging->thumbnailUrlOnRequest(
            $fileSrc,
            new Thumbnail([
                'w' => $w,
                'h' => $h,
            ])
        );
    }

    private function getCropUrl($fileSrc)
    {
        return \Yii::$app->imaging->thumbnailUrlOnRequest(
            $fileSrc,
            new CropAndResize([
                'widthCrop' => 1180,
                'widthResize' => 750
            ])
        );
    }
}