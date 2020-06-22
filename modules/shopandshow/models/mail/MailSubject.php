<?php

namespace modules\shopandshow\models\mail;

use common\helpers\ArrayHelper;
use common\models\user\User;
use skeeks\cms\components\Cms as SkeeksCms;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;


/**
 * Class MailSubject
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property string $name
 * @property string $active
 * @property integer $begin_datetime
 * @property integer $end_datetime
 * @property integer $template_id
 * @property MailTemplate $template
 * @property string $subject [VARCHAR(255)]  Тема
 *
 * @package modules\shopandshow\models\mail
 */
class MailSubject extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_mail_subject}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->isNewRecord) {
            $this->active = SkeeksCms::BOOL_Y;
            $this->begin_datetime = strtotime('tomorrow +7 hours');
            $this->end_datetime = $this->begin_datetime + DAYS_1 - 1;
        }
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
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'template_id'], 'integer'],
            [['name', 'subject'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
            [['name', 'begin_datetime', 'end_datetime', 'subject', 'template_id'], 'required'],
            ['end_datetime', 'compare', 'compareAttribute' => 'begin_datetime', 'operator' => '>=', 'type' => 'number'],
            ['template_id', 'validateNonIntersectionOfDatePeriodForSelectedTemplate', 'params' => ['begin_datetime', 'end_datetime']]
        ];
    }

    public function validateNonIntersectionOfDatePeriodForSelectedTemplate($attribute, $params)
    {
        $mailSubjects = MailSubject::find()
            ->where(['template_id' => $this->$attribute])
            ->andWhere(new Expression(
                //Like custom min() :-)
                    ' IF(begin_datetime > ' . $this->begin_datetime . ', begin_datetime, ' . $this->begin_datetime . ') ' .
                    '<=' .
                    //Like custom max() :-)
                    ' IF(end_datetime < ' . $this->end_datetime . ', end_datetime, ' . $this->end_datetime . ') '
                )
            )->andWhere(['!=', 'id', $this->id])
            ->all();

        if ($mailSubjects) {
            $this->addError($attribute, 'Есть пересечение с другой темой рассылки, назначенной на тот же шаблон и на пересекающийся период времени');
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'name' => 'Название',
            'active' => 'Активность темы рассылки',
            'begin_datetime' => 'Время начала действия темы',
            'end_datetime' => 'Время окончания действия темы',
            'subject' => 'Тема рассылки',
            'template_id' => 'Шаблон',
        ];
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
     * Get mail template
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(MailTemplate::className(), ['id' => 'template_id']);
    }

    /**
     * Выдаёт список всех шаблонов для привязки их к форме
     */
    public function getTemplates()
    {
        return MailTemplate::find()->all();
    }
}
