<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 12.05.17
 * Time: 10:40
 */

namespace common\components\images;

use common\helpers\Url;
use skeeks\cms\components\Imaging as SXImaging;
use skeeks\cms\components\imaging\Filter;

class Imaging extends SXImaging
{

    /**
     * Константа для разбора URL - это некая метка, с этого момента идет указание фильтра
     */
//    const THUMBNAIL_PREFIX = "_";


    /**
     * Собрать URL на thumbnail, который будет сгенерирован автоматически в момент запроса.
     *
     * @param string $originalSrc          Путь к оригинальному изображению
     * @param Filter $filter Объект фильтр, который будет заниматься преобразованием
     * @param string $nameForSave Название для сохраненеия файла (нужно для сео)
     * @return string
     */
    public function thumbnailUrlOnRequest($originalSrc, Filter $filter, $nameForSave = '')
    {
        $originalSrc = (string)$originalSrc;
        $extension = static::getExtension($originalSrc);

        if (!$extension) {
            return $originalSrc;
        }

        if (!$this->isAllowExtension($extension)) {
            return $originalSrc;
        }

        if (!$nameForSave) {
            $nameForSave = static::DEFAULT_THUMBNAIL_FILENAME;
        }

        $params = [];
        if ($filter->getConfig()) {
            $params = $filter->getConfig();
        }

        $replacePart = DIRECTORY_SEPARATOR . static::THUMBNAIL_PREFIX . $filter->id
            . ($params ? DIRECTORY_SEPARATOR . $this->getParamsCheckString($params) : "") //4934e55b482539dbf9a3f9c3ff71b184
            . DIRECTORY_SEPARATOR . $nameForSave;

        $imageSrcResult = str_replace('.' . $extension, $replacePart . '.' . $extension, $originalSrc);

        if ($params) {
            $imageSrcResult = $imageSrcResult . '?' . http_build_query($params);
        }

        return $imageSrcResult;
    }

    /**
     *
     * @param $originalSrc Путь к оригинальному изображению
     * @param Filter $filter Объект фильтр, который будет заниматься преобразованием
     * @param string $nameForSave Название для сохраненеия файла (нужно для сео)
     * @return string
     */
    public function thumbnailUrlSS($originalSrc, Filter $filter, $nameForSave = '')
    {
//        return $this->thumbnailUrlOnRequest($originalSrc, $filter, $nameForSave);


//        $n = mt_rand(1, 4);
        $n = 1;

        $imageSrcResult = $this->thumbnailUrlOnRequest($originalSrc, $filter, $nameForSave);

//        $staticHostPrefix = 'sc';

        return Url::withCdnPrefix($imageSrcResult);
        if ( !empty(\Yii::$app->params['hosts']['cdn']) ) {

            $host = \Yii::$app->params['hosts']['cdn'];

            return $host['schema'].'://'
                .($host['prefix'] ? $host['prefix'].($host['counter'] ? $n : '').'.' : '')
                .$host['domain'].$imageSrcResult;

        }

        return 'https://img'.$n.'.shopandshow.ru' . $imageSrcResult;
    }


    /**
     * Проверочная строка параметров.
     * @param array $params
     * @return string
     */
    public function getParamsCheckString($params = [])
    {
        if ($params) {
            $result = md5($this->sold . http_build_query($params));

            return str_replace([

                'ad',
                'ads',
                'adv',
                'advert',
                'ban',

            ], '', $result);
        }

        return '';
    }

}