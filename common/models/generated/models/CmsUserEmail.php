<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_user_email".
 *
 * @property integer $id ID
 * @property integer $user_id User ID
 * @property string $value Value
 * @property string $approved Approved
 * @property string $def Def
 * @property string $approved_key Approved Key
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $source Source
 * @property string $source_detail Source Detail
 * @property string $is_valid_site Is Valid Site
 * @property string $is_send_coupon_500r Is Send Coupon 500r
 * @property integer $value_type Value Type
 * @property string $ip Ip
 * @property integer $approved_rr Approve Rr
 *
 * @property CmsUser $user
 */
class CmsUserEmail extends \common\ActiveRecord
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
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cms_user_email';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at', 'value_type', 'approved_rr'], 'integer'],
            [['value', 'approved_key', 'source', 'source_detail', 'ip'], 'string', 'max' => 255],
            [['approved', 'def', 'is_valid_site', 'is_send_coupon_500r'], 'string', 'max' => 1],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'value' => 'Value',
            'approved' => 'Approved',
            'def' => 'Def',
            'approved_key' => 'Approved Key',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'source' => 'Source',
            'source_detail' => 'Source Detail',
            'is_valid_site' => 'Is Valid Site',
            'is_send_coupon_500r' => 'Is Send Coupon 500r',
            'value_type' => 'Value Type',
            'ip' => 'Ip',
            'approved_rr' => 'Approve Rr',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
     */
    public function getUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CmsUserEmailQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CmsUserEmailQuery(get_called_class());
    }
}
