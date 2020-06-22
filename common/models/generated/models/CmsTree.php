<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_tree".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property integer $image_id Image ID
 * @property integer $image_full_id Image Full ID
 * @property string $description_short Description Short
 * @property string $description_full Description Full
 * @property string $code Code
 * @property integer $pid Pid
 * @property string $pids Pids
 * @property integer $level Level
 * @property string $dir Dir
 * @property integer $has_children Has Children
 * @property integer $priority Priority
 * @property integer $published_at Published At
 * @property string $redirect Redirect
 * @property string $tree_menu_ids Tree Menu Ids
 * @property string $active Active
 * @property string $meta_title Meta Title
 * @property string $meta_description Meta Description
 * @property string $meta_keywords Meta Keywords
 * @property string $site_code Site Code
 * @property integer $tree_type_id Tree Type ID
 * @property string $description_short_type Description Short Type
 * @property string $description_full_type Description Full Type
 * @property integer $redirect_tree_id Redirect Tree ID
 * @property integer $redirect_code Redirect Code
 * @property string $name_hidden Name Hidden
 * @property string $view_file View File
 * @property integer $bitrix_id Bitrix ID
 * @property integer $count_content_element Count Content Element
 * @property integer $guid_id Guid ID
 * @property integer $popularity Popularity
 *
     * @property CmsContent[] $cmsContents
     * @property CmsContent[] $cmsContents0
     * @property CmsContentElement[] $cmsContentElements
     * @property CmsContentElementTree[] $cmsContentElementTrees
     * @property CmsContentElement[] $elements
     * @property CmsStorageFile $imageFull
     * @property CmsStorageFile $image
     * @property CmsTree $redirectTree
     * @property CmsTree[] $cmsTrees
     * @property CmsUser $createdBy
     * @property CmsTree $p
     * @property CmsTree[] $cmsTrees0
     * @property CmsSite $siteCode
     * @property CmsTreeType $treeType
     * @property CmsUser $updatedBy
     * @property CmsTreeFile[] $cmsTreeFiles
     * @property CmsStorageFile[] $storageFiles
     * @property CmsTreeImage[] $cmsTreeImages
     * @property CmsStorageFile[] $storageFiles0
     * @property CmsTreeProperty[] $cmsTreeProperties
     * @property FaqEmail[] $faqEmails
     * @property SsGift2019[] $ssGift2019s
    */
class CmsTree extends \common\ActiveRecord
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
            'author' => \yii\behaviors\BlameableBehavior::class,
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'cms_tree';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'image_id', 'image_full_id', 'pid', 'level', 'has_children', 'priority', 'published_at', 'tree_type_id', 'redirect_tree_id', 'redirect_code', 'bitrix_id', 'count_content_element', 'guid_id', 'popularity'], 'integer'],
            [['name', 'site_code'], 'required'],
            [['description_short', 'description_full', 'dir', 'meta_description', 'meta_keywords'], 'string'],
            [['name', 'pids', 'name_hidden'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 64],
            [['redirect', 'tree_menu_ids', 'meta_title'], 'string', 'max' => 500],
            [['active'], 'string', 'max' => 1],
            [['site_code'], 'string', 'max' => 15],
            [['description_short_type', 'description_full_type'], 'string', 'max' => 10],
            [['view_file'], 'string', 'max' => 128],
            [['pid', 'code'], 'unique', 'targetAttribute' => ['pid', 'code'], 'message' => 'The combination of Code and Pid has already been taken.'],
            [['image_full_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_full_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['redirect_tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['redirect_tree_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['pid'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['pid' => 'id']],
            [['site_code'], 'exist', 'skipOnError' => true, 'targetClass' => CmsSite::className(), 'targetAttribute' => ['site_code' => 'code']],
            [['tree_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTreeType::className(), 'targetAttribute' => ['tree_type_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'name' => 'Name',
            'image_id' => 'Image ID',
            'image_full_id' => 'Image Full ID',
            'description_short' => 'Description Short',
            'description_full' => 'Description Full',
            'code' => 'Code',
            'pid' => 'Pid',
            'pids' => 'Pids',
            'level' => 'Level',
            'dir' => 'Dir',
            'has_children' => 'Has Children',
            'priority' => 'Priority',
            'published_at' => 'Published At',
            'redirect' => 'Redirect',
            'tree_menu_ids' => 'Tree Menu Ids',
            'active' => 'Active',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'Meta Keywords',
            'site_code' => 'Site Code',
            'tree_type_id' => 'Tree Type ID',
            'description_short_type' => 'Description Short Type',
            'description_full_type' => 'Description Full Type',
            'redirect_tree_id' => 'Redirect Tree ID',
            'redirect_code' => 'Redirect Code',
            'name_hidden' => 'Name Hidden',
            'view_file' => 'View File',
            'bitrix_id' => 'Bitrix ID',
            'count_content_element' => 'Count Content Element',
            'guid_id' => 'Guid ID',
            'popularity' => 'Popularity',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContents()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContent', ['default_tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContents0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContent', ['root_tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementTree', ['tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['id' => 'element_id'])->viaTable('cms_content_element_tree', ['tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getImageFull()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'image_full_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getImage()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'image_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getRedirectTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'redirect_tree_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['redirect_tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCreatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'created_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getP()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'pid']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTrees0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['pid' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSiteCode()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsSite', ['code' => 'site_code']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTreeType()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTreeType', ['id' => 'tree_type_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeFile', ['tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStorageFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsStorageFile', ['id' => 'storage_file_id'])->viaTable('cms_tree_file', ['tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeImages()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeImage', ['tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStorageFiles0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsStorageFile', ['id' => 'storage_file_id'])->viaTable('cms_tree_image', ['tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeProperty', ['element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getFaqEmails()
    {
        return $this->hasMany($this->called_class_namespace . '\FaqEmail', ['tree_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsGift2019s()
    {
        return $this->hasMany($this->called_class_namespace . '\SsGift2019', ['cms_tree_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsTreeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsTreeQuery(get_called_class());
    }
}
