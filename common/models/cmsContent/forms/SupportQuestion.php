<?php

namespace common\models\cmsContent\forms;

use common\helpers\User;
use common\models\cmsContent\ContentElementFaq;
use yii\base\Model;
use yii\helpers\Html;

/**
 * This is the model class for table "cms_content_element_faq".
 *
 * @property integer $id
 * @property integer $element_id
 * @property integer $parent_id
 * @property integer $is_sms_notification
 * @property string $username
 * @property string $email
 * @property string $phone
 * @property string $question
 * @property string $answer
 */
class SupportQuestion extends Model
{


    public $answer_id;
    public $question;
    public $answer;
    public $username;
    public $email;
    public $phone;
    public $element_id;
    public $parent_id;
    public $is_sms_notification;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['answer_id', 'is_sms_notification', 'element_id', 'parent_id'], 'integer'],
            [['question', 'element_id'], 'required'],
            [['question', 'answer', 'username', 'email' ,'phone'], 'string'],
            [['username'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'element_id' => 'Element ID',
            'parent_id' => 'parent_id',
            'answer_id' => 'answer_id',
            'is_sms_notification' => 'is_sms_notification',
            'username' => 'Username',
            'email' => 'Email',
            'question' => 'Ваш вопрос',
            'phone' => 'phone',
        ];
    }

    /**
     * @return bool
     */
    public function processed()
    {
        $question = new ContentElementFaq();

        $question->element_id = $this->element_id;
        $question->is_sms_notification = $this->is_sms_notification;
        $question->username = $this->username;
        $question->email = $this->email;
        $question->phone = $this->phone;

        $question->user_ip = \Yii::$app->getRequest()->getUserIP();

        $question->question = Html::encode($this->question);
        $question->answer = $this->answer;
        $question->fuser_id = User::getSessionId();
        $question->created_by = User::getAuthorizeId();
        $question->status = ContentElementFaq::STATUS_PENDING;
        $question->type = ContentElementFaq::TYPE_QUESTION_CLIENT_SERVICES;
        $question->parent_id = $this->parent_id;

        $result = $question->save();

        if (!$result) {
            var_dump($question->getErrors());
            die();
        }

        return $result;
    }

}
