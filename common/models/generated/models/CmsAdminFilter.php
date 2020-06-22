<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_admin_filter".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $cms_user_id Cms User ID
 * @property integer $is_default Is Default
 * @property string $name Name
 * @property string $namespace Namespace
 * @property string $values Values
 * @property string $visibles Visibles
 *
     * @property CmsUser $cmsUser
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class CmsAdminFilter extends \common\ActiveRecord
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
        return 'cms_admin_filter';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'cms_user_id', 'is_default'], 'integer'],
            [['namespace'], 'required'],
            [['values', 'visibles'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['namespace'], 'string', 'max' => 255],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['cms_user_id' => 'id']],
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
            'cms_user_id' => 'Cms User ID',
            'is_default' => 'Is Default',
            'name' => 'Name',
            'namespace' => 'Namespace',
            'values' => 'Values',
            'visibles' => 'Visibles',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'cms_user_id']);
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
     * @inheritdoc
     * @return \common\models\query\CmsAdminFilterQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsAdminFilterQuery(get_called_class());
    }
}
