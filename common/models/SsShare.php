<?php

/**
 * @author Arkhipov Andrei <arhan89@gmail.com>
 * @copyright (c) K-Gorod
 * Date: 05.04.2019
 * Time: 15:29
 */

namespace common\models;


use common\helpers\ArrayHelper;
use common\helpers\Url;
use common\lists\Contents;
use common\lists\TreeList;
use common\models\cmsContent\CmsContentElement;
use common\models\generated\models\SsShares;
use common\models\query\SsSharesQuery;
use common\thumbnails\BannerCts;
use common\thumbnails\BannerCtsMobile;
use common\thumbnails\BannerText;
use common\thumbnails\BaseThumbnail;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\StorageFile;
use yii\db\Expression as DbExpression;

/**
 * Class SsShare
 * @package common\models
 *
 * @property $shareUrl
 */
class SsShare extends SsShares
{
    // связанные с cms_content_element атрибуты
    public $cce_image_id;
    public $cce_description;

    const CATALOG_SECTION_ACTION = 'CATALOG_SECTION_ACTION';

    const BANNER_TYPE_MAIN_SMALL_EFIR = 'MAIN_SMALL_EFIR';
    const BANNER_TYPE_MAIN_SMALL_EFIR_2 = 'MAIN_SMALL_EFIR_2';

    const BANNER_TYPE_MAIN_WIDE_1 = 'MAIN_WIDE_1';
    const BANNER_TYPE_MAIN_WIDE_2 = 'MAIN_WIDE_2';

    const BANNER_TYPE_MAIN_WIDE_MOBILE = 'MAIN_WIDE_MOBILE';

    const BANNER_TYPE_SANDS_PROMO_CTS = 'SANDS_PROMO_CTS';
    const BANNER_TYPE_SANDS_PROMO_CTS2 = 'SANDS_PROMO_CTS2';

    const BANNER_TYPE_CTS = 'MAIN_CTS';
    const BANNER_TYPE_CTS_MOBILE = 'MAIN_CTS_MOBILE';

    const BANNER_TYPE_MAIN_SITE_SALE_1 = 'MAIN_SITE_SALE_1';
    const BANNER_TYPE_MAIN_SITE_SALE_2 = 'MAIN_SITE_SALE_2';
    const BANNER_TYPE_MAIN_SITE_SALE_3 = 'MAIN_SITE_SALE_3';

    const BANNER_TYPE_ACTION_BANNER = 'ACTION_BANNER';

    const BANNER_TYPE_MAIN_WIDE_ROSTELECOM = 'ROSTELECOM';

    const BANNER_TYPE_EAR = 'ear';
    const BANNER_TYPE_POPUP = 'popup';
    const BANNER_TYPE_LABEL = 'label';

    const BANNER_PREVIEW_KEY = 'banner_date';

    const DEFAULT_LIMIT_MAIN_SLIDER = 5;
    const DEFAULT_LIMIT_CTS = 5;

    const BID_PARAM = 'bid';

    //Описания в бд так себе, так что запишем отдельно более понятно
    public static $bannersLabels = [
        self::BANNER_TYPE_MAIN_WIDE_1 => 'Главный баннер',
        self::BANNER_TYPE_MAIN_WIDE_MOBILE => 'Главный баннер мобильная версия',
        self::BANNER_TYPE_MAIN_WIDE_2 => 'Главный баннер',
        self::BANNER_TYPE_CTS => 'ЦТС баннер',
        self::BANNER_TYPE_CTS_MOBILE => 'ЦТС баннер моб. версия',
        self::BANNER_TYPE_MAIN_SITE_SALE_1 => 'Моб.версия',
        self::BANNER_TYPE_MAIN_SITE_SALE_2 => 'Моб.версия',
        self::BANNER_TYPE_MAIN_SITE_SALE_3 => 'Моб.версия',
    ];

    protected $shareUrl;

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(CmsContentElement::class, ['bitrix_id' => 'bitrix_product_id'])
            ->andWhere(['content_id' => [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]]);
    }

    /**
     * @return SsSharesQuery
     */
    public static function find()
    {
        return new SsSharesQuery(get_called_class());
    }

    /**
     * @param array $shares
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function normalizeForHomePageSlider(array $shares): array
    {
        $result = [];
        /** @var SsShare $share */
        foreach ($shares as $index => $share) {

                $imageSrc = $share->getImageSrc();
                if ($imageSrc) {
                    $result[] = [
                        'id' => $share->id,
                        'type' => $share->banner_type,
                        'url' => $share->getUrl(),
                        'img' => $imageSrc,
                        'linkOption' => [
                            'class' => 'image-item data-layer_click',
                            'data-datalayer' => \Yii::$app->dataLayer->normalizePromotionOnClick([
                                'id' => $share->id,
                                'name' => $share->name,
                                'creative' => $share->banner_type,
                            ])
                        ],
                    ];
                }
            }

        return $result;
    }


    /**
     * @return mixed
     */
    /*public function getImage()
    {
        return  Url::getCdnUrl().DIRECTORY_SEPARATOR.$this->getImageSrc();
    }*/

    /**
     * Получить путь до картинки
     * @return mixed
     */
    public function getImageSrc()
    {
        if (!$this->image_id) return null;

        $cdnUrl = Url::getCdnUrl() . '/';

        if (false && $this->isCts()) { //не используем более специфику ЦТС
            return BannerCts::getShareImageWithText($this, [], $cdnUrl);
        } elseif (false && $this->isCtsMobile()) { //не используем более специфику ЦТС
            return BannerCtsMobile::getShareImageWithText($this, [], $cdnUrl);
        } elseif ($this->isWithText()) {
            return BannerText::getShareImageWithText($this, [], $cdnUrl);
        }

        $src = ArrayHelper::getValue($this, 'image.src');

        return $src ? $cdnUrl . sprintf('%s?%s=%d', $src, BaseThumbnail::NO_CACHE_PARAM, $this->updated_at) : null;
    }

    public function isCts()
    {
        return $this->banner_type == self::BANNER_TYPE_CTS;
    }

    public function isCtsMobile()
    {
        return $this->banner_type == self::BANNER_TYPE_CTS_MOBILE;
    }

    public function isWithText()
    {
        return !empty($this->bitrix_product_id) && strpos($this->banner_type, 'BANNER_') === 0;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_id']);
    }

    private function getShareUrl()
    {
        $url = trim($this->url);

        if ($url) {
            $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . sprintf('%s=%s', self::BID_PARAM, $this->id);
        }

        return $url;
    }

    public function getUrl()
    {

//        return $this->getShareUrl();

        if ($this->shareUrl) {
            return $this->shareUrl;
        }

        switch ($this->banner_type) {
            case self::BANNER_TYPE_MAIN_SMALL_EFIR :
                return $this->shareUrl = $this->getUrlMainSmallEfir();

            case self::BANNER_TYPE_MAIN_WIDE_1 :
            case self::BANNER_TYPE_MAIN_WIDE_MOBILE :
            case self::BANNER_TYPE_SANDS_PROMO_CTS2:
            case self::BANNER_TYPE_MAIN_SITE_SALE_1 :
            case self::BANNER_TYPE_MAIN_SITE_SALE_2 :
            case self::BANNER_TYPE_MAIN_SITE_SALE_3 :
            case self::BANNER_TYPE_MAIN_WIDE_2 :
            case self::CATALOG_SECTION_ACTION :
            case self::BANNER_TYPE_MAIN_SMALL_EFIR_2 :
            case self::BANNER_TYPE_SANDS_PROMO_CTS:
            case self::BANNER_TYPE_ACTION_BANNER:
                return $this->shareUrl = $this->getUrlMainWide();
                break;
            case self::BANNER_TYPE_CTS_MOBILE :
            case self::BANNER_TYPE_CTS :
                return $this->shareUrl = $this->getUrlCts();
                break;
        }

        if (substr($this->banner_type, 0, 7) === 'BANNER_') {
            return $this->shareUrl = $this->getUrlMainWide();
        }

        return $this->shareUrl = $this->getShareUrl();
    }

    /**
     * Получить ссылку для эффирных баннеров
     * @return null|string
     */
    protected function getUrlMainSmallEfir()
    {
        if (!$this->getShareUrl()) {
            return null;
        }

        // /promo/onair/?SECTION_ID=26

        $queryStr = parse_url($this->getShareUrl(), PHP_URL_QUERY);
        parse_str($queryStr, $queryParams);

        if (isset($queryParams['SECTION_ID'])) {

            $category = TreeList::getIdTreeByBitrixId($queryParams['SECTION_ID']);

            return UrlHelper::construct(['/onair',
                'category' => $category,
                '#' => 'schedule',
            ]);
        }

        return $this->getShareUrl();
    }

    /**
     * Получить ссылку на товар ЦТС
     * @return string
     */
    protected function getUrlCts()
    {
        if ($url = $this->getShareUrl()) {
            return $url;
        } elseif ($this->bitrix_product_id) {

            $cmsContentElement = Contents::getContentElementByBitrixId($this->bitrix_product_id, [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);

            if ($cmsContentElement) {
                return UrlHelper::construct(['/cms/content-element/view', 'model' => $cmsContentElement, self::BID_PARAM => $this->id]);
            }
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getUrlMainWide()
    {
        $shareUrl = $this->getShareUrl();

        if ($this->code && !$shareUrl) {
            $content = Contents::getContentByCode('promo'); //127
            $cmsContentElement = Contents::getContentElementByCode($this->code, $content);

            if ($cmsContentElement) {
                return UrlHelper::construct(['/cms/content-element/view', 'model' => $cmsContentElement, self::BID_PARAM => $this->id]);
            }
        } elseif ($this->bitrix_product_id) {
            return $this->getUrlCts();
        }

        return $shareUrl;
    }

    /**
     * Глобальный метод для определения даты баннеров
     * @return int
     */
    public static function getDate()
    {
        if ($bannerDate = \Yii::$app->request->get(self::BANNER_PREVIEW_KEY)) {
            return strtotime($bannerDate) ?: time();
        }

        return time();
    }

    /**
     * Получить акции по типу с учетом эфира
     *
     * @param string $type
     * @param int $limit
     *
     * @return mixed
     * @throws \Throwable
     */
    public static function getSharesByTypeEfir($type = self::BANNER_TYPE_CTS, $limit = 5)
    {
        return static::getDb()->cache(function ($db) use ($type, $limit) {
            return (self::getShareByTypeEfirQuery($type))
                ->limit($limit)
                ->all();
        }, HOUR_1);
    }

    public static function getMainSlider($mainSliderBannerType)
    {
        return static::getDb()->cache(function ($db) use ($mainSliderBannerType) {
            return
                SsShare::find()
                    ->with(['image'])
                    ->hasImage()
                    ->active()
                    ->bannerType($mainSliderBannerType)
                    ->orderByBeginDatetime()
                    ->showTime(SsShare::getDate())
                    ->limit(SsShare::DEFAULT_LIMIT_MAIN_SLIDER)
                    ->all();
        }, HOUR_1);
    }

    /**
     * @param string $type
     *
     * @return SsSharesQuery
     */
    private static function getShareByTypeEfirQuery($type = self::BANNER_TYPE_CTS)
    {
        $scheduleDate = self::getDate();

        $query = self::find()
            ->joinWith('product')
            ->andWhere(['banner_type' => $type])
            ->andWhere(['not', ['ss_shares.image_id' => null]])
            ->andWhere(['not', ['ss_shares.active' => Cms::BOOL_N]])
            ->andWhere('begin_datetime <= :time AND end_datetime >= :time', [
                ':time' => $scheduleDate,
            ]);

        if ($type == SsShare::BANNER_TYPE_CTS) {
            $onAirSchedule = array_filter(
                (new \common\widgets\onair\OnAir())->getScheduleList(),
                function ($schedule) {
                    return $schedule['tree_id'] != null;
                }
            );
            if (count($onAirSchedule) > 0) {
                // сортировка по расписанию эфира
                $arTrees = array_unique(array_map(function ($s) {
                    return $s['tree_id'];
                }, $onAirSchedule));
                $query->orderBy([
                    new DbEXpression('FIELD (`ss_shares`.`schedule_tree_id`, ' . implode(',', $arTrees) . ')'),
                    new DbEXpression('`ss_shares`.`begin_datetime` ASC'),
                    new DbEXpression('`ss_shares`.`id` DESC'),
                ]);
            }
        }
        return $query;
    }

    public function getMobileCtsForProduct($bitrixId){

        return static::getDb()->cache(function ($db) use ($bitrixId) {
            $query = (self::getShareByTypeEfirQuery(self::BANNER_TYPE_CTS_MOBILE));
            if ($bitrixId){
                $query->andWhere(['bitrix_product_id' => $bitrixId]);
            }
            return $query->one();
        }, HOUR_1);
    }

    /**
     * @param array $banners
     * @param null $promotionName
     * @return array
     */
    public static function normalizeForDataLayer(array $banners, $promotionName = null)
    {
        $promotions = [];

        if ($banners) {

            foreach ($banners as $index => $promotion) {

                $promotions[] = [
                    'id' => $promotion->id,
                    'name' => $promotion->name,
                    'creative' => $promotionName ?: 'banner' . ($index + 1),
                    'position' => "slot" . ($index + 1)
                ];
            }
            return $promotions;
        }
    }
}
