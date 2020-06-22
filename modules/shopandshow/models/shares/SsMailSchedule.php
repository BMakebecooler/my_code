<?php

namespace modules\shopandshow\models\shares;

/**
 * This is the model class for table "ss_share_schedule".
 */
class SsMailSchedule extends SsShareSchedule
{
    protected static $_type = self::TYPE_MAIL_TEMPLATE;

    /**
     * Выводит список доступных блоков
     * @return array
     */
    public static function getBlockList()
    {
        return [
            'widget_default' => [
                'class' => \modules\shopandshow\widgets\grid\DefaultWidget::className(),
                'title' => 'Баннер и товары',
            ],
            'widget_cts' => [
                'class' => \modules\shopandshow\widgets\grid\CtsWidget::className(),
                'title' => 'Цтс',
            ],
            'widget_lifestyle' => [
                'class' =>\modules\shopandshow\widgets\grid\LifestyleWidget::className(),
                'title' => 'LifeStyle - Советы дня',
            ],
            'widget_block1' => [
                'class' =>\modules\shopandshow\widgets\grid\Block1Widget::className(),
                'title' => 'Блок 1',
            ],
            'widget_block2' => [
                'class' =>\modules\shopandshow\widgets\grid\Block2Widget::className(),
                'title' => 'Блок 2',
            ],
            'widget_block3' => [
                'class' =>\modules\shopandshow\widgets\grid\Block3Widget::className(),
                'title' => 'Блок 3',
            ],
            'widget_block4' => [
                'class' =>\modules\shopandshow\widgets\grid\Block4Widget::className(),
                'title' => 'Блок 4',
            ],
            'widget_block5' => [
                'class' =>\modules\shopandshow\widgets\grid\Block5Widget::className(),
                'title' => 'Блок 5',
            ],
            'widget_block6' => [
                'class' =>\modules\shopandshow\widgets\grid\Block6Widget::className(),
                'title' => 'Блок 6',
            ],
            'widget_block7' => [
                'class' =>\modules\shopandshow\widgets\grid\Block7Widget::className(),
                'title' => 'Блок 7',
            ],
            'widget_block8' => [ // TODO: Вообще не нужна. Полностью повторяет 6ой блок. Отедльную вьюшку для нее делать не стал.
                'class' =>\modules\shopandshow\widgets\grid\Block6Widget::className(),
                'title' => 'Блок 8',
            ],
            'widget_block9' => [ // TODO: Эта кстати тоже повторяет, как оказалось, 7ой блок. Полностью такая же. Нужно избавиться либо от этого, либо от 7го.
                'class' =>\modules\shopandshow\widgets\grid\Block9Widget::className(),
                'title' => 'Блок 9',
            ],
            'widget_block10' => [
                'class' =>\modules\shopandshow\widgets\grid\Block10Widget::className(),
                'title' => 'Блок 10',
            ],
            'widget_block11' => [
                'class' =>\modules\shopandshow\widgets\grid\Block11Widget::className(),
                'title' => 'Блок 11',
            ],
            'widget_block12' => [
                'class' =>\modules\shopandshow\widgets\grid\Block12Widget::className(),
                'title' => 'Блок 12',
            ],
        ];
    }

    public function getWidget(array $config = [])
    {
        $blockList = self::getBlockList();

        if (!array_key_exists($this->block_type, $blockList)) {
            return 'Invalid block: '.$this->block_type;
        }
        /** @var \skeeks\cms\base\WidgetRenderable $class */
        $class = $blockList[$this->block_type]['class'];

        $config = \common\helpers\ArrayHelper::merge(['namespace' => $this->getNamespaceByClass($class)], $config);
        return $class::widget($config);
    }

    public function getNamespaceByClass($classname)
    {

        // из-за ограничения CmsComponentSettings.namespace в 50 символов, придумываем левый неймспейс
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        $classname = '\\shopandshow\\mail\\grid\\'.$classname;

        if ($this->isNewRecord) {
            return $classname;
        }
        return $classname.'_'.$this->id;
    }
}
