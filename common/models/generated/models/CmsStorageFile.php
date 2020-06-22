<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_storage_file".
 *
 * @property integer $id ID
 * @property string $cluster_id Cluster ID
 * @property string $cluster_file Cluster File
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $size Size
 * @property string $mime_type Mime Type
 * @property string $extension Extension
 * @property string $original_name Original Name
 * @property string $name_to_save Name To Save
 * @property string $name Name
 * @property string $description_short Description Short
 * @property string $description_full Description Full
 * @property integer $image_height Image Height
 * @property integer $image_width Image Width
 * @property integer $bitrix_id Bitrix ID
 *
     * @property CmsContentElement[] $cmsContentElements
     * @property CmsContentElement[] $cmsContentElements0
     * @property CmsContentElementFile[] $cmsContentElementFiles
     * @property CmsContentElement[] $contentElements
     * @property CmsContentElementImage[] $cmsContentElementImages
     * @property CmsContentElement[] $contentElements0
     * @property CmsLang[] $cmsLangs
     * @property CmsSite[] $cmsSites
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property CmsTree[] $cmsTrees
     * @property CmsTree[] $cmsTrees0
     * @property CmsTreeFile[] $cmsTreeFiles
     * @property CmsTree[] $trees
     * @property CmsTreeImage[] $cmsTreeImages
     * @property CmsTree[] $trees0
     * @property CmsUser[] $cmsUsers
     * @property SavedFilters[] $savedFilters
     * @property ShopDelivery[] $shopDeliveries
     * @property ShopDiscount[] $shopDiscounts
     * @property ShopStore[] $shopStores
    */
class CmsStorageFile extends \common\ActiveRecord
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
        return 'cms_storage_file';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'size', 'image_height', 'image_width', 'bitrix_id'], 'integer'],
            [['description_short', 'description_full'], 'string'],
            [['cluster_id', 'mime_type', 'extension'], 'string', 'max' => 16],
            [['cluster_file', 'original_name', 'name'], 'string', 'max' => 255],
            [['name_to_save'], 'string', 'max' => 32],
            [['cluster_id', 'cluster_file'], 'unique', 'targetAttribute' => ['cluster_id', 'cluster_file'], 'message' => 'The combination of Cluster ID and Cluster File has already been taken.'],
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
            'cluster_id' => 'Cluster ID',
            'cluster_file' => 'Cluster File',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'size' => 'Size',
            'mime_type' => 'Mime Type',
            'extension' => 'Extension',
            'original_name' => 'Original Name',
            'name_to_save' => 'Name To Save',
            'name' => 'Name',
            'description_short' => 'Description Short',
            'description_full' => 'Description Full',
            'image_height' => 'Image Height',
            'image_width' => 'Image Width',
            'bitrix_id' => 'Bitrix ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['image_full_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElements0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['image_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementFile', ['storage_file_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContentElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['id' => 'content_element_id'])->viaTable('cms_content_element_file', ['storage_file_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementImages()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementImage', ['storage_file_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContentElements0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['id' => 'content_element_id'])->viaTable('cms_content_element_image', ['storage_file_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsLangs()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsLang', ['image_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsSites()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsSite', ['image_id' => 'id']);
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
    public function getCmsTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['image_full_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTrees0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['image_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeFile', ['storage_file_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id'])->viaTable('cms_tree_file', ['storage_file_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsTreeImages()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTreeImage', ['storage_file_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTrees0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id'])->viaTable('cms_tree_image', ['storage_file_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUsers()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUser', ['image_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSavedFilters()
    {
        return $this->hasMany($this->called_class_namespace . '\SavedFilters', ['image_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDeliveries()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDelivery', ['logo_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopDiscounts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopDiscount', ['image_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopStores()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopStore', ['image_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsStorageFileQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsStorageFileQuery(get_called_class());
    }
}
