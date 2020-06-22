<?php

namespace modules\shopandshow\models\users;

use common\models\user\User;
use skeeks\cms\validators\PhoneValidator;

/**
 * This is the model class for table "contact_data_bitrix_users".
 *
 * @property integer $id
 * @property string $phone
 * @property string $email
 * @property string $guid
 * @property User $user
 */
class ContactDataBitrixUser extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact_data_bitrix_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['id'], 'required'],
            [['phone'], 'string', 'max' => 255],
            [['guid'], 'string', 'max' => 64],
            [['phone'], PhoneValidator::className()],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bx_user_id' => 'ID',
            'phone' => 'Phone',
            'email' => 'Email',
            'guid' => 'guid',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['bitrix_id' => 'id']);
    }
}
