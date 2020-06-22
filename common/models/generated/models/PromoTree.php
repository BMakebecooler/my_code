<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "promo_tree".
 *
 * @property integer $id ID
 * @property integer $promo_id Promo ID
 * @property integer $tree_id Tree ID
 *
 * @property Promo $promo
 * @property CmsTree $tree
 */
class PromoTree extends \common\ActiveRecord
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
        return 'promo_tree';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['promo_id', 'tree_id'], 'required'],
            [['promo_id', 'tree_id'], 'integer'],
            [['promo_id', 'tree_id'], 'unique', 'targetAttribute' => ['promo_id', 'tree_id'], 'message' => 'The combination of Promo ID and Tree ID has already been taken.'],
            [['promo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Promo::className(), 'targetAttribute' => ['promo_id' => 'id']],
            [['tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['tree_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'promo_id' => 'Promo ID',
            'tree_id' => 'Tree ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
     */
    public function getPromo()
    {
        return $this->hasOne($this->called_class_namespace . '\Promo', ['id' => 'promo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
     */
    public function getTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\PromoTreeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\PromoTreeQuery(get_called_class());
    }
}