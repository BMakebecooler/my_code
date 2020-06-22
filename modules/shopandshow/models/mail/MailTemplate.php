<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 12.09.2017
 * Time: 17:53
 */

namespace modules\shopandshow\models\mail;

use common\helpers\ArrayHelper;
use common\models\user\User;
use common\models\Tree;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\web\Application as webApplication;


/**
 * Class MailTemplate
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $mail_template_id
 *
 * @property string $name
 * @property string $active
 * @property string $template
 * @property string $from
 * @property integer $tree_id
 *
 * @property Tree $tree
 *
 * @package modules\shopandshow\models\mail
 */
class MailTemplate extends \yii\db\ActiveRecord
{
    public $templatesDir = '@modules/shopandshow/components/mail/template';

    /** параметры для тестовой рассылки */
    public $mail_to;
    public $begin_date;

    protected $_templates = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_mail_template}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->isNewRecord) {
            $this->active = \skeeks\cms\components\Cms::BOOL_Y;
            $this->from = 'info@email.shopandshow.ru';
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
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'tree_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
            [['template'], 'string', 'max' => 1024],
            [['from'], 'email'],
            [['from'], 'default', 'value' => \Yii::$app->user->identity->email],
            [['name', 'active', 'template', 'from'], 'required'],
            [['mail_to', 'begin_date', 'message'], 'safe']
        ];
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
            'name' => 'Тема письма по умолчанию',
            'active' => 'Активен',
            'template' => 'Шаблон',
            'from' => 'От кого',
            'tree_id' => 'Категория товаров в рассылке',
            'ActiveSubject' => 'Тема письма'
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
     * @return \yii\db\ActiveQuery
     */
    public function getMailDispatch()
    {
        return $this->hasMany(MailDispatch::className(), ['mail_template_id' => 'id'])->indexBy('id')->orderBy('created_at DESC')->inverseOf('mailTemplate');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTree()
    {
        return $this->hasOne(Tree::className(), ['id' => 'tree_id']);
    }

    /**
     * Формирует список доступных шаблонов
     * @return array
     */
    public function getTemplates()
    {
        if (!$this->_templates) {
            $files = new \DirectoryIterator(\Yii::getAlias($this->templatesDir));

            foreach ($files as $fileInfo) {
                if ($fileInfo->isDot()) continue;

                if ($fileInfo->getExtension() == 'php') {
                    $template = $fileInfo->getBasename('.php');
                    $this->_templates[$template] = $template;
                }
            }
        }

        return $this->_templates;
    }

    /**
     * генерирует рассылку из шаблона
     * @param $useLinksForGetresponse boolean генерить ссылки в формате getresponse
     * @param $data array
     * @return MailDispatch
     */
    public function generate($useLinksForGetresponse = false, $data = [])
    {
        $mailDispatch = new MailDispatch();
        $mailDispatch->setMailTemplate($this);
        $mailDispatch->from = $this->from;
        $mailDispatch->to = $this->mail_to;
        $mailDispatch->subject = $this->getActiveSubject();

        $generatorClass = $this->getGeneratorClass();
        $config = [
            'mail_dispatch' => $mailDispatch,
            'tree_id' => $this->tree_id,
            'begin_date' => $this->begin_date,
            'useLinksForGetresponse' => $useLinksForGetresponse,
            'data' => $data,
        ];
        $generator = new $generatorClass($config);
        $mailDispatch->body = $generator->render();

        $mailDispatch->save();

        return $mailDispatch;
    }

    protected function getGeneratorClass()
    {
        $path = str_replace(['@', '/'], ['', '\\'], $this->templatesDir);
        return $path . '\\' . $this->template;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    private function getSubjects()
    {
        return $this->hasMany(MailSubject::className(), ['template_id' => 'id']);
    }

    /**
     * Return scheduled mailSubject for this template if it is or template's default subject if not
     * used in modules/shopandshow/views/mail/admin-mail-template/check.php what's why not private
     *
     * @return string
     */
    public function getActiveSubject()
    {
        if ((\Yii::$app instanceof webApplication) && !empty(\Yii::$app->request->post('MailTemplate[ActiveSubject]'))) {
            return \Yii::$app->request->post('MailTemplate[ActiveSubject]');
        }

        $now = $this->begin_date ?: time();

        return $this->getActiveSubjectByTimestamp($now);
    }

    /**
     * Return scheduled mailSubject for this template if it is or template's default subject if not
     *
     * @param integer $timestamp
     * @return string
     */
    public function getActiveSubjectByTimestamp($timestamp)
    {
        $timestamp = intval($timestamp);
        /** @var MailSubject $mailSubject */
        $mailSubject = $this->getSubjects()
            ->where(['active' => 'Y'])
            ->andWhere(['<', 'begin_datetime', $timestamp])
            ->andWhere(['>', 'end_datetime', $timestamp])
            ->one();


        if ($mailSubject) {
            return $mailSubject->subject;
        } else {
            return $this->name;
        }
    }
}
