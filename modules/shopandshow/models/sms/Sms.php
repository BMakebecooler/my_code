<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 30.08.17
 * Time: 13:12
 */

namespace modules\shopandshow\models\sms;

use lowbase\sms\models\Sms as SmsModel;
use Yii;

class Sms extends SmsModel
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['phone', 'text'], 'required'],
//            ['phone', 'match', 'pattern' => '/^(\+7){1}\d{10}$/', 'message' => Yii::t('sms', 'Enter phone in format +79801234567.')],
            [['text', 'provider_answer'], 'string'],
            [['type', 'for_user_id', 'status', 'created_by'], 'integer'],
            [['created_at', 'must_sent_at', 'check_status_at'], 'safe'],
            [['provider_sms_id'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['provider'], 'string', 'max' => 100],
            ['status', 'in', 'range' => array_keys(self::getStatuses())],
            ['status', 'default', 'value' => self::STATUS_SENT],
            [['phone', 'text', 'provider_answer'], 'filter', 'filter' => 'trim'],
            [['text', 'provider_sms_id', 'type', 'for_user_id', 'created_by',
                'must_sent_at', 'check_status_at', 'provider', 'provider_answer'], 'default', 'value' => null],
        ];
    }

}