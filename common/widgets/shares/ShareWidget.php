<?php

namespace common\widgets\shares;

use common\helpers\Url;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shares\SsShareSchedule;
use skeeks\cms\base\WidgetRenderable;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContent;
use skeeks\cms\shop\models\ShopContent;
use common\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property CmsContent $cmsContent;
 * @property ShopContent $shopContent;
 * @property []                 $childrenElementIds;
 *
 * Class ShareWidget
 * @package skeeks\cms\shop\cmsWidgets\filters
 */
class ShareWidget extends WidgetRenderable
{

    /**
     * Тип баннера
     * @var
     */
    public $type;
    public $defaultImage;
    /**
     * @var SsShareSchedule $block
     */
    public $block;

    /**
     * Активность
     * @var string
     */
    public $is_active = Cms::BOOL_Y;

    /**
     * @var SsShare
     */
    protected $share;
    protected $shares = [];

    protected static $typeIds = [];

    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => 'Настройка баннера',
        ]);
    }

    public function init()
    {
        parent::init();

        /*        if ($this->is_active === \skeeks\cms\components\Cms::BOOL_N) {
                    return false;
                }*/


        $this->getShareData();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'type' => 'Тип баннера',
                'is_active' => 'Активность',
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
//                [['type'], 'integer'],
                [['type'], 'string'],
                [['is_active', 'defaultImage'], 'safe'],
            ]);
    }

    public function renderConfigForm(ActiveForm $form)
    {
        echo \Yii::$app->view->renderFile(__DIR__ . '/_form.php', [
            'form' => $form,
            'model' => $this
        ], $this);
    }

    protected function getTimeForBanner()
    {
//        return strtotime('2017-08-17 10:00:00');
        //if(\common\helpers\User::isDeveloper()) return time() + 86400;
        return SsShare::getDate();
    }

    /**
     * Получить 1 баннер
     */
    protected function getShareOne()
    {
        $shareQuery = SsShare::find()
            ->andWhere(['banner_type' => $this->type])
            ->andWhere(['not', ['image_id' => null]])
            ->andWhere(['not', ['active' => Cms::BOOL_N]])
            ->andWhere('begin_datetime <= :time AND end_datetime >= :time', [
                ':time' => $this->getTimeForBanner(),
            ])
            ->andWhere(['NOT IN', 'id', isset(self::$typeIds[$this->type]) ? self::$typeIds[$this->type] : []])
            ->orderBy('ss_shares.begin_datetime ASC, ss_shares.id ASC')
            ->limit(1);

        if ($this->block) {
            $shareQuery->byBlockId($this->block->id);
        }

        $this->share = $shareQuery->one();

        if ($this->share && !$this->block) {
            self::$typeIds[$this->type][] = $this->share->id;
        }
    }

    /**
     * Получить все баннера по типу
     */
    protected function getShareAll()
    {
        $this->shares = SsShare::find()
            ->andWhere(['banner_type' => $this->type])
            ->andWhere(['not', ['image_id' => null]])
            ->andWhere(['not', ['active' => Cms::BOOL_N]])
            ->andWhere('begin_datetime <= :time AND end_datetime >= :time', [
                ':time' => $this->getTimeForBanner(),
            ])
            ->orderBy('ss_shares.begin_datetime ASC, ss_shares.id ASC')
            ->all();
    }

    public function getShareWithType($shareType){
        return SsShare::find()
            ->andWhere(['banner_type' => $shareType])
            ->andWhere(['not', ['image_id' => null]])
            ->andWhere(['not', ['active' => Cms::BOOL_N]])
            ->andWhere('begin_datetime <= :time AND end_datetime >= :time', [
                ':time' => $this->getTimeForBanner(),
            ])
            ->one();
    }

    /**
     * Получить баннер
     */
    protected function getShareData()
    {
        switch ($this->type) {
            case SsShare::CATALOG_SECTION_ACTION:
            case SsShare::BANNER_TYPE_MAIN_WIDE_1:
            case SsShare::BANNER_TYPE_MAIN_WIDE_MOBILE:
                return $this->getShareAll();
            default:
                return $this->getShareOne();
        }
    }

    /**
     * Получить акции по типу
     * @return array|SsShare[]
     */
    public function getShares()
    {
        return $this->shares;
    }

    /**
     * Получить акцию
     * @return SsShare
     */
    public function getShare()
    {
        return $this->share;
    }

    /**
     * @param SsShare|null $share
     * @return mixed
     */
    public function getImage(SsShare $share = null)
    {
        $share = ($share) ?: $this->share;

        return $share ? Url::withCdnPrefix($share->getImageSrc()) : '';
    }
}