<?php

namespace common\models;

use common\seo\MetaTag;
use common\seo\SeoForm;

/**
 * This is the model class for table "{{%seo}}".
 *
 * @property int $id
 * @property string $owner
 * @property int $owner_id
 * @property string $h1
 * @property string $title
 * @property string $slug
 * @property string $meta_keywords
 * @property string $meta_description
 * @property string $meta_index
 * @property string $redirect_301
 * @property string $pageHeader
 */
class Seo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%seo}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner', 'h1', 'title', 'slug', 'og_title', ], 'safe'],
            [['og_title', 'og_description'], 'safe'],
            [['meta_index'], 'default', 'value' => SeoForm::DEFAULT_META_INDEX],
            [['owner', 'h1', 'title', 'slug', 'meta_index'], 'filter', 'filter' => 'trim'],
            [['owner_id'], 'integer'],
            [['meta_keywords', 'meta_description'], 'string'],
            [['owner', 'h1'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 512],
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
        ];
    }

    /**
     * @param $owner
     * @param $id
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function findByOwner($owner, $id)
    {
        return static::find()
            ->where('owner = :owner', [':owner' => $owner])
            ->andWhere(['owner_id' => (int) $id])
            ->one();
    }
    /**
     * @param $owner
     * @param $slug
     * @return Seo|null
     */
    public static function findByOwnerAndSlug($owner, $slug)
    {
        return static::find()
            ->where('owner = :owner', [':owner' => $owner])
            ->andWhere(['slug' => $slug])
            ->one();
    }

    public function findOwners()
    {
        return [

        ];
    }

    public function getPageHeader()
    {
        return MetaTag::replacementChunks($this->h1);
    }
}
