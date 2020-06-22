<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_mail_template".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property string $active Active
 * @property resource $name Name
 * @property string $template Template
 * @property string $from From
 * @property integer $tree_id Tree ID
 *
     * @property SsMailDispatch[] $ssMailDispatches
     * @property SsMailSubject[] $ssMailSubjects
    */
class SsMailTemplate extends \common\ActiveRecord
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
            'author' => \yii\behaviors\BlameableBehavior::class,
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'ss_mail_template';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'tree_id'], 'integer'],
            [['active', 'template', 'from'], 'required'],
            [['active'], 'string', 'max' => 1],
            [['name', 'from'], 'string', 'max' => 255],
            [['template'], 'string', 'max' => 1024],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'active' => 'Active',
            'name' => 'Name',
            'template' => 'Template',
            'from' => 'From',
            'tree_id' => 'Tree ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsMailDispatches()
    {
        return $this->hasMany($this->called_class_namespace . '\SsMailDispatch', ['mail_template_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsMailSubjects()
    {
        return $this->hasMany($this->called_class_namespace . '\SsMailSubject', ['template_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsMailTemplateQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsMailTemplateQuery(get_called_class());
    }
}
