<?php

namespace common\components;

use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\Theme;
use yii\web\Application;

class AppComponent extends Component implements BootstrapInterface
{

    /**
     * Название куки ключа в котором содержится желаемая для пользователя версия
     */
    const VIEW_TYPE_SITE_VERSION_NAME = 'site_v';

    const VIEW_TYPE_SITE = 'site';
    const VIEW_TYPE_MOBILE = 'mobile';
    const VIEW_TYPE_WEBVIEW = 'webview';

    protected $viewType = null;

    public function init()
    {
        parent::init();

        $this->viewType = $this->getViewType();
    }

    public function bootstrap($app)
    {
        \Yii::$app->on(Application::EVENT_BEFORE_REQUEST, function () {
            // Если это мобильный телефон и пользователь НЕ захотел смотреть настольную версию
            if (
                $this->isSiteSS() &&
                $this->canViewMobileVersion() &&
                !$this->isViewTypeSite() &&
                \Yii::$app->mobileDetect->isMobile()/* &&
                !\Yii::$app->mobileDetect->isTablet()*/
            ) {
                //определение пути к папке с шаблоном
                \Yii::$app->view->theme = new Theme([
                    'pathMap' =>
                        [
                            '@app/views' =>
                                [
                                    '@app/templates/v2/mobile',
                                ],
                        ]
                ]);
            }

            if ($this->isViewTypeMobileApp()) {
                \Yii::$app->layout = '@app/templates/v2/mobile/layouts/webview';
            }
        });
    }

    /**
     * Кто и где может видеть мобильную версию сайта
     * @return bool
     */
    protected function canViewMobileVersion()
    {

        return true;

        // На мастере мобила не должна быть включена
        return YII_ENV !== 'production';
    }

    /**
     * Виды отображений
     * @return array
     */
    protected function typesView()
    {
        return [
            self::VIEW_TYPE_SITE => 'Настольная версия сайта',
            self::VIEW_TYPE_MOBILE => 'Мобильная версия сайта',
            self::VIEW_TYPE_WEBVIEW => 'Мобильное приложение',
        ];
    }

    /**
     * Получение настроек отображения
     */
    public function getViewType()
    {
        if (\Yii::$app instanceof \yii\web\Application && \Yii::$app->request->get('webview', 0)) {
            return self::VIEW_TYPE_WEBVIEW;
        }

        $hasSiteVersionCookie = isset($_COOKIE[self:: VIEW_TYPE_SITE_VERSION_NAME]);
        $types = $this->typesView();

        if ($hasSiteVersionCookie) {
            $val = $_COOKIE[self:: VIEW_TYPE_SITE_VERSION_NAME];
            return ($hasSiteVersionCookie && isset($types[$val])) ? $val : self::VIEW_TYPE_SITE;
        }

        return false;
    }

    /**
     * Признак отображения - Настольная версия
     * @return bool
     */
    public function isViewTypeSite()
    {
        return $this->viewType === self::VIEW_TYPE_SITE;
    }

    /**
     * Признак отображения - Мобильная версия
     * @return bool
     */
    public function isViewTypeMobile()
    {
        return $this->viewType === self::VIEW_TYPE_MOBILE;
    }

    /**
     * Признак отображения - Мобильное приложение
     * @return bool
     */
    public function isViewTypeMobileApp()
    {
        return $this->viewType === self::VIEW_TYPE_WEBVIEW;
    }

    /**
     * Признак сайта ШШ
     * @return bool
     */
    public function isSiteSS()
    {
        return SS_SITE === CONST_SITE_SS;
    }

    /**
     * Признак сайта Шика
     * @return bool
     */
    public function isSiteShik()
    {
        return SS_SITE === CONST_SITE_SHIK;
    }


}