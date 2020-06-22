<?php
    /**
     * Created by PhpStorm.
     * User: andrei
     * Date: 2019-03-30
     * Time: 14:04
     */

    namespace common\widgets\SwiperSlider;


    use yii\base\Widget;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;

    class SwiperSlider extends Widget
    {
        const BTN_TYPE_DEFAULT = '';
        const BTN_TYPE_CIRCLE = 'nav-button-circle';

        public $asImage = false;
        public $asBackground = true;
        public $pagination;
        public $options = [];
        public $buttons = [
            'prev' => null,
            'next' => null,
            'type' => self::BTN_TYPE_DEFAULT,
        ];
        public $gallery = [];

        public function init ()
        {
            parent::init();

            $defaultBtnClass = 'icon ';

            $defaultBtnClass .= ArrayHelper::getValue($this->buttons, 'type');


//            if (ArrayHelper::getValue($this->buttons, 'prev') === null) {
//                $this->buttons['prev'] = $this->renderSvgIcon('#icon-arrow-up', [
//                    'class' => "$defaultBtnClass nav-button-prev",
//                ]);
//            }
//
//            if (ArrayHelper::getValue($this->buttons, 'next') === null) {
//                $this->buttons['next'] = $this->renderSvgIcon('#icon-arrow-up', [
//                    'class' => "$defaultBtnClass nav-button-next",
//                ]);
//            }


            $className = 'swiper-slider';
            if (isset($this->options['class'])) {
                $className .= ' ' . $this->options['class'];
            }

            $this->options['class'] = $className;
        }

        protected function renderSvgIcon ($xlink, $options = [])
        {
            $use = Html::tag('use', null, ['xlink:href' => $xlink]);
            return Html::tag('svg', $use, $options);
        }

        public function run ()
        {
            return $this->renderSlider();
        }

        /**
         * Render Swiper slider item(one slide)
         *
         * @param $item
         *
         * @return string
         */
        public function renderItem ($item)
        {
            $label = ArrayHelper::getValue($item, 'label');
            $defaultOptions = [
                'class' => ' swiper-slide',
            ];
            $defaultLinkOptions = [];

            $options = ArrayHelper::getValue($item, 'options', []);

            if(!empty($item['options'])) {

                if(key_exists('class', $item['options'])) {
                    $options['class'] .=  $defaultOptions['class'];
                }
            }

            $linkOption = ArrayHelper::getValue($item, 'linkOption', []);
            $img = null;

            if ($this->asImage) {
                $alt = ArrayHelper::getValue($item, 'alt', $label);
                $img = empty($item) ? null : Html::img($item['img'], ['alt' => $alt, 'class' => 'img-fluid']);
            }

            if ($this->asBackground) {
                $defaultLinkOptions['style'] = "background-image: url('{$item['img']}')";
            }

            $link = Html::a($img, $item['url'], array_merge($defaultLinkOptions, $linkOption));

            return Html::tag('div', $link, [
                'class' => ' swiper-slide',
            ]);
        }

        /**
         * Render Swiper slider structure
         * @return string
         */
        public function renderSlider ()
        {
            $sliderContainer = Html::beginTag('div', ['class' => 'swiper-container']);

            /*render wrap with slides*/
            $sliderWrapper = Html::beginTag('div', ['class' => 'swiper-wrapper']);

            foreach ($this->gallery as $item) {
                $sliderWrapper .= $this->renderItem($item);
            }

            $sliderWrapper .= Html::endTag('div');

            $sliderContainer .= $sliderWrapper;

            $sliderContainer .= Html::endTag('div');


            $html = Html::beginTag('div', $this->options);
            /*slides*/
            $html .= $sliderContainer;

            if ($this->pagination) {
                $html .= '<div class="swiper-pagination d-flex align-items-center justify-content-center"></div>';
            }
            /*nav btns*/
//            $html .= $this->buttons['prev'];
//            $html .= $this->buttons['next'];

            $html .= Html::endTag('div');

            return $html;
        }
    }
