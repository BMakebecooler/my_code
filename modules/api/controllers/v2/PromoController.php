<?php


namespace modules\api\controllers\v2;

use common\components\cache\PageCache;
use common\helpers\CacheHelper;
use common\helpers\Common;
use common\helpers\Strings;
use common\helpers\User;
use common\models\CmsUserEmail;
use modules\api\controllers\ActiveController;
use \modules\api\resource\v2\Promo;
use modules\shopandshow\models\users\UserEmail;
use yii\data\ActiveDataProvider;
use yii\filters\Cors;
use Yii;
use yii\helpers\Html;


class PromoController extends ActiveController
{
    public $modelClass = \modules\api\resource\v2\Promo::class;

    // grabbed from yii\rest\OptionsAction with a little work around
    private $_verbs = ['GET', 'OPTIONS'];

    private $response = [
        'success' => false,
        'message' => '',
        'data' => []
    ];

    public function behaviors()
    {
        return [
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
                ],
            ],
            [
                'class' => PageCache::class,
                'duration' => CacheHelper::CACHE_TIME_PROMO_API,
                'variations' => CacheHelper::getPromoViaApiVariation(),
                'enabled' => CacheHelper::isEnabled(),
                'except' => ['item'],
            ],
        ];
    }


    public function actionOptions()
    {
        if (Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
            Yii::$app->getResponse()->setStatusCode(405);
        }
        $options = $this->_verbs;
        Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $options));
    }

    /**
     *
     * Method is duplicate, original method  in ProductController
     * @return array
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ]
        ];
    }

    public function prepareDataProvider()
    {
        $query = Promo::findPromosActionQuery();

        $perPage = Yii::$app->request->get('per_page');
        if (!$perPage) {
            $perPage = static::$promoPerPage;
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $perPage,
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ]
        ]);
    }

    public function actionItem()
    {
        $promoItemSlug = \Yii::$app->request->get('slug');

        if ($promoItemSlug) {
            $promoItemSlug = Html::encode($promoItemSlug);

            switch ($promoItemSlug) {
                case 'slovo':
                    $word = Html::encode(\Yii::$app->request->post('promo_word', '<НеУказано>'));
                    $userName = Html::encode(\Yii::$app->request->post('user_name', '<НеУказано>'));
                    $userSecondName = Html::encode(\Yii::$app->request->post('user_second_name', '<НеУказано>'));
                    $phoneClean = Strings::getPhoneClean(\Yii::$app->request->post('phone'), true);
                    $date = date("Ymd");
                    $source = UserEmail::SOURCE_SITE;
                    $sourceDetail = UserEmail::SOURCE_DETAIL_PROMO_WORD . '_' . $date;

                    if ($phoneClean) {

                        //Проверяем есть ли такой тел в базе
                        $userTry = CmsUserEmail::find()
                            ->andWhere([
                                'value' => $phoneClean,
                                'value_type' => UserEmail::VALUE_TYPE_PHONE,
                                'source' => $source,
                                'source_detail' => $sourceDetail,
                            ])
                            ->one();

                        if (!$userTry) {
                            $phoneFormated = Strings::formatPhone($phoneClean, "(%s) %s-%s-%s");

                            //* Отправляем письмо *//

                            $mailSended = false;

                            try {

                                \Yii::$app->mailer->htmlLayout = false;
                                \Yii::$app->mailer->textLayout = false;

                                if (1) {
                                    $emails = [
                                        'slovo@shopandshow.ru',
                                    ];
                                    $emailsCc = [
                                        'ryabov_yn@shopandshow.ru',
                                        'shevelov_iv@shopandshow.ru',
                                    ];
                                } else { //test
                                    $emails = [
                                        'ryabov_yn@shopandshow.ru',
                                    ];
                                    $emailsCc = [
                                        'ryabov_yn@shopandshow.ru',
                                    ];
                                }


                                $subject = "#ОткройСлово [{$date}] - {$phoneFormated}";
                                $msg = "{$word} - {$phoneFormated}\n{$userName} {$userSecondName}";

                                $message = \Yii::$app->mailer->compose()
                                    ->setFrom('no-reply@shopandshow.ru')
                                    ->setTo($emails)
                                    ->setCc($emailsCc)
                                    ->setSubject($subject)
                                    ->setTextBody($msg);

                                $mailSended = $message->send();

                            } catch (\Exception $exception) {
                                //echo $exception->getMessage();
                            }
                            //* /Отправляем письмо *//

                            //Добавляем попытку в базу
                            if ($mailSended) {
                                $this->response['success'] = true;
//                                $this->response['message'] = "У тебя появился шанс стать одним из 10 обладателей смартфона. Победителей объявим в субботу в эфире Shop&Show, не пропусти! А также угаданное слово открывает гарантированную скидку 10% при заказе до конца этих выходных – просто назови его оператору.";
                                $this->response['message'] = "У тебя появился шанс получить приз. Победителей объявим в конце недели в эфире Shop&Show, не пропусти! А также угаданное слово открывает гарантированную скидку 10% при заказе до конца этих выходных – просто назови его оператору.";

                                //Добавляем запись в базу
                                $cmsUserEmail = new CmsUserEmail();
                                $cmsUserEmail->setAttributes([
                                    'value' => $phoneClean,
                                    'source' => $source,
                                    'source_detail' => $sourceDetail,
                                    'value_type' => UserEmail::VALUE_TYPE_PHONE,
                                    'is_valid_site' => Common::BOOL_Y,
                                ]);

                                if (!$cmsUserEmail->save()) {
                                    //Письмо хоть и ушло, но в БД записать не удалось
                                    $cmsUserEmail->getErrors();
                                }
                            } else {
                                //Ошибка при отправке письма
                                $this->response['message'] = 'Ошибка при отправке. Попробуйте повторить попытку позже.';
                            }
                        } else {
                            //Уже была попытка
                            $this->response['message'] = "Ты открыл слово, повторная отправка невозможна.";
                        }

                    } else {
                        //неверный формат телефона
                        $this->response['message'] = 'Неверный формат телефона';
                    }
                    break;

                case 'million';
                    $this->getPromoItemMillion();
                    break; //million

                    break;
                default: //$promoItemSlug

            }
        }

        return $this->response;
    }

    private function getPromoItemMillion()
    {
        $action = \Yii::$app->request->get('action');
        $user = User::getUser();

        if ($user) {
            $kfssUserData = \Yii::$app->kfssLkApiV2->getUserDataByPhone($user->phone);
        }

        $this->response['success'] = true;

        if ($action) {
            //Если какое то действие, то нам обязательно нужен авторизованный пользователь
            if ($user) {
                //Если пользователь авторизован на сайте - то нужен соответствующий активный и хороший юзер из кфсс
                if (!empty($kfssUserData)) {

                    switch ($action) {
                        case 'init':
                            $initResponse = \Yii::$app->kfssLkApiV2->initMillionPromo($kfssUserData['id']);

                            if ($initResponse && isset($initResponse['code']) && $initResponse['code'] == 0) {
                                $this->response['success'] = true;
                                $this->response['message'] = 'Теперь вы принимаете участие в розыгрыше';
                            } else {
                                $this->response['success'] = false;

                                //TODO Временный ответ, проверить что может возвращать КФСС так как регистрация участия идет через функционал регистрации кода
                                $message = "Возникла ошибка. Повторите попытку позже.";
                                $this->response['message'] = $message;
                            }
                            break; //init
                        case 'regcode':

                            $code = \Yii::$app->request->post('code');

                            $verifyCaptcha = \Yii::$app->captcha->verifyCaptcha(
                                \Yii::$app->request->post('captchaToken'),
                                \Yii::$app->request->url
                            );
                            if (!$verifyCaptcha) {
                                \Yii::error(
                                    'Verify Captcha Error, User id: ' . $user->id . ', User phone: ' . $user->phone,
                                    __METHOD__
                                );
                            }

                            if ($code && $verifyCaptcha) {
                                $addCodeResponse = \Yii::$app->kfssLkApiV2->addMillionPromoCode($code, $kfssUserData['id']);

                                if ($addCodeResponse && isset($addCodeResponse['code'])) {
                                    if ($addCodeResponse['code'] == 0) {
                                        $this->response['success'] = true;
                                        $this->response['message'] = 'Код успешно добавлен';
                                    } else {
                                        $this->response['success'] = false;
                                        switch ($addCodeResponse['code']) {
                                            case 4:
                                                $this->response['message'] = 'Код не существует';
                                                break;
                                            case 5:
                                                $this->response['message'] = 'Код сейчас не активен (истек или не начался)';
                                                break;
                                            default:
                                                //Кроме 4 и 5 остальные про ошибки АПИ или клиента (не найден или заблочен), что не так интересно или отсеивается ранее
                                                $this->response['message'] = 'Возникла ошибка. Повторите попытку позже.';
                                        }
                                    }
                                } else {
                                    $this->response['success'] = false;

                                    $message = "Возникла ошибка. Повторите попытку позже.";
                                    $this->response['message'] = $message;
                                }
                            } else {
                                $this->response['success'] = false;
                                $this->response['message'] = 'Не указан код';
                            }

                            break; //regcode
                        default:
                            $this->response['success'] = false;
                            $this->response['message'] = 'Неизвестное действие';
                    }

                } else {
                    //Данных о пользователе из кфсс нет, то есть он или заблочен или не найден или ошибка
                    $this->response['success'] = false;
                    $this->response['message'] = 'Возникла ошибка, попробуйте повторить попытку позже';
                }

            } else {
                $this->response['success'] = false;
                $this->response['message'] = 'Для регистрации в розыгрыше вам необходимо авторизоваться/зарегистрироваться';
            }

        }

        $kfssUserId = !empty($kfssUserData['id']) ? $kfssUserData['id'] : 0;

        $this->response['data'] = \common\helpers\Promo::getPromoMillionUserData($kfssUserId);
    }
}