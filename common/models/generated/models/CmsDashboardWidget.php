<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_dashboard_widget".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $cms_dashboard_id Cms Dashboard ID
 * @property integer $cms_dashboard_column Cms Dashboard Column
 * @property integer $priority Priority
 * @property string $component Component
 * @property string $component_settings Component Settings
 *
     * @property CmsDashboard $cmsDashboard
     * @property CmsUser $createdBy
     * @property CmsUser $updatedBy
    */
class CmsDashboardWidget extends \common\ActiveRecord
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
        return 'cms_dashboard_widget';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'cms_dashboard_id', 'cms_dashboard_column', 'priority'], 'integer'],
            [['cms_dashboard_id', 'component'], 'required'],
            [['component_settings'], 'string'],
            [['component'], 'string', 'max' => 255],
            [['cms_dashboard_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsDashboard::className(), 'targetAttribute' => ['cms_dashboard_id' => 'id']],
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
            'cms_dashboard_id' => 'Cms Dashboard ID',
            'cms_dashboard_column' => 'Cms Dashboard Column',
            'priority' => 'Priority',
            'component' => 'Component',
            'component_settings' => 'Component Settings',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsDashboard()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsDashboard', ['id' => 'cms_dashboard_id']);
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
     * @return \common\models\query\CmsDashboardWidgetQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsDashboardWidgetQuery(get_called_class());
    }
}
