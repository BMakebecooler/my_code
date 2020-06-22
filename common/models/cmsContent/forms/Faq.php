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
 * @property integer $productId
 * @property string $username
 * @property string $question
 * @property string $answer
 */
class Faq extends Model
{


    public $answer_id;
    public $question;
    public $answer;
    public $username;
    public $productId;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['answer_id'], 'integer'],
            [['question', 'productId'], 'required'],
            [['question', 'answer', 'username'], 'string'],
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'productId' => 'Element ID',
            'username' => 'Username',
            'email' => 'Email',
            'question' => 'Ваш вопрос',
            'answer' => 'Ответ',
            'like' => 'Like',
            'dislike' => 'Dislike',
            'status' => 'Status',
            'user_ip' => 'User Ip',
            'url' => 'Url',
        ];
    }

    /**
     * @return bool
     */
    public function processed()
    {
        $question = new ContentElementFaq();

        $question->element_id = $this->productId;
        $question->question = Html::encode($this->question);
        $question->answer = $this->answer;
        $question->username = $this->username;
        $question->status = ContentElementFaq::STATUS_PENDING;

        if (User::isEditor()) {
            $question->status = ContentElementFaq::STATUS_PUBLISHED;
            // типа ответ через 10-30 минут
            $question->published_at = time() + rand(120, 420);
//            $question->published_at = time() + rand(600, 1800);
        }

        $result = $question->save();

        if (!$result) {
            var_dump($question->getErrors());
            die();
        }


        return $result;
    }

}
