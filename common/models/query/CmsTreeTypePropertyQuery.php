<?php

namespace common\models\query;


/**
* This is the ActiveQuery class for [[\common\models\generated\models\CmsTreeTypeProperty]].
 *
 * @see \common\models\generated\models\CmsTreeTypeProperty
*/
class CmsTreeTypePropertyQuery extends \common\ActiveQuery
{
    /*public function active()
    {
    return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @param string $code
     * @return CmsTreeTypePropertyQuery
     */
    public function code(string $code)
    {
        return $this->andWhere(['code' => $code]);
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\models\CmsTreeTypeProperty[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\models\CmsTreeTypeProperty|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

}