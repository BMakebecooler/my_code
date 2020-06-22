<?php
/** @var \yii\web\View $this*/
/** @var array $data */
/** @var \modules\shopandshow\components\mail\BaseTemplate $template */
?>
<hr style="border-top-color: #e1e1e1; border-top-style: solid;  border-top-width: 1px; border-bottom-color: none; border-left-color: none; border-right-color: none; border-bottom-width:0; border-left-width:0; border-right-width:0; margin-top:0; margin-right:0; margin-bottom:0; margin-left:0;"/>


<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-top: 25px">
            <?php
            $allTrees = array_chunk(\modules\shopandshow\components\mail\BaseTemplate::getStaticTreeMenuList(), 5);

            $salesTree = \common\models\Tree::findOne(['code' => 'sales']);
            if ($salesTree) {
                $allTrees[1][] = $salesTree;
            }

            $additionalTree = \common\models\Tree::findOne(['code' => 'poslednii_razmer']);
            if ($additionalTree) {
                $additionalTree->name = 'Последний<br>размер';
            }

            ?>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding-bottom: 4px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <?php foreach ($allTrees[0] as $tree): ?>
                                    <td valign="top">
                                        <a href="<?= $tree->absoluteUrl; ?>" target="_blank"
                                           style="display: inline-block; width: 132px; height: 52px; background-color: #ffffff; border: 2px solid #cacaca; line-height: 52px; font-size: 18px; color: #3b3937; text-align: center; text-decoration: none;">
                                            <span style="font-family: Arial, Helvetica, sans-serif; color: #3b3937"><?= $tree->name; ?></span>
                                        </a>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <?php foreach ($allTrees[1] as $tree): ?>
                                    <td valign="top">
                                        <a href="<?= $tree->absoluteUrl; ?>" target="_blank"
                                           style="display: inline-block; width: 132px; height: 52px; background-color: #ffffff; border: 2px solid #cacaca; line-height: 52px; font-size: 18px; color: #3b3937; text-align: center; text-decoration: none;">
                                            <span style="font-family: Arial, Helvetica, sans-serif; color: #3b3937"><?= $tree->name; ?></span>
                                        </a>
                                    </td>
                                <?php endforeach; ?>

                                <?php if ($additionalTree): ?>
                                <td valign="top">
                                    <a href="<?= $additionalTree->absoluteUrl; ?>"
                                       target="_blank"
                                       style="display: inline-block; width: 132px; height: 52px; border: 2px solid #cacaca; line-height: 26px; font-size: 16px; color: #ffffff; text-align: center; text-decoration: none;">
                                        <span style="font-family: Arial, Helvetica, sans-serif; color: #3b3937"><?= $additionalTree->name; ?></span>
                                    </a>
                                </td>
                                <? endif; ?>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td valign="top" align="center" style="padding: 36px 0 30px;">
            <p style="color: #4c4c4c; font-size: 14px; line-height: 18px; margin: 0; font-family: Arial, Helvetica, sans-serif;">
                Телеканал Shop&Show<br>Интересно смотреть, удобно выбирать, выгодно заказывать.
            </p>
        </td>
    </tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td valign="top" align="center">
            <a href="<?= ABS_URL; ?>" target="_blank" style="display: block;">
                <img src="<?= ABS_IMG_PATH; ?>/sh.png" alt="Shop&Show" style="display: block;" width="70" height="70" border="0">
            </a>
        </td>

        <td valign="top" align="center">
            <a rel="nofollow" href="https://vk.com/shopandshow" target="_blank" style="display: block;">
                <img src="<?= ABS_IMG_PATH; ?>/vk.png" alt="VKontakte" style="display: block;" width="70" height="70" border="0">
            </a>
        </td>

        <td valign="top" align="center">
            <a rel="nofollow" href="https://www.facebook.com/Shop-Show-192744827750938/" target="_blank"
               style="display: block;">
                <img src="<?= ABS_IMG_PATH; ?>/fb.png" alt="Facebook" style="display: block;" width="70" height="70" border="0">
            </a>
        </td>

        <td valign="top" align="center">
            <a rel="nofollow" href="https://www.instagram.com/shopandshow.ru/" target="_blank"
               style="display: block;">
                <img src="<?= ABS_IMG_PATH; ?>/ig.png" alt="Instagram" style="display: block;" width="70" height="70" border="0">
            </a>
        </td>

        <td valign="top" align="center">
            <a rel="nofollow" href="http://ok.ru/shopandshow" target="_blank" style="display: block;">
                <img src="<?= ABS_IMG_PATH; ?>/ok.png" alt="Odnoklassniki"
                     style="display: block;" width="70" height="70" border="0">
            </a>
        </td>

        <td valign="top" align="center">
            <a rel="nofollow" href="http://www.youtube.com/channel/UC3ZSro00SmKj2DzrY0OPwbQ" target="_blank"
               style="display: block;">
                <img src="<?= ABS_IMG_PATH; ?>/yt.png" alt="Youtube" style="display: block;" width="70" height="70" border="0">
            </a>
        </td>
    </tr>

    <tr>
        <td height="30"></td>
    </tr>
</table>
</td>
</tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #F2F2F2;">
    <tr>
        <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="700">
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                               style="height: 130px;">
                            <tr>
                                <td style="padding-right: 20px;">
                                    <img src="<?= ABS_IMG_PATH; ?>/letter_footer_1.png" alt="" width="50" height="50" border="0">
                                </td>

                                <td>
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 20px; color: #4c4c4c;">
                                        Не отвечайте на это письмо!
                                        По всем вопросам Вы можете написать
                                        на <a href="mailto:clients@shopandshow.ru" style="font-family: Arial, Helvetica, sans-serif; color: #256aa3;">
                                            <span style="font-family: Arial, Helvetica, sans-serif; color: #256aa3;">clients@shopandshow.ru</span>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                               style="height: 130px;">
                            <tr>
                                <td style="padding-right: 20px; padding-left: 32px;">
                                    <img src="<?= ABS_IMG_PATH; ?>/letter_footer_2.png" alt="" width="50" height="50" border="0">
                                </td>

                                <td>
                                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 20px; color: #4c4c4c;">
                                        Вы получили это письмо,
                                        потому что подписаны на рассылку
                                        интернет-магазина <a
                                                href='<?= ABS_URL; ?>'
                                                style="font-family: Arial, Helvetica, sans-serif; color: #256aa3;">
                                            <span style="font-family: Arial, Helvetica, sans-serif; color: #256aa3;">shopandshow.ru</span>
                                        </a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table align="center" border="0" cellpadding="0" cellspacing="0" width="700">
    <tr>
        <td valign="top">
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td valign="top">
                        <p style="margin: 30px 0 34px; font-size: 11px; line-height: 16px; color: #4c4c4c; text-align: center; font-family: Arial, Helvetica, sans-serif;">
                            Данное письмо не является офертой. Все цены действительны на момент совершения
                            рассылки.<br>
		                        Общество с ограниченной ответственностью «МаркетТВ», ОГРН: 1137746389505<br>
                            Фактический/Юридический адрес: Российская Федерация, 109029, город Москва,<br>
                            Сибирский проезд, дом 2, строение 10. Телефон: <a href="tel:88003016010"
                                                                              style="color: #256aa3; text-decoration-style: dashed;">
                                <span style="font-family: Arial, Helvetica, sans-serif; color: #256aa3;">8 (800) 301-60-10</span>
                            </a>
                        </p>

                    </td>
                </tr>

                <?php if (isset($template) && $template->useLinksForGetresponse): ?>
                <tr>
                    <td valign="top">
                        <p style="margin: 30px 0 34px; font-size: 15px; line-height: 16px; color: #4c4c4c; text-align: center; font-family: Arial, Helvetica, sans-serif;">
                            Если письмо отображается некорректно, посмотрите <a href="[[view]]">веб-версию</a>
                        </p>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </td>
    </tr>
</table>

</td>
</tr>
</table>
</body>
</html>
