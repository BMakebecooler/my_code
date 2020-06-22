<?php

/**
 * Виджет для блока ЦТС
 */

namespace common\widgets\cts;

use modules\shopandshow\lists\Shares;
use modules\shopandshow\models\shares\SsShare;
use yii\base\Widget;

class Cts extends Widget
{

    public $label = 'Цена только сегодня';
    public $description = 'Каждый день мы представляем один товар по суперцене';

    public $viewFile = '@template/widgets/Cts/one-cts-plus-buy';

    public $params = [];

    public $isCts = true;

    /**
     * @var \modules\shopandshow\models\shares\SsShare
     */
    public $share = null;
    public $shares = null;

    public function init()
    {
        parent::init();

        if(\Yii::$app->mobileDetect->isMobile()) {
            $this->description = null;
            $this->label = null;
        }
//        $this->share = $this->getCtsShare();

/*        if ($this->share && $this->share->name) {
            $this->label = $this->share->name;
        }
        if ($this->share && $this->share->description) {
            $this->description = $this->share->description;
        }*/

//        $this->product = $this->getCtsProduct();
//        $this->products = $this->getCtsProducts();
    }

    public function run()
    {
        return $this->render($this->viewFile,['isCts' => $this->isCts]);
    }

    /**
     * Вернуть баннер ЦТС
     * @return array|\modules\shopandshow\models\shares\SsShare|null|\yii\db\ActiveRecord
     */
    public function getCtsShare()
    {
        if ($this->share) {
            return $this->share;
        }

        if(\Yii::$app->mobileDetect->isMobile()) {
            $type = SsShare::BANNER_TYPE_CTS_MOBILE;
        } else {
            $type = SsShare::BANNER_TYPE_CTS;
        }

        return $this->share = Shares::getShareByTypeEfir($type);
    }

    /**
     * Вернуть баннера ЦТС
     * @param int $limit? количество баннеров
     * @return array|null|\yii\db\ActiveRecord[]|\modules\shopandshow\models\shares\SsShare[]
     */
    public function getCtsShares($limit = 5)
    {
        if ($this->shares) {
            return $this->shares;
        }

        if(\Yii::$app->mobileDetect->isMobile()) {
            $type = SsShare::BANNER_TYPE_CTS_MOBILE;
        } else {
            $type = SsShare::BANNER_TYPE_CTS;
        }

        return $this->shares = Shares::getSharesByTypeEfir($type, $limit);
    }
}