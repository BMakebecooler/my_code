<?php

namespace common\seo;

/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-15
 * Time: 20:42
 */

interface SeoFields
{
    /**
     *  Получает заголовок для сео
     */
    public function getSeoTitle();

    /**
     * Получает описанеие для сео
     */
    public function getSeoDescription();
    /**
     * Получает описанеие для сео
     */
    public function getOpenGraphDescription();

    /**
     * todo:: Данный мета тег считается устаревшим
     * @deprecated
     */
    public function getSeoKeywords();

}
