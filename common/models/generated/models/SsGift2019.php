<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_gift_2019".
 *
 * @property integer $id ID
 * @property integer $cms_tree_id Cms Tree ID
 * @property integer $is_woman Is Woman
 * @property integer $is_man Is Man
 * @property integer $is_relative Is Relative
 * @property integer $is_colleague Is Colleague
 * @property integer $age_lt_14 Age Lt 14
 * @property integer $age_lt_30 Age Lt 30
 * @property integer $age_eq_30 Age Eq 30
 * @property integer $age_lt_50 Age Lt 50
 * @property integer $age_gt_50 Age Gt 50
 * @property integer $interest_fashion Interest Fashion
 * @property integer $interest_jewerly Interest Jewerly
 * @property integer $interest_cooking Interest Cooking
 * @property integer $interest_tech Interest Tech
 * @property integer $interest_interior Interest Interior
 * @property integer $interest_needlework Interest Needlework
 *
     * @property CmsTree $cmsTree
    */
class SsGift2019 extends \common\ActiveRecord
{
    private $called_class_namespace;

    public function __construct()
    {
        $this->called_class_namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
        parent::__construct();
    }

                                                                    
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'ss_gift_2019';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['cms_tree_id', 'is_woman', 'is_man', 'is_relative', 'is_colleague', 'age_lt_14', 'age_lt_30', 'age_eq_30', 'age_lt_50', 'age_gt_50', 'interest_fashion', 'interest_jewerly', 'interest_cooking', 'interest_tech', 'interest_interior', 'interest_needlework'], 'integer'],
            [['cms_tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['cms_tree_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cms_tree_id' => 'Cms Tree ID',
            'is_woman' => 'Is Woman',
            'is_man' => 'Is Man',
            'is_relative' => 'Is Relative',
            'is_colleague' => 'Is Colleague',
            'age_lt_14' => 'Age Lt 14',
            'age_lt_30' => 'Age Lt 30',
            'age_eq_30' => 'Age Eq 30',
            'age_lt_50' => 'Age Lt 50',
            'age_gt_50' => 'Age Gt 50',
            'interest_fashion' => 'Interest Fashion',
            'interest_jewerly' => 'Interest Jewerly',
            'interest_cooking' => 'Interest Cooking',
            'interest_tech' => 'Interest Tech',
            'interest_interior' => 'Interest Interior',
            'interest_needlework' => 'Interest Needlework',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'cms_tree_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsGift2019Query the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsGift2019Query(get_called_class());
    }
}
