<?php
/**
 * Created by PhpStorm.
 * User: koval
 * Date: 09.07.18
 * Time: 18:25
 */
?>
<?php if ($widget->header || $widget->subHeader): ?>
    <tr>
        <td valign="top" align="center" style="padding: 25px 0 14px;">
            <?php if ($widget->header): ?>
                <p style="margin: 0; color: #1592a5; font-size: 30px; line-height: 1; text-transform: uppercase;">
                    <?= $widget->header; ?>
                </p>
            <?php endif; ?>

            <?php if ($widget->subHeader): ?>
                <p style="margin: 8px 0 0; font-size: 18px; line-height: 22px;">
                    <?= $widget->subHeader; ?>
                </p>
            <?php endif; ?>
        </td>
    </tr>
<? endif; ?>
