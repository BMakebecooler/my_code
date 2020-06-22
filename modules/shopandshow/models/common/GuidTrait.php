<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 06.09.17
 * Time: 14:20
 */

namespace modules\shopandshow\models\common;

/**
 * Class GuidTrait
 * @property Guid $guid
 */
trait GuidTrait
{

    protected $_guid = null;

    public function init()
    {
//        parent::init();

//        $this->on(self::EVENT_AFTER_INSERT, [$this, "generateGuid"]);
//        $this->on(self::EVENT_AFTER_UPDATE, [$this, "generateGuid"]);
    }


    /*    public function _processGuidInsert($e)
        {
            $this->generateGuid();
        }*/

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGuid()
    {
        return $this->hasOne(Guid::className(), ['id' => 'guid_id']);
    }

    public function setGuid($guid)
    {
        $this->_guid = $guid;
    }

    public function generateGuid()
    {
        return Guid::saveGuid($this, $this->_guid);
    }

}