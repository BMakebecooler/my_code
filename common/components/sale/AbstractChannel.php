<?php


namespace common\components\sale;

use skeeks\cms\shop\models\ShopBuyer;
use Yii;


abstract class AbstractChannel
{
    protected $label;

    /**
     * @todo Разобраться, почему не всегда срабатывает механизм сохраненния даннных в сессию, используя yii
     */

    public static function isFirstBuy(ShopBuyer $buyer)
    {
        return $buyer->getShopOrders()->count() == 1;
    }

    protected function redirect()
    {
        $url = \Yii::$app->request->getUrl();
        \Yii::$app->response->redirect($url, 200);
        \Yii::$app->end();
    }

    public function setLabelData()
    {
        $label = $this->getLabelData();
        if (!$label) {

            $data = Yii::$app->request->get($this->label);
            if ($data) {
                $_SESSION[$this->label] = $data;
                $this->redirect();
//                Yii::$app->session->set($this->label, $data);
            }
        }
    }

    public function deleteLabelData()
    {
        unset($_SESSION[$this->label]);
//        $_SESSION[$this->label] = null;
        $this->redirect();
//        Yii::$app->session->remove($this->label);
    }

    public function getLabelData()
    {
//        $data =  Yii::$app->response->cookies->getValue($this->label);
        $data = $_SESSION[$this->label] ?? null;
//        $data = Yii::$app->session->get($this->label);
        return $data ? $data : null;
    }


}