<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "contact_data_bitrix_users".
 *
 * @property integer $id ID
 * @property string $phone Phone
 * @property string $email Email
 * @property string $guid Guid
*/
class ContactDataBitrixUsers extends \common\ActiveRecord
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
            [['email'], 'required'],
            [['phone', 'email'], 'string', 'max' => 255],
            [['guid'], 'string', 'max' => 64],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'email' => 'Email',
            'guid' => 'Guid',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ContactDataBitrixUsersQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ContactDataBitrixUsersQuery(get_called_class());
    }
}
