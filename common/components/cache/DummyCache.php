<?php

/**
 * Общий компонент для кеша
 * User: ubuntu5
 * Date: 04.04.17
 * Time: 20:54
 */

namespace common\components\cache;

use \yii\caching\DummyCache as CacheComponent;

class DummyCache extends CacheComponent
{

	public $cachePath;
	public $redis;

}