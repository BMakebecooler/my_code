<?php


namespace common\components\sendSms;

use yii\base\Component;
use Exception;
use Yii;

class SmsDanytechService extends Component implements SendSms
{

    public $services;

    public $classLogger;

    public $serviceId;

    public $pass;

    public $source;

    public $flash;

    protected $logger;

    protected $service = 'danytech';

    protected $sendUrl = 'https://api.seven.tech/send';

//    protected $sendUrl = 'https://api.danytech.ru/send';

    protected $codesStatus = [
        2 => ['status' => 'DELIVERED','answer' => 'Сообщение успешно доставлено','code' => 2],
        3 => ['status' => 'EXPIRED', 'answer' => 'Время жизни сообщения истекло', 'code' => 3],
        5 => ['status' => 'UNDELIVERED', 'answer' => 'Сообщение не может быть доставлено', 'code' => 5],
        8 => ['status' => 'REJECTED', 'answer' => 'Сообщение отклонено', 'code' => 8]
    ];

    public function __construct($config = [])
    {
        parent::__construct($config);

        $classLogger = $this->classLogger ?? null;

        if($classLogger){
            $this->logger = new $classLogger;
        }
    }

    /**
     * Send Sms
     *
     * @param string $phone
     * @param string $statusCode
     * @param $flash
     *
     * @throws Exception
     * @return bool
     */
    public function sendSms(string $phone, string $text, $flash = false)
    {
        $phone = \common\helpers\Strings::onlyInt($phone);
        if(!preg_match('/^7[34589]\d{9}$(?<!(7940\d{7}))/', $phone)){
            throw new Exception('Phone is not valid');
        }

        if(!$text){
            throw new Exception('Text is required');
        }

        $params = [
            'clientId' => $phone,
            'serviceId' => $this->serviceId,
            'pass'  => $this->pass,
            'source' => $this->source,
            'message'  => $text,
//            'sending_time' => $sending_time,
//            'time_zone' => 'Europe/Moscow',
//            'flash' => $flash ? 1 : 0,
//            'partnerMsgId' =>
        ];
        if($this->logger){
            $params['ptag'] = $this->logger->addLog([
                'phone' => $phone,
                'text'  => $text,
                'provider' => $this->service
            ]);
        }

        return $this->sendRequest($this->sendUrl,$params);
    }

    /**
     * Send Response
     * @throws Exception
     * @return void
     */
    public function state()
    {
        $transactionId = Yii::$app->request->get('transactionId');
        $status = Yii::$app->request->get('status',2);

        if(!$transactionId){
            $this->sendResponse(400,'Transaction not found');
        }

        if($this->logger) {
            $check = $this->logger->check($transactionId);
            if(!$check){
                $this->sendResponse(400,'Transaction not found');
            }else{
                $data = isset($this->codesStatus[$status]) ? $this->codesStatus[$status] : [];
                $this->logger->updateLog($transactionId,$data);
                $this->sendResponse(200,'OK');
            }
        }else{
            $this->sendResponse(200,'OK');
        }
    }

    /**
     * Send Response
     *
     * @param integer $statusCode
     * @param string $content
     * @throws Exception
     * @return void
     */
    protected function sendResponse(int $statusCode, string $content)
    {
        Yii::$app->response->statusCode = $statusCode;
        Yii::$app->response->content = $content;
        Yii::$app->end();
    }

    /**
     * Send Request
     *
     * @param $requestUrl
     * @param null $options
     * @return bool
     */
    protected function sendRequest($requestUrl, $options = null)
    {
        try {

            mb_internal_encoding("UTF-8");

            $ch = curl_init();

            if (false === $ch) {
                throw new Exception('failed to initialize');
            }

            $requestUrl .= '?'.http_build_query($options);

            curl_setopt($ch, CURLOPT_URL, $requestUrl);
            curl_setopt($ch, CURLOPT_HTTPGET, $options);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);

            if (false === $result) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            $info = curl_getinfo($ch);
            curl_close($ch);

            return $info['http_code'] == 200 ? true : false;


        } catch (Exception $e) {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);

        }
    }
}