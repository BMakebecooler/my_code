<?php

namespace common\components\queue\handler\protocolkfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;
use common\models\Product;
use yii\db\Exception;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 17:30
 */
class PropAddInfo extends AbstractHandler implements HandlerInterface
{

    public function execute()
    {
        if ($guid = $this->data['OffcntGuid']){
            /** @var Product $product */
            $product = parent::getProductByGuid($guid);
            if ($this->data['Props']) {
                foreach ($this->data['Props'] as $prop) {
                    $property = $product->getPropertyByGuid($prop['PropTypeGuid']);
                    if ($property) {
                        $product->$property = $prop['PropValue'];
                    }
                }
                // todo check and fix save(false)
                if (!$product->save(false)) {
                    throw new Exception('Error save new properties product errors' . print_r($product->errors, true));
                }
            }
        }else{
            //Сообщений с этой ошибкой слишком много, отключим запись в лог что бы не захламлять
            //\Yii::error("Error! No GUID! Data: " . var_export($this->data, true), __METHOD__);
        }

        return true;
    }
}