<?php

namespace common\components;

use skeeks\cms\components\Breadcrumbs as SXBreadcrumbs;

class Breadcrumbs extends SXBreadcrumbs
{

    protected $stringViewPath = null;

    /**
     * @param string $delimiter
     * @param array $params
     * @return string
     */
    public function getStringViewPath($delimiter = '/', $params = [])
    {
        array_map(function ($part) use ($delimiter) {
            return $this->stringViewPath .= $delimiter . $part['name'];
        }, array_splice($this->parts, 1));

        if ($params && isset($params['removeString'])) {
            $this->stringViewPath = str_replace($params['removeString'], '', $this->stringViewPath);
        }

        return trim($this->stringViewPath, $delimiter);
    }

    /**
     * @param int $beforePrev
     * @return null
     */
    public function getBackUrl($beforePrev = 2)
    {
        if ($this->parts) {
            $prev = array_slice($this->parts, -$beforePrev, 1);
            return array_filter($prev) ? $prev[0]['url'] : null;
        }

        return null;
    }

}