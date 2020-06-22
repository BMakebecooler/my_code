<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 17.03.17
 * Time: 16:46
 */

namespace common\models\user;

use common\lists\Bitrix;
use common\models\user\authorizations\SignupForm;
use skeeks\cms\models\forms\PasswordResetRequestFormEmailOrLogin;

class PasswordResetForm extends PasswordResetRequestFormEmailOrLogin
{

    protected $user;

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {

        $user = ($this->user) ?: User::findByUsernameOrEmail($this->identifier);

        if ($user) {
            if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
            }

            if ($user->save(false, [
                'password_reset_token'
            ])
            ) {
                if (!$user->email) {
                    return false;
                }

                try {

                    $resetLink = \skeeks\cms\helpers\UrlHelper::construct('site/reset-password', ['token' => $user->password_reset_token])->enableAbsolute();

                    \Yii::$app->mailer->htmlLayout = false;
                    \Yii::$app->mailer->textLayout = false;

                    $message = \Yii::$app->mailer->compose('@templates/mail/site/password-reset-token', [
                        'user' => $user,
                        'resetLink' => $resetLink
                    ])
                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
                        ->setTo($user->email)
                        ->setSubject(\Yii::t('skeeks/cms', 'The request to change the password for') . \Yii::$app->cms->appName);

                    return $message->send();

                } catch (\Exception $exception) {
                    \Yii::error('PasswordResetForm message ' . $exception->getMessage());
                }
            } else {
                \Yii::error('PasswordResetForm errors ' . print_r($user->getErrors(), true));
            }
        }

        return false;
    }

    /**
     * @param $attr
     * @return bool
     */
    public function validateEdentifier($attr)
    {
        $user = User::findByUsernameOrEmail($this->identifier);

        if (!$user) {

            if (false && $bitrixUser = Bitrix::getUserByLoginOrEmail($this->identifier)) {

                $registration = new SignupForm();
                $registration->setScenario(User::SCENARIO_RIGISTRATION_FROM_BITRIX);

                $registration->setAttributes([
                    'email' => $bitrixUser['EMAIL'],
                    'username' => $bitrixUser['LOGIN'],
                    'password' => 'password',
                    'name' => $bitrixUser['NAME'],
                    'surname' => $bitrixUser['LAST_NAME'],
                    'patronymic' => $bitrixUser['SECOND_NAME'],
                    'isSubscribe' => true,
                    'phone' => $bitrixUser['PERSONAL_PHONE'],
                    'bitrix_id' => $bitrixUser['ID'],
                ]);

                if ($user = $registration->signup()) {

                } else {
//                    var_dump($registration->getErrors());
//                    die();
                }

            } else {
                $this->addError($attr, \Yii::t('skeeks/cms', 'User not found'));
            }
        }

        $this->user = $user;
    }

}