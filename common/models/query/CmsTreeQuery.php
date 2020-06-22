<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-08
 * Time: 15:26
 */

namespace common\models\query;


use common\models\CmsTree;
use common\models\TreeFactory;

class CmsTreeQuery extends \common\models\generated\query\CmsTreeQuery
{
    /**
     * @param $id
     *
     * @return CmsTreeQuery
     */
    public function parent ($id)
    {
        return $this->andWhere(['pid' => (int)$id]);
    }

    /**
     * @param $id
     *
     * @return CmsTreeQuery
     */
    public function byParent ($id)
    {
        return $this->andWhere(['pid' => (int)$id]);
    }

    public function type ($id)
    {
        return $this->andWhere(['tree_type_id' => $id]);
    }

    /**
     * @param $level
     *
     * @return CmsTreeQuery
     */
    public function level ($level)
    {
        return $this->andWhere(['level' => (int)$level]);
    }

    /**
     * @return CmsTreeQuery
     */
    public function active ()
    {
        return $this->andWhere(['active' => \common\helpers\Common::BOOL_Y]);
    }

    /**
     * @return CmsTreeQuery
     */
    public function notActive ()
    {
        return $this->andWhere(['!=', 'active', \common\helpers\Common::BOOL_Y]);
    }

    public function siteCode ($code)
    {
        return $this->andWhere(['site_code' => $code]);
    }

    /**
     * @inheritdoc
     * @return CmsTree[]|array
     */
    public function all ($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CmsTree|array|null
     */
    public function one ($db = null)
    {
        return parent::one($db);
    }

}