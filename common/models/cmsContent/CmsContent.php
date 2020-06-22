<?php

namespace common\models\cmsContent;

use common\helpers\ArrayHelper;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\cmsContent\CmsContentElementRelation;
use modules\shopandshow\models\common\GuidBehavior;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContent as SXCmsContent;
use skeeks\cms\models\CmsContentElementProperty;
use Yii;

class CmsContent extends SXCmsContent
{

    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            GuidBehavior::className() => GuidBehavior::className(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'default_tree_id', 'root_tree_id'], 'integer'],
            [['name', 'content_type'], 'required'],
            [['description'], 'string'],
            [['meta_title_template'], 'string'],
            [['meta_description_template'], 'string'],
            [['meta_keywords_template'], 'string'],
            [['name', 'viewFile'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['access_check_element'], 'string'],
//            [['code'], 'validateCode'],
            [
                ['code'],
                function ($attribute) {
                    if (!preg_match('/^[a-zA-Z]{1}[\w]{1,255}$/',
                        $this->$attribute)
                    ) //if(!preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $this->$attribute))
                    {
                        $this->addError($attribute, \Yii::t('skeeks/cms',
                            'Use only letters of the alphabet in lower or upper case and numbers, the first character of the letter (Example {code})',
                            ['code' => 'code']));
                    }
                },
            ],
            [['active', 'index_for_search', 'tree_chooser', 'list_mode', 'is_allow_change_tree'], 'string', 'max' => 1],
            [['content_type'], 'string', 'max' => 32],
            [['name_meny', 'name_one'], 'string', 'max' => 100],
            ['priority', 'default', 'value' => 500],
            ['active', 'default', 'value' => Cms::BOOL_Y],
            ['is_allow_change_tree', 'default', 'value' => Cms::BOOL_Y],
            ['access_check_element', 'default', 'value' => Cms::BOOL_N],
            ['name_meny', 'default', 'value' => Yii::t('skeeks/cms', 'Elements')],
            ['name_one', 'default', 'value' => Yii::t('skeeks/cms', 'Element')],


            ['visible', 'default', 'value' => Cms::BOOL_Y],
            ['parent_content_is_required', 'default', 'value' => Cms::BOOL_Y],
            ['parent_content_on_delete', 'default', 'value' => self::CASCADE],

            ['parent_content_id', 'integer'],

            ['guid_id', 'safe'],

            [
                'code',
                'default',
                'value' => function ($model, $attribute) {
                    return "sxauto" . md5(rand(1, 10) . time());
                },
            ],
        ];
    }

    /**
     * Добавить элемент контента
     *
     * @param array $data
     * @return bool
     */
    public function createContent(array $data)
    {
        if (isset($data['guid']) && $data['guid']) {
            $cmsContent = Guids::getEntityByGuid($data['guid']);
        }
        else {
            $cmsContent = self::find()->andWhere('code = :code', [':code' => $data['code']]);

            if (isset($data['content_type'])) {
                $cmsContent = $cmsContent->andWhere('content_type = :content_type', [':content_type' => $data['content_type']]);
            }

            $cmsContent = $cmsContent->limit(1)->one();
        }

        if (!$cmsContent) {
            $cmsContent = $this;
        }

        $cmsContent->name = $data['name'];
        $cmsContent->description = $data['description'];
        $cmsContent->content_type = $data['content_type'];

        if (isset($data['guid'])) {
            $cmsContent->guid->setGuid($data['guid']);
        }

        // Код устанавливаем 1 раз, если его еще нет
        // для одинаковых имен генерим постфикс
        $i = 0;
        do {
            if ($cmsContent->isNewRecord || !$cmsContent->code) {
                $cmsContent->code = $data['code'];
                if ($i > 0) {
                    $cmsContent->code = $data['code'] . '_' . $i;
                }
            }

            $result = $cmsContent->save();
        } while ($result == false && $i++ < 5);

        if($result == false) {
            Job::dump($cmsContent->getErrors());
            Job::dump($data);

            return false;
        }

        if (isset($data['properties']) && !empty($data['properties'])) {
            foreach ($data['properties'] as $property) {

                $contentId = isset($property['content_id']) ? $property['content_id'] : $cmsContent->id;
                $code = $property['code'] ?? $cmsContent->code;

                $newProperty = CmsContentProperty::find()
                    ->andWhere('code = :code', [':code' => $code])
                    ->andWhere('content_id = :content_id', [':content_id' => $contentId])
                    ->one();

                if (!$newProperty) {
                    $newProperty = $cmsContent->createProperty($contentId);
                }

                $newProperty->code = $code;
                $newProperty->name = $property['name'];
                $newProperty->property_type = $property['property_type'];
                $newProperty->list_type = $property['list_type'];
                $newProperty->is_required = $property['is_required'];
                $newProperty->multiple = $property['is_multiple'] ?? Cms::BOOL_N;
                $newProperty->component = $property['component'];
                $newProperty->component_settings = isset($property['component_settings']) ? $property['component_settings'] : '';

                if (!$newProperty->save()) {
                    Job::dump($newProperty->getErrors());
                    //Job::dump($element);
                    Job::dump($data);

                    return false;
                }

            }
        }

        if (isset($data['elements']) && !empty($data['elements'])) {
            foreach ($data['elements'] as $element) {

                $guid = isset($element['guid']) ? $element['guid'] : false;

                $newElement = $cmsContent->createElementIfNotExistGuid($guid);

                $newElement->name = $element['name'];
                if ($newElement->isNewRecord) {
                    $newElement->code = isset($element['code']) ? $element['code'] : uniqid();
                }

                $newElement->description_full = isset($element['description']) ? $element['description'] : '';
                $newElement->priority = isset($element['priority']) ? $element['priority'] : '';
                $newElement->active = (isset($element['active']) && $element['active']) ? Cms::BOOL_Y : Cms::BOOL_N;

                if ($guid && $newElement->isNewRecord) {
                    $newElement->guid->setGuid($guid);
                    $newElement->noGuidAutoGenerateAttribute = false;
                }

                if (isset($element['parent_guid'])) {
                    if ($parentContentElement = Guids::getEntityByGuid($element['parent_guid'])) {
                        $newElement->parent_content_element_id = $parentContentElement->id;
                    } else {
                        Job::dump('parent not found for guid: ' . $element['parent_guid']);
                        return false;
                    }
                }

                if (!$newElement->save()) {
                    Job::dump($newElement->getErrors());
                    //Job::dump($element);
                    Job::dump($data);

                    return false;
                }

                if (isset($element['relatedPropertiesModel']) && is_array($element['relatedPropertiesModel'])) {
                    $relatedPropertiesEnum = [];
                    $relatedPropertiesChanged = false;
                    foreach ($element['relatedPropertiesModel'] as $propertyName => $propertyValue) {
                        if (is_array($propertyValue)) {
                            $relatedPropertiesEnum[$propertyName] = $propertyValue;
                        }
                        else {
                            $newElement->relatedPropertiesModel->setAttribute($propertyName, $propertyValue);
                            $relatedPropertiesChanged = true;
                        }
                    }

                    if ($relatedPropertiesChanged) {
                        if (!$newElement->relatedPropertiesModel->save()) {
                            Job::dump($newElement->relatedPropertiesModel->getErrors());

                            return false;
                        }
                    }

                    if ($relatedPropertiesEnum) {
                        foreach ($relatedPropertiesEnum as $propertyName => $propertyValues) {
                            $property = CmsContentProperty::find()
                                ->andWhere('code = :code', [':code' => $propertyName])
                                ->andWhere('content_id = :content_id', [':content_id' => $newElement->content_id])
                                ->one();

                            CmsContentElementProperty::deleteAll('element_id = '.$newElement->id);

                            foreach ($propertyValues as $propertyValue) {
                                $cmsContentElementProperty = new CmsContentElementProperty();
                                $cmsContentElementProperty->property_id = $property->id;
                                $cmsContentElementProperty->element_id = $newElement->id;
                                $cmsContentElementProperty->value = (string)$propertyValue;
                                $cmsContentElementProperty->value_num = (float)$propertyValue;
                                $cmsContentElementProperty->value_enum = (float)$propertyValue;

                                if (!$cmsContentElementProperty->save()) {
                                    Job::dump($cmsContentElementProperty->getErrors());
                                    return false;
                                }
                            }
                        }
                    }
                }

                /**
                 * связь вида [id , related_id] (1 ко многим)
                 */
                if (isset($element['relations']) && !empty($element['relations'])) {
                    // clear old
                    if (!$newElement->isNewRecord && $newElement->elementRelations) {
                        foreach ($newElement->elementRelations as $relatedElement) {
                            if (!$relatedElement->delete()) {
                                Job::dump($relatedElement->getErrors());
                                return false;
                            }
                        }
                    }

                    foreach ($element['relations'] as $relatedGuid) {
                        if (!$relatedElement = Guids::getEntityByGuid($relatedGuid)) {
                            Job::dump(' cant find related element '.$relatedGuid);
                            return false;
                        }

                        $cmsContentElementRelation = new CmsContentElementRelation([
                           'content_element_id' => $newElement->id,
                           'related_content_element_id' => $relatedElement->id
                        ]);

                        if (!$cmsContentElementRelation->save()) {
                            Job::dump(' failed to save relation '.$newElement->id.' -> '.$relatedElement->id);
                            Job::dump($cmsContentElementRelation->getErrors());
                            Job::dump($element['relations']);
                            return false;
                        }
                    }
                }

                if (isset($element['imagePath']) && !empty($element['imagePath'])) {
                    $vendorFilePath = \Yii::$app->params['storage']['kfssImagesPath'] . '/' . preg_replace('#\\\+#', '/', $element['imagePath']);

                    $vendorFile = new \skeeks\sx\File($vendorFilePath);

                    if ($vendorFile->isExist() === false) {
                        Job::dump('нет пикчи ' . $vendorFilePath);
                        return false;
                    } else {

                        /** Копируем фаил чтобы не удалять у вендора (в нашем случае из папки оригиналов) */
                        $tmpFile = new \skeeks\sx\File('/tmp/' . md5(time() . $vendorFilePath) . "." . $vendorFile->getExtension());

                        $vendorFile->copy($tmpFile);

                        if (($oldImage = $newElement->image)) {
                            /**
                             * Если новая фотка отличается от ранее загруженной то загружаем ее
                             */

                            if ($oldImage->original_name != $newElement->guid->getGuid()
                                || $oldImage->size != $tmpFile->size()->getBytes()
                            ) {

                                $newElement->image->cluster->update($newElement->image->cluster_file, $tmpFile);
                                $oldImage->size = $tmpFile->size()->getBytes();
                                $oldImage->name = $newElement->name;
                                $oldImage->original_name = $newElement->guid->getGuid();
                                $oldImage->save(false);
                            }

                        } else {
                            $file = \Yii::$app->storage->upload($tmpFile, [
                                'name' => $newElement->name,
                                'original_name' => $newElement->guid->getGuid()
                            ],
                                \Yii::$app->params['storage']['clusters'][$element['imageClusterId']] // TODO: подумать как такой херни не плодить.
                            );

                            $newElement->link('image', $file);
                        }
                    }
                }
                elseif($newElement->image) {
                    $newElement->image->delete();
                }
            }
        }

        return true;
    }

    /**
     * @param int $contentId
     *
     * @return CmsContentProperty
     */
    public function createProperty($contentId = null)
    {
        return new CmsContentProperty([
            'content_id' => $contentId ?: $this->id,
        ]);
    }

    /**
     * @return CmsContentElement
     */
    public function createElement()
    {
        return new CmsContentElement([
            'content_id' => $this->id,
        ]);
    }

    /**
     * @param $guid
     *
     * @return CmsContent|CmsContentElement|\common\models\user\User|false|\modules\shopandshow\models\shop\ShopOrder|\yii\db\ActiveRecord
     */
    public function createElementIfNotExistGuid($guid = false)
    {
        if ($guid && ($contentElement = Guids::getEntityByGuid($guid))) {
            return $contentElement;
        }

        return new CmsContentElement([
            'content_id' => $this->id,
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElementsActive()
    {
        return $this->hasMany(CmsContentElement::className(), ['content_id' => 'id'])
            ->andOnCondition('active = :active', [':active' => Cms::BOOL_Y]);
    }
}