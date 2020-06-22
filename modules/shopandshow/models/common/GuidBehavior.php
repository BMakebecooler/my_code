<?php

namespace modules\shopandshow\models\common;

use yii\db\BaseActiveRecord;
use \yii\base\Behavior;

/**
 * @property Guid $guidObject
 */

class GuidBehavior extends Behavior
{

    const ATTRIBUTE_GUID_ID = 'guid_id';

    public $guidIdAttribute = self::ATTRIBUTE_GUID_ID;
    public $noGuidAutoGenerateAttribute = 'noGuidAutoGenerate';

    /**
     * @var string
     */
    public $guid = null;

    /**
     * @var string|null
     */
    private $_guid = null;

    public function init()
    {
        $this->guid = $this;
    }

    /**
     * @return array
     */
    public function events()
    {
        return [
//            BaseActiveRecord::EVENT_AFTER_FIND => "getGuid",
            BaseActiveRecord::EVENT_AFTER_UPDATE => "generateGuid",
            BaseActiveRecord::EVENT_AFTER_INSERT => "generateGuid",
        ];
    }

    /**
     * @param $event
     */
    public function generateGuid($event = null)
    {
        // если не надо генерировать гуид
        if ($this->owner->hasProperty($this->noGuidAutoGenerateAttribute) && $this->owner->{$this->noGuidAutoGenerateAttribute} == true) {
            return;
        }

        if (/*!$this->owner->{$this->guidIdAttribute} && */ //[DEPRECATED] Если нет гуида то генерируем его
          $this->owner->hasProperty($this->guidIdAttribute)
        ) {
            //Сохраняем любой ГУИД, он создастся или обновится
            Guid::saveGuid($this->owner, $this->_guid);
        }
    }

    /**
     * @return Guid|null
     */
    public function getGuidObject()
    {
        return $this->owner->hasOne(Guid::className(), ['id' => $this->guidIdAttribute]);
    }

    public function setGuid($guid)
    {
        return $this->_guid = $guid;
    }

    public function getGuid()
    {
        if ($this->_guid) {
            return $this->_guid;
        } else {
            $guidObject = $this->guidObject->one();

            return $guidObject ? $guidObject->guid : null;
        }
    }

}
