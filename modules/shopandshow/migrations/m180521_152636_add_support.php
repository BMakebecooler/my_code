<?php

use common\models\cmsContent\CmsContentElement;
use common\models\Tree;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentType;
use yii\db\Migration;

class m180521_152636_add_support extends Migration
{
    const CONTENT_CODE_SUPPORT = 'support';
    const CONTENT_CODE_SUPPORT_THEME = 'support-theme';
    const CONTENT_CODE_SUPPORT_QUESTION = 'support-questions';
    const SERVICE_NAME = 'Раздел клиентского сервиса';

    protected $propertiesQuestions = [
        'section' => [
            'name' => 'Раздел',
            'property_type' => 'E',
            'list_type' => 'L',
            'is_required' => Cms::BOOL_Y,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement',
            'component_settings' => 'a:7:{s:4:"code";s:1:"E";s:4:"name";s:36:"Привязка к элементу";s:12:"fieldElement";s:6:"select";s:10:"content_id";s:3:"%_CONTENT_ID_%";s:2:"id";s:62:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement";s:8:"property";a:27:{s:2:"id";i:222;s:10:"created_by";i:1267;s:10:"updated_by";i:1267;s:10:"created_at";i:1526991888;s:10:"updated_at";i:1526991888;s:4:"name";s:12:"Раздел";s:4:"code";s:7:"section";s:10:"content_id";s:3:"199";s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"T";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:12:"multiple_cnt";N;s:16:"with_description";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:11:"is_required";s:1:"Y";s:7:"version";i:1;s:9:"component";s:62:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement";s:18:"component_settings";a:6:{s:4:"code";s:1:"T";s:4:"name";s:34:"Привязка к разделу";s:11:"is_multiple";s:1:"0";s:2:"id";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeTree";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"Y";s:4:"name";s:12:"Раздел";s:4:"code";s:0:"";s:9:"component";s:59:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeTree";s:4:"hint";s:0:"";s:10:"content_id";s:3:"199";}s:10:"activeForm";N;}s:4:"hint";s:0:"";s:15:"smart_filtrable";s:1:"N";s:9:"vendor_id";N;s:11:"filter_name";N;s:11:"widget_name";N;s:9:"item_name";N;}s:10:"activeForm";N;}',
        ],
        'like' => [
            'name' => 'Кол-во лайков',
            'property_type' => 'N',
            'list_type' => 'L',
            'is_required' => Cms::BOOL_N,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber',
            'component_settings' => 'a:6:{s:4:"code";s:1:"N";s:4:"name";s:10:"Число";s:13:"default_value";s:1:"0";s:2:"id";s:61:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"N";s:4:"name";s:67:"Кол-во "вы нашли ответ на свой вопрос"";s:4:"code";s:4:"like";s:9:"component";s:61:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber";s:4:"hint";s:0:"";s:10:"content_id";s:3:"%_CONTENT_ID_%";}s:10:"activeForm";N;}',
        ],
        'dislike' => [
            'name' => 'Кол-во дизлайков',
            'property_type' => 'N',
            'list_type' => 'L',
            'is_required' => Cms::BOOL_N,
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber',
            'component_settings' => 'a:6:{s:4:"code";s:1:"N";s:4:"name";s:10:"Число";s:13:"default_value";s:1:"0";s:2:"id";s:61:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber";s:8:"property";a:15:{s:6:"active";s:1:"Y";s:8:"priority";s:3:"500";s:13:"property_type";s:1:"S";s:9:"list_type";s:1:"L";s:8:"multiple";s:1:"N";s:10:"searchable";s:1:"N";s:9:"filtrable";s:1:"N";s:7:"version";i:1;s:15:"smart_filtrable";s:1:"N";s:11:"is_required";s:1:"N";s:4:"name";s:67:"Кол-во "вы нашли ответ на свой вопрос"";s:4:"code";s:4:"like";s:9:"component";s:61:"skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber";s:4:"hint";s:0:"";s:10:"content_id";s:3:"%_CONTENT_ID_%";}s:10:"activeForm";N;}',
        ],
    ];

    /**
     * @var null|CmsContent
     */
    private $contentTheme = null;

    /**
     * @var null|CmsContent
     */
    private $contentItem = null;

    private $table_name = 'ss_support_questions';
    private $table_name_dialog = 'ss_support_questions_dialog';

    public function init()
    {
        parent::init();

        $this->contentTheme = CmsContent::findOne(['code' => self::CONTENT_CODE_SUPPORT_THEME]);
        $this->contentItem = CmsContent::findOne(['code' => self::CONTENT_CODE_SUPPORT_QUESTION]);
    }

    public function safeUp()
    {

        $contentType = new CmsContentType();
        $contentType->name = 'Клиентский сервис';
        $contentType->code = self::CONTENT_CODE_SUPPORT;
        $contentType->save();

        $tree = new Tree();
        $tree->name = self::SERVICE_NAME;
        $tree->code = self::CONTENT_CODE_SUPPORT;
        $tree->dir = self::CONTENT_CODE_SUPPORT;
        $tree->tree_type_id = 2;
        $tree->view_file = '@template/modules/cms/tree/support';
        $tree->pid = 1;
        $tree->pids = '1';
        $tree->level = 1;
        $tree->active = Cms::BOOL_N;
        $tree->save();

        $this->contentTheme = $themesContent = new CmsContent([
            'name' => 'Разделы',
            'code' => self::CONTENT_CODE_SUPPORT_THEME,
            'active' => Cms::BOOL_Y,
            'priority' => 2000,
            'name_meny' => 'Разделы',
            'name_one' => 'Раздел',
            'content_type' => self::CONTENT_CODE_SUPPORT,
            'default_tree_id' => $tree->id,
            'root_tree_id' => $tree->id,
            'is_allow_change_tree' => Cms::BOOL_N,
            'viewFile' => '@template/modules/cms/tree/support',
            'access_check_element' => Cms::BOOL_N
        ]);

        if (!$themesContent->save()) {
            throw new Exception('Failed create new themesContent');
        }

        $this->contentItem = $questionContent = new CmsContent([
            'name' => 'Элементы',
            'code' => self::CONTENT_CODE_SUPPORT_QUESTION,
            'active' => Cms::BOOL_Y,
            'priority' => 2000,
            'name_meny' => 'Элементы',
            'name_one' => 'Элемент',
            'content_type' => self::CONTENT_CODE_SUPPORT,
            'is_allow_change_tree' => Cms::BOOL_N,
            'viewFile' => '@template/modules/cms/content-element/support',
            'access_check_element' => Cms::BOOL_N,

            'default_tree_id' => $tree->id,
            'root_tree_id' => $tree->id,
        ]);

        if (!$questionContent->save()) {
            throw new Exception('Failed create new questionContent');
        }

        foreach ($this->propertiesQuestions as $propertyCode => $property) {

            $cmsContentProperty = \common\models\cmsContent\CmsContentProperty::find()
                ->where(['content_id' => $this->contentItem->id])
                ->andWhere(['code' => $propertyCode])
                ->limit(1)
                ->one();

            if (!$cmsContentProperty) {

                $compSettings = str_replace('%_CONTENT_ID_%', $this->contentItem->id, $property['component_settings']);

                $cmsContentProperty = new \common\models\cmsContent\CmsContentProperty();
                $cmsContentProperty->code = $propertyCode;
                $cmsContentProperty->name = $property['name'];
                $cmsContentProperty->property_type = $property['property_type'];
                $cmsContentProperty->list_type = $property['list_type'];
                $cmsContentProperty->component = $property['component'];
                $cmsContentProperty->component_settings = $compSettings;
                $cmsContentProperty->is_required = $property['is_required'];
                $cmsContentProperty->content_id = $this->contentItem->id;

                $cmsContentProperty->save();
            }
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        if (!$this->db->getTableSchema($this->table_name, true)) {

            $this->createTable($this->table_name, [
                'id' => $this->primaryKey(),

                'created_by' => $this->integer(),
                'updated_by' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),

                'element_id' => $this->integer()->notNull(),

                'fuser_id' => $this->integer()->notNull(),
                'user_id' => $this->integer(),
                'is_view' => $this->smallInteger(1),
                'is_sms_notification' => $this->smallInteger(1),
                'status' => $this->smallInteger(1),
                'rating' => $this->smallInteger(1),

                'user_ip' => $this->string(15),
                'username' => $this->string(128),
                'email' => $this->string(128),
                'phone' => $this->string(128),

                'question' => $this->text(),
            ], $tableOptions);

            $this->createIndex('I_user_id', $this->table_name, 'user_id');
            $this->createIndex('I_element_id', $this->table_name, 'element_id');
            $this->createIndex('I_fuser_id', $this->table_name, 'fuser_id');

            $this->addForeignKey('FK_questions_element_id', $this->table_name, 'element_id', 'cms_content_element', 'id', 'CASCADE', 'CASCADE');
        }


        if (!$this->db->getTableSchema($this->table_name_dialog, true)) {

            $this->createTable($this->table_name_dialog, [
                'id' => $this->primaryKey(),

                'created_by' => $this->integer(),
                'updated_by' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),

                'question_id' => $this->integer()->notNull(),

                'fuser_id' => $this->integer()->notNull(),
                'user_id' => $this->integer(),

                'user_ip' => $this->string(15),

                'message' => $this->text(),
            ], $tableOptions);

            $this->createIndex('I_user_id', $this->table_name_dialog, 'user_id');
            $this->createIndex('I_question_id', $this->table_name_dialog, 'question_id');
            $this->createIndex('I_fuser_id', $this->table_name_dialog, 'fuser_id');

            $this->addForeignKey('FK_questions_dialog_question_id', $this->table_name_dialog, 'question_id', $this->table_name, 'id', 'CASCADE', 'CASCADE');
            $this->addForeignKey('FK_questions_dialog_user_id', $this->table_name_dialog, 'user_id', 'cms_user', 'id', 'CASCADE', 'CASCADE');
        }

        return true;
    }

    public function safeDown()
    {

        $this->dropTable($this->table_name);
        $this->dropTable($this->table_name_dialog);

        if ($this->contentTheme) {
            CmsContentElement::deleteAll(sprintf("content_id = %d", $this->contentTheme->id));
        }

        if ($this->contentItem) {
            CmsContentElement::deleteAll(sprintf("content_id = %d", $this->contentItem->id));
        }

        foreach ($this->propertiesQuestions as $propertyCode => $property) {
            $cmsContentProperty = \common\models\cmsContent\CmsContentProperty::find()
                ->where(['content_id' => $this->contentItem->id])
                ->andWhere(['code' => $propertyCode])
                ->limit(1)
                ->one();

            if ($cmsContentProperty) $cmsContentProperty->delete();
        }

        CmsContent::deleteAll(sprintf("code = '%s'", self::CONTENT_CODE_SUPPORT_THEME));
        CmsContent::deleteAll(sprintf("code = '%s'", self::CONTENT_CODE_SUPPORT_QUESTION));

        CmsContentType::deleteAll(sprintf("code = '%s'", self::CONTENT_CODE_SUPPORT));

        Tree::deleteAll(sprintf("dir = '%s'", self::CONTENT_CODE_SUPPORT));

        return true;
    }
}
