<?php

/** @var array $data */
/** @var \modules\shopandshow\components\mail\BaseTemplate $template */

define('ABS_URL', $template->absUrl);
define('ABS_IMG_PATH', $template->absImgPath);

?>

<?= $this->render("@modules/shopandshow/views/mail/template/_header.php", ['data' => $data, 'template' => $template]); ?>

    <hr style="border-top-color: #e1e1e1; border-top-style: solid;  border-top-width: 1px; border-bottom-color: none; border-left-color: none; border-right-color: none; border-bottom-width:0; border-left-width:0; border-right-width:0; margin-top:0; margin-right:0; margin-bottom:0; margin-left:0;"/>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td height="22"></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%; text-align: center;">
        <tr>
            <td>
                <a href="<?= $template->getResponseLink($data['IMAGE']['URL']); ?>" target="_blank"
                   style="display: block; text-decoration: none; color: #fff; font-family: Arial, Helvetica, sans-serif;">
                    <img src="<?= $template->makeAbsUrl($data['IMAGE']['IMG']); ?>" alt=""
                         style="display: block; border: none;" width="700">
                </a>
            </td>
        </tr>

        <tr>
            <td>
                <p style="font-size: 33px; color: #970330; margin-top: 1.25em; margin-bottom: .75em;">Благодарим вас за подписку!</p>

                <p style="line-height: 1.44; font-size: 24px; color:  #3b3937; margin-bottom: 1em;">
                    Ваш подарок —
                    <a href="https://shopandshow.ru/v2/common/docs/book_recipes.pdf" target="_blank" style="color: #1592a5; text-decoration: none;">
                        <span style="color: #1592a5; text-decoration: none;">«Книга рецептов Shop&Show».</span>
                    </a>
                    <br/>
                    Нажмите на ссылку, и книга откроется в браузере.
                </p>

                <p style="line-height: 1.6; font-size: 20px; color: #2c2c2c; margin-bottom: 1.2em;">Вы будете получать письма о модных
                    новинках, стильных украшениях, о лучших товарах для красоты и здоровья. Мы будем держать вас в курсе
                    всех выгодных акций на товары для дома и кухни.</p>

                <p style="margin-bottom: .15em;">
                    <a href="https://shopandshow.ru/" target="_blank" style="color: #ffffff; text-decoration: none;">
                        <span style="display: inline-block; border-radius: 3px; background-color:  #1592a5; width: 180px; height: 35px; line-height: 35px; color: #ffffff; text-decoration: none; font-size: 18px;">Начать покупки</span>
                    </a>
                </p>
            </td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
        <tr>
            <td height="26"></td>
        </tr>
    </table>

<?= $this->render("@modules/shopandshow/views/mail/template/_footer.php", ['data' => $data, 'template' => $template]); ?>