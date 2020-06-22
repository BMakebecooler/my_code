<?php

namespace common\thumbnails;

use common\helpers\ArrayHelper;

/**
 * Class BaseThumbnail
 * @package common\thumbnails
 */
abstract class BaseThumbnail extends \skeeks\cms\components\imaging\Filter
{

    /**
     * Название параметра для отключения кеширования на стороне nginx
     */
    const NO_CACHE_PARAM = 'upd';
    const QUALITY_DEFAULT = 100;

    public $upd = 0;
    public $quality = null;

    public function init()
    {
        parent::init();

        $this->initQuality();

        if ($this->upd) {
            $this->_config['upd'] = $this->upd;
        }
    }

    public function getOptions()
    {
        return [
            'jpeg_quality' => $this->quality,
        ];
    }

    /**
     * инициализирует quality
     */
    protected function initQuality()
    {
        // если качество не указано, берем по умолчанию
        if (empty($this->quality)) {
            $this->quality = self::QUALITY_DEFAULT;
            ArrayHelper::remove($this->_config, 'quality');
        }
        // а если указано явно - передаем его в конфиг (для генерации урл в imaging)
        else {
            $this->_config['quality'] = $this->quality;
        }
    }
}