<?php/** * php ./yii tools/agent/fix-running */namespace console\controllers\tools;use console\controllers\export\ExportController;use skeeks\cms\agent\models\CmsAgent;use skeeks\cms\components\Cms;/** * Class AgentController * @package console\controllers */class AgentController extends ExportController{    /**     * Чинит сломанные агенты, которые могли зависнуть в бд     */    public function actionFixRunning()    {        // находим запущенных агентов, время следующего запуска которых уже просрочен на полчаса        /**         * @var $agents CmsAgent[]         */        $agents = CmsAgent::find()            ->andWhere(['is_running' => Cms::BOOL_Y])            ->andWhere(['active' => Cms::BOOL_Y])            ->andWhere('next_exec_at < unix_timestamp(now()) - 1800')            ->all();        foreach ($agents as $agent) {            $agent->is_running = Cms::BOOL_N;            $agent->save();        }    }}