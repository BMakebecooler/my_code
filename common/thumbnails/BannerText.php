<?php

namespace common\thumbnails;

use common\helpers\Strings;
use common\helpers\Url;
use yii\base\Exception;

use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopProduct;

class BannerText extends BaseThumbnail
{
    public $font = 'v2/common/fonts/glober_bold.woff';
    public $color = '#950330';
    public $size = '13';
    public $w;
    public $h;

    // SsShare id
    public $sid = null;
    public $angle = 0;

    public function init()
    {
        parent::init();

        if (!$this->font && !$this->color && !$this->size) {
            throw new Exception("Необходимо указать все параметры шрифта: название, цвет, размер");
        }

        if (!$this->sid) {
            throw new Exception("Необходимо указать продукт");
        }
    }

    protected function _save()
    {
        $share = SsShare::findOne($this->sid);

        $boxOffset = self::getShareTextStart($share);
        // Banner unsupported
        if (!$boxOffset || empty($share->bitrix_product_id)) {
            return $share->image->src;
        }

        $shopProduct = ShopProduct::getInstanceByContentElement($share->product);

        $price = $shopProduct->getBasePriceMoney();
        $prefix = 'Цена сегодня';

        if ($shopProduct->isOffer()) {

            $prices = $shopProduct
                ->getTradeOffers()
                ->select(new \yii\db\Expression('MIN(IFNULL(price, min_price)) as min_price, MAX(IFNULL(price, max_price)) as max_price'))
                ->innerJoin('ss_shop_product_prices as p', 'p.product_id = cms_content_element.id')
                ->innerJoin('shop_product as SP', 'SP.id = cms_content_element.id AND SP.quantity > 0')
                ->asArray()
                ->one();

            if ($prices && $prices['min_price'] != $prices['max_price']) {
                $prefix = 'от';
                $price = Strings::getMoneyFormat($prices['min_price']);
            }
        }


        $options = $this->getOptions();
        $fontOptions = [
            'size' => $this->size,
            'color' => $this->color,
            'angle' => $this->angle,
        ];

        $text = '';

        if ((int)$price) {
            $text = sprintf('%s %s руб.', $prefix, $price);
        }

        Image::text($this->_originalRootFilePath, $text, $this->font, $boxOffset, $fontOptions)
            ->save($this->_newRootFilePath, $options);

        if ($this->w || $this->h) {
            if (!$this->w) {
                $size = Image::getImagine()->open($this->_originalRootFilePath)->getSize();

                $this->w = round(($size->getWidth() * $this->h) / $size->getHeight());

            } elseif (!$this->h) {
                $size = Image::getImagine()->open($this->_originalRootFilePath)->getSize();

                $this->h = round(($size->getHeight() * $this->w) / $size->getWidth());
            }

            Image::thumbnail($this->_newRootFilePath, $this->w, $this->h)
                ->save($this->_newRootFilePath, $options);
        }
    }

    /**
     * @param $share
     * @param array $params
     * @return string
     */
    public static function getShareImageWithText( $share, $params = [])
    {
        $params['upd'] = $share->updated_at;
        $params['sid'] = $share->id;

        return \Yii::$app->imaging->thumbnailUrlOnRequest(
            Url::withCdnPrefix($share->image->src),
            new static($params)
        );
    }

    /**
     * @param SsShare $share
     * @return mixed|null
     */
    protected static function getShareTextStart(SsShare $share)
    {
        static $boxOffset = [
            'BANNER_1_1' => [19, 538],
            'BANNER_1_2' => [19, 538],
            'BANNER_1_3' => [19, 239],
            'BANNER_1_4' => [19, 239],

            'BANNER_2_1' => [7, 239],
            'BANNER_2_2' => [7, 239],
            'BANNER_2_3' => [14, 538],

            'BANNER_3_1' => [15, 238],
            'BANNER_3_2' => [15, 238],
            'BANNER_3_3' => [15, 238],
            'BANNER_3_4' => [12, 536],

            'BANNER_4_1' => [15, 542],
            'BANNER_4_2' => [16, 240],
            'BANNER_4_3' => [16, 240],

            'BANNER_5_1' => [20, 337],
            'BANNER_5_2' => [20, 337],
            'BANNER_5_3' => [13, 237],
            'BANNER_5_4' => [13, 237],
            'BANNER_5_5' => [13, 237],

            // узкий баннер
            //'BANNER_6' => [0, 0],

            'BANNER_7_1' => [21, 333],
            'BANNER_7_2' => [21, 333],
            'BANNER_7_3' => [21, 333],

            'BANNER_9_1' => [21, 538],
            'BANNER_9_2' => [21, 538],
            'BANNER_9_3' => [21, 538],

            'BANNER_10_1' => [19, 239],
            'BANNER_10_2' => [19, 239],
            'BANNER_10_3' => [19, 539],
            'BANNER_10_4' => [19, 539],

            // комплект без цен
            //'BANNER_11_1' => [0, 0],
            //'BANNER_11_2' => [0, 0],
            //'BANNER_11_3' => [0, 0],
            //'BANNER_11_4' => [0, 0],
            //'BANNER_11_5' => [0, 0],

            'BANNER_12_1' => [16, 538],
            'BANNER_12_2' => [16, 538],
        ];

        return isset($boxOffset[$share->banner_type]) ? $boxOffset[$share->banner_type] : null;
    }
}