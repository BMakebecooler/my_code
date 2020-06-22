<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "promo".
 *
 * @property integer $id ID
 * @property integer $active Active
 * @property string $name Name
 * @property string $link Link
 * @property string $description Description
 * @property string $meta_description Meta Description
 * @property string $meta_title Meta Title
 * @property string $meta_keywords Meta Keywords
 * @property integer $segment_id Segment ID
 * @property string $image Image
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $have_image Have Image
 * @property integer $updated_by Updated By
 * @property integer $tree_id_onair Tree Id Onair
 * @property integer $created_by Created By
 * @property integer $count_views Count Views
 * @property string $image_banner Image Banner
 * @property integer $count_views_day Count Views Day
 * @property string $url_link Url Link
 * @property integer $rating Rating
 * @property string $promocode Promocode
 * @property integer $start_timestamp Start Timestamp
 * @property integer $end_timestamp End Timestamp
 * @property integer $in_menu In Menu
 * @property integer $in_main In Main
 * @property integer $in_actions In Actions
 * @property string $promo Promo
 * @property string $tree_ids Tree Ids
 * @property integer $have_image_banner Have Image Banner
 * @property integer $priority Priority
 *
     * @property PromoTree[] $promoTrees
     * @property CmsTree[] $trees
    */
class Promo extends \common\ActiveRecord
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
    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
            'author' => \yii\behaviors\BlameableBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'promo';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['active', 'segment_id', 'created_at', 'updated_at', 'have_image', 'updated_by', 'tree_id_onair', 'created_by', 'count_views', 'count_views_day', 'rating', 'start_timestamp', 'end_timestamp', 'in_menu', 'in_main', 'in_actions', 'have_image_banner', 'priority'], 'integer'],
            [['name', 'link'], 'required'],
            [['description', 'meta_description'], 'string'],
            [['name', 'link', 'meta_title', 'meta_keywords', 'image', 'image_banner', 'url_link', 'promocode', 'promo', 'tree_ids'], 'string', 'max' => 255],
            [['link'], 'unique'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'name' => 'Name',
            'link' => 'Link',
            'description' => 'Description',
            'meta_description' => 'Meta Description',
            'meta_title' => 'Meta Title',
            'meta_keywords' => 'Meta Keywords',
            'segment_id' => 'Segment ID',
            'image' => 'Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'have_image' => 'Have Image',
            'updated_by' => 'Updated By',
            'tree_id_onair' => 'Tree Id Onair',
            'created_by' => 'Created By',
            'count_views' => 'Count Views',
            'image_banner' => 'Image Banner',
            'count_views_day' => 'Count Views Day',
            'url_link' => 'Url Link',
            'rating' => 'Rating',
            'promocode' => 'Promocode',
            'start_timestamp' => 'Start Timestamp',
            'end_timestamp' => 'End Timestamp',
            'in_menu' => 'In Menu',
            'in_main' => 'In Main',
            'in_actions' => 'In Actions',
            'promo' => 'Promo',
            'tree_ids' => 'Tree Ids',
            'have_image_banner' => 'Have Image Banner',
            'priority' => 'Priority',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPromoTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\PromoTree', ['promo_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id'])->viaTable('promo_tree', ['promo_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\PromoQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\PromoQuery(get_called_class());
    }
}
