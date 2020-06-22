<?php

namespace common\models\cmsContent;

use common\helpers\User as UserHelper;
use common\models\user\User;
use modules\shopandshow\models\questions\QuestionEmail;
use skeeks\cms\models\Core;

/**
 * This is the model class for table "cms_content_element_faq".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $element_id
 * @property string $username
 * @property string $email
 * @property string $question
 * @property string $answer
 * @property integer $like
 * @property integer $dislike
 * @property integer $status
 * @property string $user_ip
 * @property string $url
 * @property integer $editor_lastview_at
 * @property integer $sent_service_at
 * @property integer $sent_buyer_at
 * @property integer $published_at
 * @property string $buyer_answer
 * @property string $service_answer
 * @property string $copyright_answer
 * @property integer $is_sms_notification
 * @property integer $type
 * @property integer $fuser_id
 * @property integer $parent_id
 * @property string $phone
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property CmsContentElement $element
 */
class ContentElementFaq extends Core
{

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_SPAM = 2;
    const STATUS_DELETED = 3;
    const STATUS_PROBLEM = 4;
    const STATUS_PUBLISHED = self::STATUS_APPROVED;

    const TYPE_QUESTION_PRODUCT_CARD = 1;
    const TYPE_QUESTION_CLIENT_SERVICES = 2;


    // Модератор раздела в админке (все права)
    const PERM_EDIT = 'faq-edit';
    // баер
    const PERM_BUYER = 'buyer/faq-edit';
    // сервисник
    const PERM_SERVICE = 'service/faq-edit';
    // копирайтер
    const PERM_COPYRIGHT = 'copyright/faq-edit';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cms_content_element_faq';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'element_id', 'like', 'dislike', 'status',
                'editor_lastview_at', 'sent_service_at', 'sent_buyer_at', 'published_at', 'is_sms_notification', 'fuser_id',
                'type', 'parent_id'], 'integer'],
            [['element_id'], 'required'],
            [['question', 'answer', 'buyer_answer', 'service_answer', 'copyright_answer', 'phone'], 'string'],
            [['username', 'email'], 'string', 'max' => 128],
            [['user_ip'], 'string', 'max' => 15],
            [['url'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],

            [['buyer_answer'], 'validateBuyer'],
            [['service_answer'], 'validateService'],
            [['copyright_answer'], 'validateCopyright'],
            [['answer',  'email'], 'validateAdmin'], //'username',
            [['question'], 'validateAdminOrNewRecord'],
            [['status'], 'validateStatus'],
            [['published_at'], 'validateCopyrightOrAdmin'],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Создано',
            'updated_by' => 'Изменено',
            'created_at' => 'Дата добавления',
            'updated_at' => 'Дата изменения',
            'element_id' => 'Продукт',
            'username' => 'Автор',
            'email' => 'Email',
            'question' => 'Вопрос',
            'answer' => 'Ответ (виден клиенту)',
            'like' => 'Like',
            'dislike' => 'Dislike',
            'status' => 'Статус',
            'is_sms_notification' => 'is_sms_notification',
            'type' => 'type',
            'parent_id' => 'parent_id',
            'user_ip' => 'User Ip',
            'phone' => 'phone',
            'fuser_id' => 'fuser_id',
            'url' => 'Url',
            'editor_lastview_at' => 'Просмотрено редактором',
            'sent_service_at' => 'Отправлено в сервис',
            'sent_buyer_at' => 'Отправлено баеру',
            'published_at' => 'Дата публикации',
            'buyer_answer' => 'Комментарий баера (не виден клиенту)',
            'service_answer' => 'Комментарий сервиса (не виден клиенту)',
            'copyright_answer' => 'Ответ копирайтера (виден клиенту вместо оригинального ответа)',
        ];
    }

    /**
     * getTypeList
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING => 'Не одобрен',
            self::STATUS_APPROVED => 'Одобрен',
            self::STATUS_SPAM => 'Спам',
            self::STATUS_DELETED => 'Удален',
            //self::STATUS_PROBLEM => 'Проблема у клиента',
        ];
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return self::getStatusList()[$this->status];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElement()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'element_id']);
    }

    /**
     * Валидация на признак баера
     * @param $attribute
     */
    public function validateBuyer($attribute)
    {
        if ($this->isAttributeChanged($attribute) && !UserHelper::can(self::PERM_BUYER)) {
            $this->addError($attribute, 'Недостаточно прав');
        }
    }

    /**
     * Валидация на признак клиентского сервиса
     * @param $attribute
     */
    public function validateService($attribute)
    {
        if ($this->isAttributeChanged($attribute) && !UserHelper::can(self::PERM_SERVICE)) {
            $this->addError($attribute, 'Недостаточно прав');
        }
    }

    /**
     * Валидация на признак отдела копирайтинга
     * @param $attribute
     */
    public function validateCopyright($attribute)
    {
        if ($this->isAttributeChanged($attribute) && !UserHelper::can(self::PERM_COPYRIGHT)) {
            $this->addError($attribute, 'Недостаточно прав');
        }
    }


    /**
     * Валидация на признак админа
     * @param $attribute
     */
    public function validateAdmin($attribute)
    {
        if ($this->isAttributeChanged($attribute) && !UserHelper::can(self::PERM_EDIT)) {
            $this->addError($attribute, 'Недостаточно прав');
        }
    }

    public function validateCopyrightOrAdmin($attribute)
    {
        if ($this->isAttributeChanged($attribute) && !UserHelper::can(self::PERM_EDIT) && !UserHelper::can(self::PERM_COPYRIGHT)) {
            $this->addError($attribute, 'Недостаточно прав');
        }
    }

    public function validateAdminOrNewRecord($attribute)
    {
        if ($this->isNewRecord) {
            return;
        }

        $this->validateAdmin($attribute);
    }

    public function validateCopyrightOrNewRecord($attribute)
    {
        if ($this->isNewRecord) {
            return;
        }

        $this->validateCopyright($attribute);
    }

    public function validateStatus($attribute)
    {
        if ($this->isNewRecord) {
            return;
        }

        if ($this->isAttributeChanged($attribute) && !UserHelper::can(self::PERM_EDIT) && !UserHelper::can(self::PERM_COPYRIGHT)) {
            $this->addError($attribute, 'Недостаточно прав');
        }

        if ($this->status == self::STATUS_PUBLISHED) {
            if (empty($this->published_at)) {
                $this->addError($attribute, 'Дата публикации не указана');
            }
            if ($this->published_at < $this->created_at) {
                $this->addError($attribute, 'Дата публикации не может быть раньше даты создания вопроса');
            }
            if (empty($this->getFinalAnswer())) {
                $this->addError($attribute, 'Не указан ответ');
            }
        }
    }

    public function getFinalQuestion()
    {
        return nl2br($this->question);
    }

    public function getFinalAnswer()
    {
        if (!empty($this->copyright_answer)) {
            return nl2br($this->copyright_answer);
        }

        /*if (!empty($this->service_answer)) {
            return nl2br($this->service_answer);
        }*/

        return nl2br($this->answer);
    }

    /**
     * @return string
     */
    public function sendMailToBuyer()
    {
        $buyerMails = QuestionEmail::findForBuyer($this)->all();
        $buyerMails = \common\helpers\ArrayHelper::getColumn($buyerMails, 'email');

        $product = $this->element;
        $subject = "Вопрос клиента по товару " . $product->getLotName() . " [" . $product->relatedPropertiesModel->getAttribute('LOT_NUM') . "] требует ответа";

        $result = \Yii::$app->mailer->compose('@mail/modules/faq/admin_send_buyer', [
            'model' => $this
        ])
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
            ->setTo($buyerMails)
            ->setSubject($subject)
            ->send();

        if ($result) {
            $this->sent_buyer_at = time();
            $this->save(false, ['sent_buyer_at']);
        }

        return $result ? 'Сообщение отправлено' : 'Не удалось отправить сообщение';
    }

    /**
     * @return string
     */
    public function sendMailToService()
    {
        $serviceMails = QuestionEmail::findForService($this)->all();
        $serviceMails = \common\helpers\ArrayHelper::getColumn($serviceMails, 'email');

        $subject = "Проблема у клиента сайта Shopandshow.ru требует ответа";

        $result = \Yii::$app->mailer->compose('@mail/modules/faq/admin_send_service', [
            'model' => $this
        ])
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
            ->setTo($serviceMails)
            ->setSubject($subject)
            ->send();

        if ($result) {
            $this->sent_service_at = time();
            $this->save(false, ['sent_service_at']);
        }

        return $result ? 'Сообщение отправлено' : 'Не удалось отправить сообщение';
    }

    /**
     * Признак вопроса для карточки товара
     * @return bool
     */
    public function isTypeProductCard()
    {
        return $this->type === self::TYPE_QUESTION_PRODUCT_CARD;
    }

    /**
     * Признак вопроса из клиентского сервиса
     * @return bool
     */
    public function isTypeClientServices()
    {
        return $this->type === self::TYPE_QUESTION_CLIENT_SERVICES;
    }
}
