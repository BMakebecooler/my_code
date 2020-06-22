<?php

namespace modules\shopandshow\models\mediaplan;


class MediaPlan extends \yii\db\ActiveRecord
{

    /**
     * Вернуть название категории
     * @return string
     */
    public function getCategoryName()
    {
        return $this->section_name;
    }

    /**
     * Вернуть ид категории
     * @return int
     */
    public function getCategoryId()
    {
        return $this->section_id;
    }

    /**
     * Вернуть ид категории с битрикса
     * @return int
     */
    public function getBitrixCategoryId()
    {
        return $this->bitrix_section_id;
    }

    public function getBeginTime()
    {
        return date('H:i', $this->begin_datetime); // + (13 * 3600)
    }

    public function getEndTime()
    {
        return date('H:i', $this->end_datetime); // + (13 * 3600)
    }


}