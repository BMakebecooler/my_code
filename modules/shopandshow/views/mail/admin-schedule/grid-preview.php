<?php
use modules\shopandshow\models\shares\SsMailSchedule;
use yii\web\View;

/** @var View $this */
/** @var string $block */

if(\common\helpers\User::isDeveloper()) {
    \Yii::$app->cmsToolbar->initEnabled();
    \Yii::$app->cmsToolbar->enabled = true;
    \Yii::$app->cmsToolbar->editWidgets = \skeeks\cms\components\Cms::BOOL_Y;
    \Yii::$app->cmsToolbar->inited = true;

    echo '<h4 style="color: blue;">(Шаблон для примера, редактировать можно только в целях первичной настройки, чтобы посмотреть его тут в превью)</h4>';
}

$model = new SsMailSchedule(['block_type' => $block]);
echo $model->getWidget();
?>

<?php if(\common\helpers\User::isDeveloper()): ?>
    <script>
      var v_id = $('div#block_new .block-preview .skeeks-cms-toolbar-edit-view-block').attr('id');

      new sx.classes.toolbar.EditViewBlock({'id' : v_id});
    </script>
<?php endif; ?>