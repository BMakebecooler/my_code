<?php

namespace modules\shopandshow\models\newEntities\products;

use common\models\cmsContent\CmsContentElement;
use common\models\ProductProperty;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;

class UsersList extends CmsContentElementModel
{
    const LINK_BUYER_GUID = '62968471C9840BAFE0538201090ACE60';

    public $users = [];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'Глобальный идентификатор',
        ];
    }

    public function setCmsContentElement(CmsContentElement $contentElement)
    {
        $this->cmsContentElement = $contentElement;

        $this->setAttributes([
            'guid' => $contentElement->guid->getGuid(),
        ]);
    }

    public function setUsersList(array $usersList = [])
    {
        $this->users = $usersList;
    }

    /**
     * @return bool
     */
    public function addData()
    {
        foreach ($this->users as $user) {
            Job::dump('UsrGuid: '.$user['UsrGuid']);
            Job::dump('LnkGuid: '.$user['LnkGuid']);

            if ($user['LnkGuid'] == self::LINK_BUYER_GUID) {

                // просто сохраняем guid в свойстве. Реальные данные по guid будут тянуться по мере необходимости через апи
                //$this->relatedPropertiesModel['BUYER_GUID'] = $user['UsrGuid'];
                ProductProperty::savePropByCode($this->cmsContentElement->id, 'BUYER_GUID', $user['UsrGuid']);
                break;
            }
        }

        //return $this->saveRelatedProperties();
        return true;
    }
}