<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 14.09.17
 * Time: 20:53
 */

namespace common\components\email\services;

use common\components\email\services\modules\newsLetters\GRCreateNewsLettersOptions;
use rvkulikov\yii2\getResponse\modules\GRApiNewsLetters as KulikovGRApiNewsLetters;

class GRApiNewsLetters extends KulikovGRApiNewsLetters
{


    /**
     * @param GRCreateNewsLettersOptions $options
     *
     * @return array|mixed
     */
    public function sendNewsletter(GRCreateNewsLettersOptions $options)
    {
        $request  = $this->httpClient->post("newsletters", $options->toArray());
        $response = $request->send();

        if (!$response->isOk) {
            $this->handleError($response);
        }

        return $response->getData();
    }


}