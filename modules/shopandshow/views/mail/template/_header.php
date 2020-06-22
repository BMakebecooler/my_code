<?php
/** @var \yii\web\View $this*/
/** @var array $data */
/** @var \modules\shopandshow\components\mail\BaseTemplate $template */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <title><?= $data['SUBJECT']; ?></title>
</head>
<body style="margin:0;padding:0;background:#f1f1f1; font-size: 16px;">
<table border="0" cellpadding="0" cellspacing="0" width="100%"
       style="background: #fff; font-family: Arial, sans-serif; color: #3b3937;">
    <tr>
        <td valign="top">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="700">
                <tr>
                    <td valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="height: 85px;">
                            <tr>
                                <td>
                                    <a href="<?= ABS_URL; ?>" target="_blank" style="display: block; text-decoration: none;">
                                        <img src="<?= ABS_IMG_PATH; ?>/logo.png" alt="LOGO" width="250" height="50" border="0" style="display: block;">
                                    </a>
                                </td>

                                <td style="text-align: center;">
                                    <a href="<?= ABS_URL; ?>/onair" target="_blank" style="padding: 12px; text-decoration: none;">
                                        <img src="<?= ABS_IMG_PATH; ?>/onair.png" alt="Сейчас в эфире" width="42" height="31" border="0" style="display: block;">
                                    </a>
                                </td>

                                <td align="right" style="color: #3b3937;">
                                    <p style="margin: 0; font-size: 34px; line-height: 1;">
                                        <span style="font-family: Arial, Helvetica, sans-serif; font-size: 20px;">8 (800)</span>
                                        <span style="font-family: Arial, Helvetica, sans-serif;line-height: 24px;">301-60-10</span>
                                    </p>

                                    <p style="font-family: Arial, Helvetica, sans-serif; margin: 0; font-size: 14px; line-height: 24px;">Бесплатно и
                                        круглосуточно</p>
                                </td>
                            </tr>
                        </table>
