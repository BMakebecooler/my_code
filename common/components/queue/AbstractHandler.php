<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 17:36
 */

namespace common\components\queue;

use common\models\Product;
use yii\db\Exception;

class AbstractHandler
{

    public $data;

    /**
     * @param string $guid
     * @return object
     * @throws yii\db\Exception
     */
    protected function getProductByGuid(string $guid)
    {
        $product = Product::getModelByGuid($guid);
        if (empty($product)) {
            throw  new Exception('Error find product by guid ' . $guid);
        }
        return $product;
    }

}