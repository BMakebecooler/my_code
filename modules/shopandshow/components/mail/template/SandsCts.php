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

class SandsCts extends BaseTemplate
{
    const IMAGE_WIDTH = 750;
    const SALE_ITEMS_IN_SUBSCRIPTION = 9;

    public $viewFile = '@modules/shopandshow/views/mail/template/sands_cts';

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

        $this->data['CTS_BANNER'] = $this->getBannersByTypes('MAIN_CTS', 1, true);
        $this->data['PROMO_BANNER'] = $this->getBannersByTypes('SANDS_PROMO_CTS', 1, true);
        $this->data['PROMO_BANNER2'] = $this->getBannersByTypes('SANDS_PROMO_CTS2', 1, true); // поставить тру если нужна гиф рассылка
        $this->data['MAIN_SMALL_BANNERS'] = $this->getBannersByTypes(['MAIN_SMALL_EFIR', 'MAIN_SMALL_HIT'], 3, true, ['width' => 240, 'height' => 237]);
        $this->data['SALE_BANNERS'] = [
            'BIG' => $this->getBannersByTypes('MAIN_SITE_SALE_1', 1, true, ['width' => 495, 'height' => 305]),
            'SMALL_TOP' => $this->getBannersByTypes('MAIN_SITE_SALE_2', 1, true, ['width' => 240, 'height' => 145]),
            'SMALL_BOTTOM' => $this->getBannersByTypes('MAIN_SITE_SALE_3', 1, true, ['width' => 240, 'height' => 145]),
        ];
        $this->data['MAIN_SMALL_BANNERS2'] = $this->getBannersByTypes('MAIN_SMALL_EFIR_2', 3, true, ['width' => 240, 'height' => 240]);

        //$this->data['SALE_BANNERS_FOR_MAILING'] = $this->getBannersByTypes('BANNER_FOR_MAILING', false, true, ['width' => 750, 'height' => 384]);

        $this->data["SALE"] = $this->getBestSale();
    }

    private function getBestSale()
    {
        $query = ShopContentElement::find()
            ->select('cms_content_element.*')
            ->addSelect(new \yii\db\Expression("COUNT(shop_viewed_product.id) as viewed"))
            ->leftJoin(CmsContentElementProperty::tableName() . ' AS not_public_value',
                "not_public_value.element_id = cms_content_element.id AND not_public_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
                ")
            ->leftJoin(ShopViewedProduct::tableName() . ' as shop_viewed_product',
                "shop_viewed_product.shop_product_id = cms_content_element.id AND shop_viewed_product.updated_at > ".$this->viewed_at)
            ->active()
            ->andWhere('not_public_value.value IS NULL')
            ->andWhere(['cms_content_element.content_id' => PRODUCT_CONTENT_ID])
            ->groupBy('cms_content_element.id')
            ->orderBy(['viewed' => SORT_DESC])
            ->limit(self::SALE_ITEMS_IN_SUBSCRIPTION);

        ShopContentElement::catalogFilterQuery($query);

        if($this->tree_id) {
            $tree = \common\models\Tree::findOne($this->tree_id);
        }
        else {
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
        if(!$cmsContentElement) {
            throw new \Exception('Не найден ЦТС товар по bitrix_id: '.$banner->bitrix_product_id);
        }

        $tree = $cmsContentElement->cmsTree;
        if($tree->level == 2) return $tree;

        while($parent = $tree->parent) {
            $tree = $parent;
            if($tree->level == 2) break;
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
        if ($banner->banner_type == SsShare::BANNER_TYPE_SANDS_PROMO_CTS2) { //Поставить false если будет ГИФ рассылка
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