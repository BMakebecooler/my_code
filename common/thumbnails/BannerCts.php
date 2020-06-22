<?php

    namespace common\thumbnails;

    use common\helpers\ArrayHelper;
    use common\helpers\Strings;
    use common\helpers\Url;
    use yii\base\Exception;

    use modules\shopandshow\models\shares\SsShare;
    use modules\shopandshow\models\shop\ShopProduct;

    class BannerCts extends BaseThumbnail
    {
        public $font1 = 'v2/common/fonts/glober_bold.woff';
        public $font2 = 'v2/common/fonts/glober_bold.woff';

        public $color1 = '#ffffff';
        public $color2 = '#9E0030';

        public $size1 = '36';
//    public $size2 = '16';
        public $size2 = '20';

        // отступы от левого верхнего края по осям Х и У соответственно
//    public $offset1 = [50, 110];
//    public $offset2 = [125, 174];

        // отступы от правого верхнего края по осям Х и У соответственно
        public $offset1 = [245, 110];
        public $offset2 = [243, 176];

        // цвет линии для зачеркивания цены
        public $strikeColor = '#B22222';
        // толщина линии для зачеркивания (0 - зачеркивать не надо)
        public $strikeWidth = 0;

        public $w;
        public $h;

        // SsShare id
        public $sid = null;
        public $angle = 0;

        /**
         * @inheritdoc
         * @throws Exception
         */
        public function init()
        {
            parent::init();

            if (!$this->font1 && !$this->color1 && !$this->size1 && !$this->font2 && !$this->color2 && !$this->size2) {
                throw new Exception("Необходимо указать все параметры шрифта: название, цвет, размер");
            }

            if (!$this->sid) {
                throw new Exception("Необходимо указать продукт");
            }
        }

        /**
         * @return string
         */
        protected function _save()
        {
            $share = SsShare::findOne($this->sid);

            // Banner unsupported
            if (empty($share->bitrix_product_id)) {
                return $share->image->src;
            }
            $options = $this->getOptions();
            $shopProduct = ShopProduct::getInstanceByContentElement($share->product);

            $price1 = $shopProduct->getBasePriceMoney();
            $price2 = Strings::getMoneyFormat($shopProduct->maxPrice() - $shopProduct->basePrice());

            // нет цен - досвидули
            if (!$price1 && !$price2) {
                return $share->image->src;
            }

            $fontOptions1 = [
                'size' => $this->size1,
                'color' => $this->color1,
                'angle' => $this->angle,
            ];

            $fontOptions2 = [
                'size' => $this->size2,
                'color' => $this->color2,
                'angle' => $this->angle,
            ];

            $lineOptions = [
                'color' => $this->strikeColor,
                'width' => $this->strikeWidth,
                'text' => $price2
            ];

            $text = sprintf('%s', $price1);

            //* Offsets correction *//
            //Знаем X правой стороны, необходимо вычислеть X левой

            $fontSize1 = ArrayHelper::getValue($fontOptions1, 'size', 12);
            $fontColor1 = ArrayHelper::getValue($fontOptions1, 'color', '#000');
            $fontAngle1 = ArrayHelper::getValue($fontOptions1, 'angle', 0);

            $fontBox1 = imagettfbbox($fontSize1, $fontAngle1, \Yii::getAlias($this->font1), $text);
            $textWidth1 = $fontBox1[2] - $fontBox1[0];
            $this->offset1[0] -= $textWidth1;

            //* /Offsets correction *//

            Image::text($this->_originalRootFilePath, $text, $this->font1, $this->offset1, $fontOptions1)
                ->save($this->_newRootFilePath, $options);

            $text = sprintf('%s', $price2);

            //* Offsets correction *//
            //Знаем X правой стороны, необходимо вычислеть X левой

            $fontSize2 = ArrayHelper::getValue($fontOptions2, 'size', 12);
            $fontColor2 = ArrayHelper::getValue($fontOptions2, 'color', '#000');
            $fontAngle2 = ArrayHelper::getValue($fontOptions2, 'angle', 0);

            $fontBox2 = imagettfbbox($fontSize2, $fontAngle2, \Yii::getAlias($this->font1), $text);
            $textWidth2 = $fontBox2[2] - $fontBox2[0];
            $this->offset2[0] -= $textWidth2;

            //* /Offsets correction *//

            if ($this->strikeWidth > 0) {
                Image::textStriked($this->_newRootFilePath, $text, $this->font2, $this->offset2, $fontOptions2, $lineOptions)
                    ->save($this->_newRootFilePath, $options);
            } else {
                Image::text($this->_newRootFilePath, $text, $this->font2, $this->offset2, $fontOptions2)
                    ->save($this->_newRootFilePath, $options);
            }

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
         * @return bool|string
         */
        public static function getShareImageWithText($share, $params = [])
        {
            $params['upd'] = $share->updated_at;
            $params['sid'] = $share->id;

            if (!$share->image) {
                return false;
            }

            return \Yii::$app->imaging->thumbnailUrlOnRequest(
                Url::withCdnPrefix($share->image->src),
                new static($params)
            );
        }
    }