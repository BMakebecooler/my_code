<?php

namespace common\models;

/**
 *
 * Class CmsUser
 * @deprecated  Use \common\models\User instead
 * @package common\models
 */
class CmsUser extends \common\models\generated\models\CmsUser
{
    public static function find()
    {
        return new \common\models\query\CmsUserQuery(get_called_class());
    }
}