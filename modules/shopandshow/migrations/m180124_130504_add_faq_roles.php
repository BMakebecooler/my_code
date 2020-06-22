<?php

use common\models\cmsContent\ContentElementFaq;
use yii\db\Migration;

/**
 * Class m180124_130504_add_faq_roles
 */
class m180124_130504_add_faq_roles extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $authManager = \Yii::$app->authManager;

        // Новые роли
        $buyer = $authManager->getRole('buyer');
        if (!$buyer) {
            $buyer = $authManager->createRole('buyer');
            $buyer->description = 'Баер (администрирование)';
            $authManager->add($buyer);
        }
        $service = $authManager->getRole('service');
        if (!$service) {
            $service = $authManager->createRole('service');
            $service->description = 'Сервис для работы с пользователями (администрирование)';
            $authManager->add($service);
        }
        $copyright = $authManager->getRole('copyright');
        if (!$copyright) {
            $copyright = $authManager->createRole('copyright');
            $copyright->description = 'Копирайтер (администрирование)';
            $authManager->add($copyright);
        }

        // наследуем новые роли для рута
        $root = $authManager->getRole('root');
        if (!$authManager->hasChild($root, $buyer)) {
            $authManager->addChild($root, $buyer);
        }
        if (!$authManager->hasChild($root, $service)) {
            $authManager->addChild($root, $service);
        }
        if (!$authManager->hasChild($root, $copyright)) {
            $authManager->addChild($root, $copyright);
        }

        // доступ в админку
        $cmsAdminAccess = $authManager->getPermission('cms.admin-access');
        if (!$authManager->hasChild($buyer, $cmsAdminAccess)) {
            $authManager->addChild($buyer, $cmsAdminAccess);
        }
        if (!$authManager->hasChild($service, $cmsAdminAccess)) {
            $authManager->addChild($service, $cmsAdminAccess);
        }
        if (!$authManager->hasChild($copyright, $cmsAdminAccess)) {
            $authManager->addChild($copyright, $cmsAdminAccess);
        }

        // доступ к пункту меню
        $cmsAdminMenuQuestionsAccess = $authManager->getPermission('shopandshow/questions/questions');
        if (!$cmsAdminMenuQuestionsAccess) {
            $cmsAdminMenuQuestionsAccess = $authManager->createPermission('shopandshow/questions/questions');
        }
        if (!$authManager->hasChild($buyer, $cmsAdminMenuQuestionsAccess)) {
            $authManager->addChild($buyer, $cmsAdminMenuQuestionsAccess);
        }
        if (!$authManager->hasChild($service, $cmsAdminMenuQuestionsAccess)) {
            $authManager->addChild($service, $cmsAdminMenuQuestionsAccess);
        }
        if (!$authManager->hasChild($copyright, $cmsAdminMenuQuestionsAccess)) {
            $authManager->addChild($copyright, $cmsAdminMenuQuestionsAccess);
        }

        // новые разрешения
        $faqBuyerPerm = $authManager->createPermission(ContentElementFaq::PERM_BUYER);
        $faqBuyerPerm->description = 'FAQ | Редактирование поля комментария для баеров';
        $faqServicePerm = $authManager->createPermission(ContentElementFaq::PERM_SERVICE);
        $faqServicePerm->description = 'FAQ | Редактирование поля комментария для сервиса';
        $faqCopyrightPerm = $authManager->createPermission(ContentElementFaq::PERM_COPYRIGHT);
        $faqCopyrightPerm->description = 'FAQ | Редактирование поля ответа копирайтера';

        $authManager->add($faqBuyerPerm);
        $authManager->addChild($buyer, $faqBuyerPerm);

        $authManager->add($faqServicePerm);
        $authManager->addChild($service, $faqServicePerm);

        $authManager->add($faqCopyrightPerm);
        $authManager->addChild($copyright, $faqCopyrightPerm);

        // общее редактирование faq
        $faqEditPerm = $authManager->createPermission(ContentElementFaq::PERM_EDIT);
        $faqEditPerm->description = 'FAQ | Администрирование';

        $authManager->add($faqEditPerm);
        $authManager->addChild($faqEditPerm, $faqBuyerPerm);
        $authManager->addChild($faqEditPerm, $faqServicePerm);
        $authManager->addChild($faqEditPerm, $faqCopyrightPerm);

        // даем админу права на faq
        $admin = $authManager->getRole('admin');
        $authManager->addChild($admin, $faqEditPerm);
        $editor = $authManager->getRole('editor');
        $authManager->addChild($editor, $faqEditPerm);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $authManager = \Yii::$app->authManager;

        // удаляем разрешения
        $faqEditPerm = $authManager->getPermission(ContentElementFaq::PERM_EDIT);
        $faqBuyerPerm = $authManager->getPermission(ContentElementFaq::PERM_BUYER);
        $faqServicePerm = $authManager->getPermission(ContentElementFaq::PERM_SERVICE);
        $faqCopyrightPerm = $authManager->getPermission(ContentElementFaq::PERM_COPYRIGHT);

        $authManager->remove($faqEditPerm);
        $authManager->remove($faqBuyerPerm);
        $authManager->remove($faqServicePerm);
        $authManager->remove($faqCopyrightPerm);

        // удаляем роли
        $buyer = $authManager->getRole('buyer');
        $service = $authManager->getRole('service');
        $copyright = $authManager->getRole('copyright');

        $authManager->remove($buyer);
        $authManager->remove($service);
        $authManager->remove($copyright);
    }
}
