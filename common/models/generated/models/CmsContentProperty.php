<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_content_property".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $name Name
 * @property string $code Code
 * @property integer $content_id Content ID
 * @property string $active Active
 * @property integer $priority Priority
 * @property string $property_type Property Type
 * @property string $list_type List Type
 * @property string $multiple Multiple
 * @property integer $multiple_cnt Multiple Cnt
 * @property string $with_description With Description
 * @property string $searchable Searchable
 * @property string $filtrable Filtrable
 * @property string $is_required Is Required
 * @property integer $version Version
 * @property string $component Component
 * @property string $component_settings Component Settings
 * @property string $hint Hint
 * @property string $smart_filtrable Smart Filtrable
 * @property integer $vendor_id Vendor ID
 * @property string $filter_name Filter Name
 * @property string $widget_name Widget Name
 * @property string $item_name Item Name
 * @property string $is_admin_show Is Admin Show
 *
     * @property CmsContentElementProperty[] $cmsContentElementProperties
     * @property CmsContent $content
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
     * @property CmsContentPropertyEnum[] $cmsContentPropertyEnums
    */
class CmsContentProperty extends \common\ActiveRecord
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
        return 'cms_content_property';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'content_id', 'priority', 'multiple_cnt', 'version', 'vendor_id'], 'integer'],
            [['name', 'code'], 'required'],
            [['component_settings'], 'string'],
            [['name', 'code', 'component', 'hint', 'filter_name', 'widget_name', 'item_name'], 'string', 'max' => 255],
            [['active', 'property_type', 'list_type', 'multiple', 'with_description', 'searchable', 'filtrable', 'is_required', 'smart_filtrable', 'is_admin_show'], 'string', 'max' => 1],
            [['code', 'content_id'], 'unique', 'targetAttribute' => ['code', 'content_id'], 'message' => 'The combination of Code and Content ID has already been taken.'],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContent::className(), 'targetAttribute' => ['content_id' => 'id']],
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
            'content_id' => 'Content ID',
            'active' => 'Active',
            'priority' => 'Priority',
            'property_type' => 'Property Type',
            'list_type' => 'List Type',
            'multiple' => 'Multiple',
            'multiple_cnt' => 'Multiple Cnt',
            'with_description' => 'With Description',
            'searchable' => 'Searchable',
            'filtrable' => 'Filtrable',
            'is_required' => 'Is Required',
            'version' => 'Version',
            'component' => 'Component',
            'component_settings' => 'Component Settings',
            'hint' => 'Hint',
            'smart_filtrable' => 'Smart Filtrable',
            'vendor_id' => 'Vendor ID',
            'filter_name' => 'Filter Name',
            'widget_name' => 'Widget Name',
            'item_name' => 'Item Name',
            'is_admin_show' => 'Is Admin Show',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementProperty', ['property_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContent()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContent', ['id' => 'content_id']);
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
    public function getCmsContentPropertyEnums()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentPropertyEnum', ['property_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsContentPropertyQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsContentPropertyQuery(get_called_class());
    }
}
