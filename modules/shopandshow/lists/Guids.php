<?php

namespace modules\shopandshow\lists;

use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\user\User;
use modules\shopandshow\models\common\Guid;
use modules\shopandshow\models\shop\ShopOrder;
use yii\db\ActiveRecord;

class Guids
{

    /**
     * @param $guid
     * @return Guid
     */
    public static function getGuid($guid)
    {
        return Guid::findOne(['guid' => $guid]);
    }

    /**
     * @param $guid
     * @return CmsContentElement|User|ShopOrder|ActiveRecord|CmsContent|false
     */
    public static function getEntityByGuid($guid)
    {
        if ($guid = self::getGuid($guid)) {
            $entity = $guid->getEntity();
            $entity = $entity::findOne(['guid_id' => $guid->id]);

            return $entity;
        }

        return false;
    }
}