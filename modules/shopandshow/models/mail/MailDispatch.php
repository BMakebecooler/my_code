<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 12.09.2017
 * Time: 17:53
 */

namespace modules\shopandshow\models\mail;

use common\helpers\ArrayHelper;
use skeeks\cms\models\CmsUser;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Class MailDispatch
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $mail_template_id
 *
 * @property string $status
 * @property string $from
 * @property string $to
 * @property string $segments
 * @property string $subject
 * @property string $body
 * @property string $message
 *
 * @property MailTemplate $mailTemplate
 *
 * @package modules\shopandshow\models\mail
 */
class MailDispatch extends \yii\db\ActiveRecord
{

    const STATUS_DRAFT = 'D';
    const STATUS_SENT = 'S';
    const STATUS_CANCEL = 'C';

    const DISPATCHES_LIMIT_PER_DAY = 8; //временно 8, должно быть 3, ну максимум 4

    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT  => 'Черновик',
            self::STATUS_SENT   => 'Отправлено',
            self::STATUS_CANCEL => 'Отменено',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_mail_dispatch}}';
    }

    public function init()
    {
        parent::init();
        if($this->isNewRecord) $this->status = self::STATUS_DRAFT;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            BlameableBehavior::className(),
            TimestampBehavior::className(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'mail_template_id'], 'integer'],
            [['mail_template_id'], 'exist', 'skipOnError' => true, 'targetClass' => MailTemplate::className(), 'targetAttribute' => ['mail_template_id' => 'id']],
            [['status'], 'string', 'max' => 32],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['from', 'to'], 'email'],
            [['subject', 'segments'], 'string', 'max' => 512],
            [['status', 'from', 'subject', 'body', 'mail_template_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by'            => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by'            => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at'            => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at'            => \Yii::t('skeeks/shop/app', 'Updated At'),
            'mail_template_id'      => 'Шаблон',
            'status'                => 'Состояние',
            'from'                  => 'От кого',
            'to'                    => 'Кому',
            'segments'              => 'Сегменты',
            'subject'               => 'Тема',
            'body'                  => 'Текст',
            'message'               => 'Сообщение',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(CmsUser::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailTemplate()
    {
        return $this->hasOne(MailTemplate::className(), ['id' => 'mail_template_id']);
    }

    public function setMailTemplate(MailTemplate $mailTemplate)
    {
        $this->mail_template_id = $mailTemplate->id;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function send()
    {
        $message = \Yii::$app->mailer->compose()
            ->setFrom($this->from)
            ->setTo(array_map('trim', explode(',', $this->to)))
            ->setSubject($this->subject)
            ->setHtmlBody($this->body);

        if($send = $message->send()) {
            $this->setStatus(self::STATUS_SENT);
        }
        else {
            $this->setStatus(self::STATUS_CANCEL);
        }
        $this->save(false);

        return $send;
    }

    /** Получение списка рассылок за сегодняшний день
     * @param $filters - данные фильтра для участия в where
     * @return array
     */
    public static function getTodayDispatches ($filters = null){
        $mailDispatches = self::find()
            ->where(['>=', 'created_at', new Expression("UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d 00:00:00'))")])
            ->asArray();

        if ($filters){
            $mailDispatches->andWhere($filters);
        }

        return $mailDispatches->all();
    }

    /**
     * @param string $segments
     * @return MailDispatch
     */
    public function setSegments(string $segments): MailDispatch
    {
        $this->segments = $segments;
        return $this;
    }

    /**
     * @param string $message
     * @return MailDispatch
     */
    public function setMessage(string $message): MailDispatch
    {
        $this->message = $message;
        return $this;
    }
}