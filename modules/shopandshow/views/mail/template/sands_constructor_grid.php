<?php
/** @var $this \yii\web\View */
/** @var array $data */
/** @var \modules\shopandshow\components\mail\BaseTemplate $template */

define('ABS_URL', $template->absUrl);
define('ABS_IMG_PATH', $template->absImgPath);
?>
<?= $this->render('_header', ['data' => $data, 'template' => $template]); ?>

<?php foreach ($data['GRID'] as $grid): ?>

    <?= $grid->getWidget(['template' => $template]); ?>

<?php endforeach; ?>

<?= $this->render('_footer', ['data' => $data, 'template' => $template]); ?>
