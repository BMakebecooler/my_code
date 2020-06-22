<?php
namespace modules\shopandshow\models\questions;

use common\models\cmsContent\ContentElementFaq;
use skeeks\cms\models\Tree;

/**
 * Class QuestionEmail
 *
 * @property int    $id
 * @property string $group
 * @property string $type
 * @property int    tree_id
 * @property string $fio
 * @property string $email
 *
 * @package modules\shopandshow\models\questions
 */
class QuestionEmail extends \yii\db\ActiveRecord
{

    const GROUP_SERVICE = 'service';
    const GROUP_BUYER   = 'buyer';

    const TYPE_COMMON   = 'common';
    const TYPE_REPORT   = 'report';
    const TYPE_TREE     = 'tree';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'faq_email';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group', 'type'], 'string', 'max' => 255],
            [['group', 'type'], 'required'],
            [['tree_id'], 'integer'],
            [['fio'], 'string'],
            [['email'], 'required'],
            [['email'], 'email'],
            [['type'], 'validateTypeAndTree']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group' => 'Отдел',
            'type' => 'Тип адреса',
            'tree_id' => 'Рубрика',
            'fio' => 'ФИО',
            'email' => 'E-mail'
        ];
    }

    public function validateTypeAndTree()
    {
        if ($this->type == self::TYPE_TREE) {
            if (empty($this->tree_id)) {
                $this->addError('tree_id', 'Не указан раздел');
            }
        }
        else {
            $this->tree_id = null;
        }
    }

    /**
     * @return array
     */
    public static function getGroupList()
    {
        return [
            self::GROUP_BUYER => 'Отдел закупок',
            self::GROUP_SERVICE => 'Отдел сервиса',
        ];
    }

    /**
     * @return array
     */
    public static function getTypeList()
    {
        return [
            self::TYPE_COMMON => 'Общий адрес',
            self::TYPE_REPORT => 'Ежемесячный отчет',
            self::TYPE_TREE => 'Рубрика',
        ];
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return self::getGroupList()[$this->group];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::getTypeList()[$this->type];
    }

    public static function findForBuyer(ContentElementFaq $contentElementFaq) {
        return self::findForFaq($contentElementFaq, self::GROUP_BUYER);
    }

    public static function findForService(ContentElementFaq $contentElementFaq) {
        return self::findForFaq($contentElementFaq, self::GROUP_SERVICE);
    }

    /**
     * @param ContentElementFaq $contentElementFaq
     * @param string            $group
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findForFaq(ContentElementFaq $contentElementFaq, $group = '')
    {
        // массив для поиска по разделам
        $treeIds = [];
        // если встретился этот элемент, останавливаем поиск
        $stopTree = \common\lists\TreeList::getTreeByCode('tovary-dlya-dachi');

        $tree = $contentElementFaq->element->cmsTree;
        do {
            $treeIds[] = $tree->id;
            if ($tree->id == $stopTree->id) {
                break;
            }
            $tree = $tree->parent;
        } while($tree instanceof Tree);

        return self::find()
            ->filterWhere(['group' => $group])
            ->andWhere(['or',
                ['type' => self::TYPE_COMMON],
                ['and',
                    ['type' => self::TYPE_TREE],
                    ['tree_id' => array_unique($treeIds)]
                ]
            ]);
    }

}