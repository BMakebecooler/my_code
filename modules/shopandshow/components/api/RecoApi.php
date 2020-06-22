<?php
/**
 * Интеграция с апи рекомендаций
 */

namespace modules\shopandshow\components\api;

use modules\shopandshow\components\api\base\ApiBase;


class RecoApi extends ApiBase
{

    /**
     * @var string
     */
    public $schema = 'http://';

    /**
     * @var string
     */
    public $host = '89.108.106.143:2424';

    public function getBaseUrl()
    {
        return $this->schema . $this->host . "/";
    }



    public function itemToItem(int $id, string $via = '')
    {

        $_method = 'products/' . (int) $id . '/also-buy/';

        if ( !empty($via) && in_array(strtolower($via), ['front', 'site']) )
            $_method .= 'via/' . strtolower($via);

        $_apiResponse = $this->send($_method);

        if ( $_apiResponse->isError === false )
            return $_apiResponse->data;

        return null;

    }



}