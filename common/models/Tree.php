<?php

namespace common\models;

use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\common\GuidBehavior;
use skeeks\cms\models\Tree As SXTree;

/**
 * @property CmsContentElement[] $cmsContentElements
 * @property integer $bitrix_id
 * @property integer $guid_id
 * @property integer $count_content_element
 *
 * @property GuidBehavior $guid
 */
class Tree extends SXTree
{

    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            GuidBehavior::className() => GuidBehavior::className()
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElements()
    {
        return $this->hasMany(CmsContentElement::className(), ['tree_id' => 'id']);
    }

}