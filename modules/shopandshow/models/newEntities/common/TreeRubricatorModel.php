<?php

namespace modules\shopandshow\models\newEntities\common;

use common\models\Tree;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use yii\base\Model;

class TreeRubricatorModel extends Model
{

    const SITE_CODE = 's1';
    const GUID_GROUP_SITE = '98DE73C1592234BFE0538201090AAF9D';

    public $guid;
    public $name;
    public $code;
    public $description;
    public $active;
    public $parentGuid;

    /**
     * @var Tree
     */
    protected $tree;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['weight'], 'integer'],
            [['guid'], 'required'],
            [array_keys(get_object_vars($this)), 'safe'],
        ];
    }

    public function setTree($tree)
    {
        $this->tree = $tree;
    }

    public function addData()
    {
        /**
         * @var Tree $tree
         * @var Tree $parentTree
         */

//        $catalog = \common\lists\TreeList::getTreeByCode('catalog'); //TODO РАСКОМЕНТИТЬ КОГДА РУБРИКАТОР ВСТАНЕТ НА ТЕСТО КАТАЛОГА!
        $catalog = \common\lists\TreeList::getTreeByCode('catalognew'); //TODO ЗАКОМЕНТИТЬ КОГДА РУБРИКАТОР ВСТАНЕТ НА ТЕСТО КАТАЛОГА!

        if (!$this->tree && $this->parentGuid && ($parentTree = Guids::getEntityByGuid($this->parentGuid))) {
            $tree = Tree::findOne(['code' => \common\helpers\Strings::translit($this->name), 'pid' => $parentTree->id]);
            if ($tree) {
                $tree->guid->setGuid($this->guid);
                $this->setTree($tree);
            }
        }
        elseif (!$this->tree && !$this->parentGuid) {
            $tree = Tree::findOne(['code' => \common\helpers\Strings::translit($this->name), 'pid' => $catalog->id]);
            if ($tree) {
                $tree->guid->setGuid($this->guid);
                $this->setTree($tree);
            }
        }

        $tree = ($this->tree) ?: new Tree();

        $tree->name = $this->name;
        $tree->description_short = $this->description;
        $tree->active = $this->active;

        if ($tree->isNewRecord) {
            $tree->code = \common\helpers\Strings::translit($tree->name);
            $tree->guid->setGuid($this->guid);
            $tree->site_code = self::SITE_CODE;
            $tree->tree_type_id = RUBRICATOR_TREE_TYPE_ID;
            $tree->setAttributesForFutureParent($catalog);
        }


        if ($this->parentGuid) {
            /** @var Tree $parentTree */
            if ($parentTree = Guids::getEntityByGuid($this->parentGuid)) {
                $tree->setAttributesForFutureParent($parentTree);

            } else {
                Job::dump('failed to find parent by guid '.$this->parentGuid);
                // складываем в ошибочные, потом дозагрузим из лога
                return false;
            }
        }

        $res = $tree->save();
        if (!$res) {
            Job::dump($tree->getErrors());
            Job::dump($tree->getAttributes());
        }
        return $res;
    }
}