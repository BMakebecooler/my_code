<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_content".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $content_id Content ID
 * @property string $yandex_export Yandex Export
 * @property string $subscription Subscription
 * @property integer $vat_id Vat ID
 * @property integer $children_content_id Children Content ID
 *
     * @property CmsContent $childrenContent
     * @property CmsContent $content
     * @property CmsUser $createdBy
     * @property ShopVat $vat
     * @property CmsUser $updatedBy
    */
class ShopContent extends \common\ActiveRecord
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
        return 'shop_content';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'content_id', 'vat_id', 'children_content_id'], 'integer'],
            [['content_id'], 'required'],
            [['yandex_export', 'subscription'], 'string', 'max' => 1],
            [['content_id'], 'unique'],
            [['children_content_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContent::className(), 'targetAttribute' => ['children_content_id' => 'id']],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContent::className(), 'targetAttribute' => ['content_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['vat_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopVat::className(), 'targetAttribute' => ['vat_id' => 'id']],
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
            'content_id' => 'Content ID',
            'yandex_export' => 'Yandex Export',
            'subscription' => 'Subscription',
            'vat_id' => 'Vat ID',
            'children_content_id' => 'Children Content ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getChildrenContent()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContent', ['id' => 'children_content_id']);
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
    public function getVat()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopVat', ['id' => 'vat_id']);
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
     * @return \common\models\query\ShopContentQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopContentQuery(get_called_class());
    }
}
