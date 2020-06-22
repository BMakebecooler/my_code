<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "seo".
 *
 * @property integer $id ID
 * @property string $owner Owner
 * @property integer $owner_id Owner ID
 * @property string $h1 H1
 * @property string $title Title
 * @property string $slug Slug
 * @property string $meta_keywords Meta Keywords
 * @property string $meta_description Meta Description
 * @property string $meta_index Meta Index
 * @property string $redirect_301 Redirect 301
 * @property string $og_title Og Title
 * @property string $og_description Og Description
*/
class Seo extends \common\ActiveRecord
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
        return 'seo';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['owner', 'h1', 'title', 'slug', 'meta_index'], 'required'],
            [['owner_id'], 'integer'],
            [['meta_keywords', 'meta_description', 'og_description'], 'string'],
            [['owner', 'h1'], 'string', 'max' => 255],
            [['title', 'og_title'], 'string', 'max' => 512],
            [['slug', 'meta_index', 'redirect_301'], 'string', 'max' => 1024],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner' => 'Owner',
            'owner_id' => 'Owner ID',
            'h1' => 'H1',
            'title' => 'Title',
            'slug' => 'Slug',
            'meta_keywords' => 'Meta Keywords',
            'meta_description' => 'Meta Description',
            'meta_index' => 'Meta Index',
            'redirect_301' => 'Redirect 301',
            'og_title' => 'Og Title',
            'og_description' => 'Og Description',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SeoQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SeoQuery(get_called_class());
    }
}
