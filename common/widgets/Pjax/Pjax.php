<?php
    /**
     * Created by PhpStorm.
     * User: ubuntu5
     * Date: 27.06.17
     * Time: 11:44
     */

    namespace common\widgets\Pjax;


    use common\helpers\Url;
    use \skeeks\cms\widgets\Pjax as SxPjax;
    use \yii\widgets\Pjax as YiiPjax;

    class Pjax extends SxPjax
    {

        /**
         * Block container Pjax  - Скиксовский
         * @var bool
         */
        public $blockPjaxContainer = false;

        /**
         * Block container Pjax  - SS шный
         * @var bool
         */
        public $blockPjaxSsContainer = false;

        public function init()
        {
            $this->isBlock = $this->blockPjaxContainer;
            parent::init();
        }

        /**
         * Registers the needed JavaScript.
         */
        public function registerClientScript()
        {
            YiiPjax::registerClientScript();

            $errorMessage = \Yii::t('skeeks/admin', 'An unexpected error occurred. Refer to the developers.');

            if ($this->blockPjaxSsContainer === true && !Url::isMainPageCurrent()) {
                $this->getView()->registerJs(<<<JS
                (function(sx, $, _)
                {
         
                    var preLoader = new sx.classes.PreLoaded();

                    $(document).on('pjax:send', function(e)
                    {
                        preLoader.show();
                    });

                    $(document).on('pjax:complete', function(e) {
                        preLoader.hide();
                    });

                    $(document).on('pjax:error', function(e, data) {
                        sx.notify.error('{$errorMessage}');
                        preLoader.hide();
                    });
                }
                )(sx, sx.$, sx._);
JS
                );
            }

            if ($this->blockContainer) {
                $this->getView()->registerJs(<<<JS
                (function(sx, $, _)
                {
                    var blockerPanel = new sx.classes.Blocker($("{$this->blockContainer}"));

                    $(document).on('pjax:send', function(e)
                    {
                        var blockerPanel = new sx.classes.Blocker($("{$this->blockContainer}"));
                        blockerPanel.block();
                    });

                    $(document).on('pjax:complete', function(e) {
                        blockerPanel.unblock();
                    });

                    $(document).on('pjax:error', function(e, data) {
                        sx.notify.error('{$errorMessage}');
                        blockerPanel.unblock();
                    });

                })(sx, sx.$, sx._);
JS
                );
            }

        }

    }