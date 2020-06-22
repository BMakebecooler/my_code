<?php

/**
 * Общий компонент для кеша
 * User: ubuntu5
 * Date: 04.04.17
 * Time: 20:54
 */

namespace common\components\cache;


use yii\caching\TagDependency;
use yii\caching\FileCache as CacheComponent; // yii\caching\DummyCache
//use yii\redis\Cache as CacheComponent;
//use \yii\mongodb\Cache as CacheComponent;

class Cache extends CacheComponent
{

	public $cachePath;

    public function invalidate($tags)
    {
        TagDependency::invalidate($this, $tags);
    }

}
