<?php

namespace common\behaviors;

use common\helpers\Strings;
use common\models\Seo;
use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class SeoBehavior
 * @package frontend\behaviors
 *
 * @property $model;
 * @property $modelName;
 * @property ActiveRecord $owner;
 * @property  $seo;
 */
class SeoBehavior extends Behavior
{
    const DEFAULT_META_INDEX = 'INDEX, FOLLOW';

    public $h1Attribute = 'name';
    public $titleAttribute = 'name';
    public $slugAttribute = 'slug';
    public $descriptionAttribute = 'description';
    public $model;
    public $force = false;
    public $forceAttribute = 'force';

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'updateFields',
            ActiveRecord::EVENT_AFTER_UPDATE => 'updateFields',
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteFields',
        ];
    }

    public function setModel($model)
    {
        if (empty($model->meta_index)) {
            $model->meta_index = self::DEFAULT_META_INDEX;
        }
        $this->model = $model;
    }

    public function getModel()
    {
        $model = Seo::find()
            ->where([
                'owner_id' => $this->owner->id,
                'owner' => $this->modelName
            ])
            ->one();

        if ($model === null) {
            $model = new Seo();
            $model->owner = $this->modelName;
            $model->owner_id = $this->owner->id;
        }
        $this->setModel($model);

        return $this->model;
    }

    //cory empty attributes from owner;
    public function mergeFieldsWithOwner(Seo &$model)
    {
        $model->title = Strings::ucFirst($model->title && !$this->force
            ? $model->title : $this->getValue($model, 'titleAttribute'));
        $model->h1 = Strings::ucFirst($model->h1 && !$this->force
            ? $model->h1 : $this->getValue($model, 'h1Attribute'));
        $model->slug = $model->slug && !$this->force ? $model->slug : $this->getValue($model, 'slugAttribute');
        $model->meta_description = Strings::ucFirst($model->meta_description && !$this->force ? $model->meta_description : $this->getValue($model,
            'descriptionAttribute'));
    }

    public function updateFields($event)
    {
        $model = $this->getModel();
        $this->force = $this->getValue($model, 'forceAttribute');//$this->forceUpdateSeoFields;

        if (is_a(Yii::$app, yii\web\Application::class)) {
            $post = Yii::$app->request->post();
            $post['Seo']['owner_id'] = $this->owner->id;
            $model->load($post);
        }

        $this->mergeFieldsWithOwner($model);

        if (!$model->save()) {
            Yii::warning(json_encode($model->firstErrors), __METHOD__);
        }
    }

    public function deleteFields($event)
    {
        if ($this->owner->seo) {
            $this->owner->seo->delete();
        }
        return true;
    }

    public function getSeo()
    {
        return $this->getModel();
    }

    public function getModelName()
    {
        return get_class($this->owner);
    }

    /**
     * @param $model
     * @param $attribute
     * @return mixed
     */
    public function getValue($model, $attribute)
    {
        if ($this->force || (empty($model->{$attribute}) && !empty($this->{$attribute}))) {
            return $this->getOwnerValue($attribute);
        }

        return null;
    }

    /**
     * @param $attribute
     * @return mixed
     */
    protected function getOwnerValue($attribute)
    {
        if ($this->{$attribute} instanceof \Closure) {
            return call_user_func($this->{$attribute});
        } else {
            return $this->owner->{$this->{$attribute}};
        }
    }
}