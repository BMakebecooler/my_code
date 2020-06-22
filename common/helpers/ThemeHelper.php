<?php

/**
 * @author Arkhipov Andrei <arhan89@gmail.com>
 * @copyright (c) K-Gorod
 * Date: 08.04.2019
 * Time: 11:14
 */

namespace common\helpers;


class ThemeHelper
{
    const VERSION_V3_CHECK_PARAM = 'new_theme';
    const VERSION_V3_VIEWS_FOLDER = 'v3';

    public static function isEnable($checkParameter = self::VERSION_V3_CHECK_PARAM)
    {
        $enableNewThemeMap = [
            'site' => [
//                'index' => true,
                'secret-sale' => true,
                'top-sto' => true,
                'htc-new' => true,
                'delivery-pvz-se' => true,
                'skoro' => true
            ],
            'category' => [
                'view' => true
            ],
            'onair' => [
                'index' => true
            ],
            //TODO Это только для теста! Можно убрать и оставить то что выше!
            'onair-schedule' => [
                'index' => true
            ],
            'products' => [
//                'index' => true,
                'view' => true,
            ],
        ];

        //если контролер-экшен присутствует в мапе то включаем новую тему.
        if(\Yii::$app->controller){
        if (ArrayHelper::getValue($enableNewThemeMap, [\Yii::$app->controller->id, \Yii::$app->controller->action->id])) {
            return true;
        }
        }


        $parseUrl = parse_url(\Yii::$app->request->url);
        if (isset($parseUrl['path']) && $parseUrl['path'] === '/') {
            return true;
        }


//        $id = \Yii::$app->request->getQueryParam('id');
//        if ($id == 1) {
//            return true;
//        }

        return isset($_GET[$checkParameter]);
    }

    public static function pathMap($theme = self::VERSION_V3_VIEWS_FOLDER)
    {
        return [
            '@app/views' =>
                [
                    "@app/themes/$theme"
                ],
        ];
    }
}