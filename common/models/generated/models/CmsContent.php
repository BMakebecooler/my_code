<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_content".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property string $code Code
 * @property string $active Active
 * @property integer $priority Priority
 * @property string $description Description
 * @property string $index_for_search Index For Search
 * @property string $name_meny Name Meny
 * @property string $name_one Name One
 * @property string $tree_chooser Tree Chooser
 * @property string $list_mode List Mode
 * @property string $content_type Content Type
 * @property integer $default_tree_id Default Tree ID
 * @property string $is_allow_change_tree Is Allow Change Tree
 * @property integer $root_tree_id Root Tree ID
 * @property string $viewFile View File
 * @property string $meta_title_template Meta Title Template
 * @property string $meta_description_template Meta Description Template
 * @property string $meta_keywords_template Meta Keywords Template
 * @property string $access_check_element Access Check Element
 * @property integer $parent_content_id Parent Content ID
 * @property string $visible Visible
 * @property string $parent_content_on_delete Parent Content On Delete
 * @property string $parent_content_is_required Parent Content Is Required
 * @property integer $guid_id Guid ID
 *
     * @property CmsContent $parentContent
     * @property CmsContent[] $cmsContents
     * @property CmsTree $defaultTree
     * @property CmsTree $rootTree
     * @property CmsContentType $contentType
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property CmsContentElement[] $cmsContentElements
     * @property CmsContentProperty[] $cmsContentProperties
     * @property Reviews2Message[] $reviews2Messages
     * @property ShopContent[] $shopContents
     * @property ShopContent $shopContent
    */
class CmsContent extends \common\ActiveRecord
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
        return 'cms_content';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'default_tree_id', 'root_tree_id', 'parent_content_id', 'guid_id'], 'integer'],
            [['name', 'code', 'content_type'], 'required'],
            [['description', 'meta_description_template', 'meta_keywords_template'], 'string'],
            [['name', 'code', 'viewFile'], 'string', 'max' => 255],
            [['active', 'index_for_search', 'tree_chooser', 'list_mode', 'is_allow_change_tree', 'access_check_element', 'visible', 'parent_content_is_required'], 'string', 'max' => 1],
            [['name_meny', 'name_one'], 'string', 'max' => 100],
            [['content_type'], 'string', 'max' => 32],
            [['meta_title_template'], 'string', 'max' => 500],
            [['parent_content_on_delete'], 'string', 'max' => 10],
            [['code'], 'unique'],
            [['parent_content_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContent::className(), 'targetAttribute' => ['parent_content_id' => 'id']],
            [['default_tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['default_tree_id' => 'id']],
            [['root_tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['root_tree_id' => 'id']],
            [['content_type'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentType::className(), 'targetAttribute' => ['content_type' => 'code']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
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
            'code' => 'Code',
            'active' => 'Active',
            'priority' => 'Priority',
            'description' => 'Description',
            'index_for_search' => 'Index For Search',
            'name_meny' => 'Name Meny',
            'name_one' => 'Name One',
            'tree_chooser' => 'Tree Chooser',
            'list_mode' => 'List Mode',
            'content_type' => 'Content Type',
            'default_tree_id' => 'Default Tree ID',
            'is_allow_change_tree' => 'Is Allow Change Tree',
            'root_tree_id' => 'Root Tree ID',
            'viewFile' => 'View File',
            'meta_title_template' => 'Meta Title Template',
            'meta_description_template' => 'Meta Description Template',
            'meta_keywords_template' => 'Meta Keywords Template',
            'access_check_element' => 'Access Check Element',
            'parent_content_id' => 'Parent Content ID',
            'visible' => 'Visible',
            'parent_content_on_delete' => 'Parent Content On Delete',
            'parent_content_is_required' => 'Parent Content Is Required',
            'guid_id' => 'Guid ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getParentContent()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContent', ['id' => 'parent_content_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContents()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContent', ['parent_content_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getDefaultTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'default_tree_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getRootTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'root_tree_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContentType()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentType', ['code' => 'content_type']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['content_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentProperty', ['content_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getReviews2Messages()
    {
        return $this->hasMany($this->called_class_namespace . '\Reviews2Message', ['content_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopContents()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopContent', ['children_content_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopContent()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopContent', ['content_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsContentQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsContentQuery(get_called_class());
    }
}
