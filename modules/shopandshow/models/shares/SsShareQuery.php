<?php

namespace modules\shopandshow\models\shares;

use skeeks\cms\components\Cms;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[AirBlockOffcnt]].
 *
 * @see SsShare
 */
class SsShareQuery extends ActiveQuery
{
    /**
     * @return SsShareQuery
     */
    public function active()
    {
        return $this->andWhere(['active' => Cms::BOOL_Y]);
    }

    /**
     * @param $searchDate string
     * @return SsShareQuery
     */
    public function byDate($searchDate = false)
    {
        if (!$searchDate) {
            $searchDate = time();
        }
        return $this
            ->andFilterWhere(['<=', 'begin_datetime', $searchDate])
            ->andFilterWhere(['>=', 'end_datetime', $searchDate]);
    }

    /**
     * @param $bannerTypeStartsWith string
     * @return SsShareQuery
     */
    public function byBannerType($bannerTypeStartsWith)
    {
        return $this->andWhere("banner_type LIKE '{$bannerTypeStartsWith}%'");
    }

    /**
     * @param $blockId integer
     * @return SsShareQuery
     */
    public function byBlockId($blockId = null)
    {
        return $this->andWhere(['share_schedule_id' => $blockId ?: -1]);
    }

    /**
     * @inheritdoc
     * @return SsShare[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return SsShare|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return SsShareQuery
     */
    public function orderForGrid()
    {
        return $this->orderBy('id ASC');
    }

    /** Заполнение массива баннеров для блока, учитывая очередность
     * @param array $placeholders - массив со списком плэйсхолдеров баннеров
     * @param int $bannersBlockForShow - номер позиции блока чьи баннеры необходимо вернуть
     * @return array
     */
    public function fillPlaceholders(array $placeholders, $bannersBlockForShow = 0)
    {
        $bannersBlockForShow = 0;
        $bannersSrc = $this->all();

        $bannersByTypes = array();

        if ($bannersSrc) {
            foreach ($bannersSrc as $banner) {
                $bannersByTypes[$banner->banner_type][] = $banner['id'];
            }
        }

        //Разложим полученные банные по рядам (блокам) и выберем нужный нам блок
        $banners = array();
        foreach ($placeholders as $placeholder) {
            $bannerId = isset($bannersByTypes[$placeholder][$bannersBlockForShow]) ? $bannersByTypes[$placeholder][$bannersBlockForShow] : 0;
            $banners[$placeholder] = $bannerId ? $bannersSrc[$bannerId] : false;
        }
        return $banners;
    }
}