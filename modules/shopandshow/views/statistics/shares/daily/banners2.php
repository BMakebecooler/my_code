<?php
/* @var $this yii\web\View */
/* @var $model modules\shopandshow\models\Shares\SharesStat */

$viewFile = '_banners2_' . ((!empty($noGridView) && $noGridView === true) ? 'mail' : 'site');

echo $this->render($viewFile, ['model' => $model]);

?>