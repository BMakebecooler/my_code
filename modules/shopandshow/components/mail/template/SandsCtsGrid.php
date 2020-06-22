<?php

namespace modules\shopandshow\components\mail\template;

use common\thumbnails\BannerCts;
use common\thumbnails\BannerText;
use common\thumbnails\BaseThumbnail;
use common\thumbnails\CropAndResize;
use common\thumbnails\Thumbnail;
use modules\shopandshow\components\mail\BaseTemplate;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shares\SsShareSchedule;
use modules\shopandshow\models\shop\ShopContentElement;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\shop\models\ShopViewedProduct;

class SandsCtsGrid extends BaseTemplate
{
    const IMAGE_WIDTH = 700;
    const SALE_ITEMS_IN_SUBSCRIPTION = 9;

    public $viewFile = '@modules/shopandshow/views/mail/template/sands_cts_grid';

    /** @var SsShare[] $banners */
    private $banners = [];

    public function init()
    {
        parent::init();

        /** @var SsShare[] $banners */
        $this->banners = SsShare::find()
            //->andWhere(['between', 'begin_datetime', $this->period_begin, $this->period_end])
            ->andWhere(['<', 'begin_datetime', $this->begin_date])
            ->andWhere(['>', 'end_datetime', $this->begin_date])
            ->andWhere(['not', ['active' => Cms::BOOL_N]])
            // в рассылке 13 баннер (слайдер) пока не используется
            ->andWhere(['!=', 'banner_type', 'BANNER_SLIDER'])
            ->orderBy('id ASC')
            ->indexBy('id')
            ->all();

        if (empty($this->banners)) {
            throw new \Exception('Ни одного баннера на указанную дату не найдено');
        }

        $this->data['CTS_BANNER'] = $this->getCtsbanner('MAIN_CTS');
        $this->data['PROMO_BANNER2'] = $this->getBannersByTypes('SANDS_PROMO_CTS2', 1, true);
        $this->data['PROMO_BANNER'] = $this->getBannersByTypes('SANDS_PROMO_CTS', false, true);

        // в рассылке 13 баннер (слайдер) пока не используется
        $schedules = SsShareSchedule::findByDate($this->period_end)
            ->andWhere(['!=', 'block_type', 'BLOCKSLIDER'])
            ->all();

        $this->data['GRID'] = [];
        foreach ($schedules as $schedule) {
            $this->data['GRID'][] = ['schedule' => $schedule, 'banners' => $this->getBannersBySchedule($schedule)];
        }

        $this->data["SALE"] = $this->getBestSale();
    }

    private function getBestSale()
    {
        // не выводить товары
        if ($this->tree_id == -1) {
            return null;
        }

        if ($this->tree_id > 0) {
            $tree = \common\models\Tree::findOne($this->tree_id);
        } else {
            $tree = $this->getCtsTree();
            $this->tree_id = $tree->id;
        }

        $treeIds = $tree->getDescendants()->select(['id'])->indexBy('id')->asArray()->all();
        $treeIds = array_keys($treeIds);
        $treeIds[] = $tree->id;

        foreach ($treeIds as $key => $treeId) {
            if (!$treeId) {
                unset($treeIds[$key]);
            }
        }

        $query = ShopContentElement::find()
            ->select('cms_content_element.*')
            ->addSelect(new \yii\db\Expression("COUNT(shop_viewed_product.id) as viewed"))
            ->leftJoin(CmsContentElementProperty::tableName() . ' AS not_public_value',
                "not_public_value.element_id = cms_content_element.id AND not_public_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
                ")
            ->leftJoin(ShopViewedProduct::tableName() . ' as shop_viewed_product',
                "shop_viewed_product.shop_product_id = cms_content_element.id AND shop_viewed_product.updated_at > " . $this->viewed_at)
            ->active()
            ->andWhere('not_public_value.value IS NULL')
            ->andWhere(['cms_content_element.content_id' => PRODUCT_CONTENT_ID])
            ->groupBy('cms_content_element.id')
            ->orderBy(['viewed' => SORT_DESC])
            ->limit(self::SALE_ITEMS_IN_SUBSCRIPTION);

        ShopContentElement::catalogFilterQuery($query);

        if ($treeIds) {
            $query->andWhere(['cms_content_element.tree_id' => $treeIds]);
        }

        return $query->all();
    }

    private function getCtsTree()
    {
        $banners = array_filter($this->banners, function ($item) {
            return $item['banner_type'] == 'MAIN_CTS';
        });

        if (sizeof($banners) != 1) {
            throw new \Exception('Не удалось найти баннер ЦТС');
        }

        $banner = reset($banners);

        if (!$banner->bitrix_product_id) {
            throw new \Exception('Не удалось найти товар ЦТС');
        }

        $cmsContentElement = \common\lists\Contents::getContentElementByBitrixId($banner->bitrix_product_id, [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);
        if (!$cmsContentElement) {
            throw new \Exception('Не найден ЦТС товар по bitrix_id: ' . $banner->bitrix_product_id);
        }

        $tree = $cmsContentElement->cmsTree;
        if ($tree->level == 2) {
            return $tree;
        }

        while ($parent = $tree->parent) {
            $tree = $parent;
            if ($tree->level == 2) {
                break;
            }
        }

        return $tree;
    }

    private function getBannersByTypes($types, $count = false, $resize = false, $resizeParams = [])
    {
        if (!is_array($types)) {
            $types = [$types];
        }
        $banners = array_filter($this->banners, function ($item) use ($types) {
            return in_array($item['banner_type'], $types);
        });

        if ($count && sizeof($banners) != $count) {
            throw new \Exception("Найдено баннеров " . sizeof($banners) . "/" . $count . " для типа " . join(',', $types));
        }

        $result = [];
        foreach ($banners as $banner) {
            $result[] = $this->prepareBanner($banner, $resize, $resizeParams);
        }

        if (sizeof($banners) == 1) {
            $result = reset($result);
        }

        return $result;
    }

    private function getCtsbanner($types)
    {
        if (!is_array($types)) {
            $types = [$types];
        }
        $banners = array_filter($this->banners, function ($item) use ($types) {
            return in_array($item['banner_type'], $types);
        });

        if (sizeof($banners) == 0) {
            return []; //Что бы можно было отправлять рассылку по шаблону ЦТС без ЦТС
            throw new \Exception("Не найдены баннеры для типа " . join(',', $types));
        }

        $banner = reset($banners);

        return $this->prepareBanner($banner, true, []);
    }

    private function getBannersBySchedule($schedule)
    {
        $banners = array_filter($this->banners, function ($item) use ($schedule) {
            return $item->share_schedule_id == $schedule->id;
        });

        $result = [];
        foreach ($banners as $banner) {
            if (!isset($result[$banner->banner_type])) {
                $result[$banner->banner_type] = $this->prepareBanner($banner, true, $this->getResizeParamsByBannerType($banner->banner_type));
                unset($this->banners[$banner->id]);
            }
        }

        return $result;
    }

    private function getResizeParamsByBannerType($bannerType)
    {
        static $sizes = [
            'BANNER_1_1' => ['width' => 224/*, 'height' => 365*/],
            'BANNER_1_2' => ['width' => 224/*, 'height' => 365*/],
            'BANNER_1_3' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_1_4' => ['width' => 224/*, 'height' => 175*/],

            'BANNER_2_1' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_2_2' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_2_3' => ['width' => 462/*, 'height' => 365*/],

            'BANNER_3_1' => ['width' => 462/*, 'height' => 175*/],
            'BANNER_3_2' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_3_3' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_3_4' => ['width' => 224/*, 'height' => 365*/],

            'BANNER_4_1' => ['width' => 343/*, 'height' => 365*/],
            'BANNER_4_2' => ['width' => 343/*, 'height' => 175*/],
            'BANNER_4_3' => ['width' => 343/*, 'height' => 175*/],

            'BANNER_5_1' => ['width' => 343/*, 'height' => 240*/],
            'BANNER_5_2' => ['width' => 343/*, 'height' => 240*/],
            'BANNER_5_3' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_5_4' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_5_5' => ['width' => 224/*, 'height' => 175*/],

            'BANNER_6' => ['width' => 700/*, 'height' => 76*/],

            'BANNER_7_1' => ['width' => 224/*, 'height' => 237*/],
            'BANNER_7_2' => ['width' => 224/*, 'height' => 237*/],
            'BANNER_7_3' => ['width' => 224/*, 'height' => 237*/],

            'BANNER_8' => ['width' => 700/*, 'height' => 76*/],

            'BANNER_9_1' => ['width' => 224/*, 'height' => 237*/],
            'BANNER_9_2' => ['width' => 224/*, 'height' => 237*/],
            'BANNER_9_3' => ['width' => 224/*, 'height' => 237*/],

            'BANNER_10_1' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_10_2' => ['width' => 224/*, 'height' => 175*/],
            'BANNER_10_3' => ['width' => 224/*, 'height' => 365*/],
            'BANNER_10_4' => ['width' => 224/*, 'height' => 365*/],

            'BANNER_11_1' => ['width' => 235, 'height' => 178],
            'BANNER_11_2' => ['width' => 313, 'height' => 178],
            'BANNER_11_3' => ['width' => 313, 'height' => 178],
            'BANNER_11_4' => ['width' => 235, 'height' => 178],
            'BANNER_11_5' => ['width' => 172, 'height' => 371],

            'BANNER_12_1' => ['width' => 343/*, 'height' => 175*/],
            'BANNER_12_2' => ['width' => 343/*, 'height' => 175*/],
        ];

        return $sizes[$bannerType];
    }

    private function prepareBanner(SsShare $banner, $resize = true, $resizeParams = [])
    {
        $url = $banner->getUrl();

        $image = null;
        if ($banner->image) {
            $image = $banner->image->src;
            if ($banner->banner_type == SsShare::BANNER_TYPE_SANDS_PROMO_CTS2 || $banner->banner_type == SsShare::BANNER_TYPE_SANDS_PROMO_CTS) { //Поставить false если будет ГИФ рассылка
                $image = $this->getCropUrl($banner);
            } elseif ($resize) {
                $image = $this->getThumbnailUrl($banner, $resizeParams);
            }
        }

        $result = [];
        $result['URL'] = $url;
        $result['IMG'] = $image;

        return $result;
    }

    private function getThumbnailUrl(SsShare $banner, $resizeParams = [])
    {
        $fileSrc = $banner->image->src;
        $filePath = $banner->image->rootSrc;

        if ($resizeParams) {
            $w = $resizeParams['width'];

            if (isset($resizeParams['height'])) {
                $h = $resizeParams['height'];
            } else {
                $fileInfo = getimagesize($filePath);
                $delta = ($fileInfo[0] / $fileInfo[1]);
                $h = round($w / $delta);
            }
        } else {
            $fileInfo = getimagesize($filePath);
            $delta = ($fileInfo[0] / $fileInfo[1]);
            $w = self::IMAGE_WIDTH;
            $h = round($w / $delta);
        }

        $thumbnailParams = ['w' => $w, 'h' => $h, BaseThumbnail::NO_CACHE_PARAM => $banner->updated_at];

        if ($banner->isCts()) {
            return BannerCts::getShareImageWithText($banner, $thumbnailParams);
        }
        elseif ($banner->isWithText()) {
            return BannerText::getShareImageWithText($banner, $thumbnailParams);
        }

        return \Yii::$app->imaging->thumbnailUrlOnRequest(
            $fileSrc,
            new Thumbnail($thumbnailParams)
        );
    }

    private function getCropUrl(SsShare $banner)
    {
        return \Yii::$app->imaging->thumbnailUrlOnRequest(
            $banner->image->src,
            new CropAndResize([
                'widthCrop' => 1180,
                'widthResize' => 700,
                'upd' => $banner->updated_at
            ])
        );
    }
}