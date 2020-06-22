<?php

namespace modules\shopandshow\components\mail\template;

use modules\shopandshow\components\mail\BaseTemplate;
use modules\shopandshow\models\shares\SsMailSchedule;

class SandsConstructorGrid extends BaseTemplate
{
    public $viewFile = '@modules/shopandshow/views/mail/template/sands_constructor_grid';

    public function init()
    {
        parent::init();

        $this->data['GRID'] = [];
        $schedules = SsMailSchedule::findByDate($this->begin_date)->all();
        foreach ($schedules as $schedule) {
            $this->data['GRID'][] = $schedule;
        }
    }

    public function getAbsImgPath()
    {
        return $this->absUrl.\Yii::getAlias('@web_common').'/img/sands_grid';
    }

    public function getTreeMenuList()
    {
        $codes = ['moda', 'obuv', 'ukrasheniya', 'dom', 'kukhnya', 'gadzhity', 'krasota-i-zdorove', 'khobbi'];
        return \common\models\Tree::find()
            ->andWhere(['level' => 2])
            ->andWhere(['active' => 'Y'])
            ->andWhere(['tree_type_id' => CATALOG_TREE_TYPE_ID])
            ->andWhere(['pid' => $this->tree_pid])
            ->orderBy(new \yii\db\Expression("FIELD(code, '".join("','", $codes)."')"))
            ->andWhere(['code' => $codes])
            ->all();
    }
}