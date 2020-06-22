<?php
namespace common\components\rbac;

use skeeks\cms\rbac\CmsManager as SXCmsManager;


/**
 * Class CmsManager
 * @package common\components\rbac
 */
class CmsManager extends SXCmsManager
{

    const ROLE_TEST = 'testing';
    const ROLE_BUYER = 'buyer';
    const ROLE_SERVICE = 'service';
    const ROLE_COPYRIGHT = 'copyright';
    const ROLE_DEMO = 'demo';

    static public function protectedRoles()
    {
        return array_merge(parent::protectedRoles(), [
            static::ROLE_TEST,
            static::ROLE_BUYER,
            static::ROLE_SERVICE,
            static::ROLE_COPYRIGHT,
            static::ROLE_DEMO,
        ]);

    }
}