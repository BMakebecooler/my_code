<?php

namespace common\models\query;


/**
 * This is the ActiveQuery class for [[\common\models\generated\models\CmsTreeProperty]].
 *
 * @see \common\models\generated\models\CmsTreeProperty
 */
class CmsTreePropertyQuery extends \common\ActiveQuery
{
    /*public function active()
    {
    return $this->andWhere('[[status]]=1');
    }*/


    /**
     * @param $id
     *
     * @return CmsTreePropertyQuery
     */
    public function element($id)
    {
        return $this->andWhere(['element_id' => (int)$id]);
    }

    /**
     * @param $id
     *
     * @return CmsTreePropertyQuery
     */
    public function property($id)
    {
        return $this->andWhere(['property_id' => $id]);
    }

    /**
     * @param $id
     *
     * @return CmsTreePropertyQuery
     */
    public function createdBy($id)
    {
        return $this->andWhere(['created_by' => (int)$id]);
    }

    /**
     * @param string $code
     * @return CmsTreePropertyQuery
     */
    public function code(string $code)
    {
        return $this->andWhere(['code' => $code]);
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\models\CmsTreeProperty[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\models\CmsTreeProperty|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
