<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 19.09.2017
 * Time: 17:22
 */

namespace modules\shopandshow\components\mail;

use modules\shopandshow\models\shares\SsShare;

/**
 * Class BaseTemplate
 *
 * @property string $absUrl
 * @property string $absImgPath
 *
 * @package modules\shopandshow\components\mail
 */
class BaseTemplate extends \yii\base\Component
{
    /** @var bool генерить ссылки в формате getresponse */
    public $useLinksForGetresponse = false;
    /** @var  string utm для GA */
    public $utm;
    /** @var string path to viewFile */
    public $viewFile;

    /** @var $mail_dispatch \modules\shopandshow\models\mail\MailDispatch */
    public $mail_dispatch;

    /** @var $tree_pid int - pid для выборки из дерева */
    public $tree_pid = 9;
    /** @var $tree_id integer раздел для вывода товаров */
    public $tree_id;
    /** @var $begin_date integer timestamp дата рассылки */
    public $begin_date;
    /** @var $viewed_at integer timestamp дата для сортировки просмотренных элементов */
    public $viewed_at;

    protected $period_begin;
    protected $period_end;

    /** @var array массив данных для шаблона */
    public $data = [];

    public function init()
    {
        if (!$this->mail_dispatch) {
            throw new \Exception('Не указан объект рассылки');
        }

        if(!$this->viewFile) {
            throw new \Exception('Не указан шаблон viewFile');
        }

        if (!$this->begin_date) {
            $this->begin_date = time();
        }
        // по умолчанию ставим за последние 7 дней
        if (!$this->viewed_at) {
            $this->viewed_at = time() - 7*24*60*60;
        }

        if(!$this->utm) {
            $this->utm = 'utm_source=email&utm_medium=email_cts&utm_campaign='.date('YmdHi');
        }

        $this->period_begin = strtotime(date('Y-m-d 00:00:00', $this->begin_date));
        $this->period_end = strtotime(date('Y-m-d 23:59:59', $this->begin_date));

        $this->data["SUBJECT"] = $this->mail_dispatch->subject;
    }

    public function render()
    {
        return \Yii::$app->controller->renderPartial($this->viewFile, ['data' => $this->data, 'template' => $this]);
    }

    public function getResponseLink($url) {
        if(empty($url)) return $url;

        if (strpos($url, '?') === false) {
            if(strpos($url, '#') === false) {
                $result = $url.'?'.$this->utm;
            }
            else {
                list($link, $anchor) = explode('#', $url, 2);
                $result = $link.'?'.$this->utm;
                if ($anchor) {
                    $result .= '#'.$anchor;
                }
            }
        }
        else {
            if(strpos($url, '#') === false) {
                $result = $url.'&'.$this->utm;
            }
            else {
                list($link, $anchor) = explode('#', $url, 2);
                $result = $link.'&'.$this->utm;
                if ($anchor) {
                    $result .= '#'.$anchor;
                }
            }
        }

        $result = $this->makeAbsUrl($result);

        if($this->useLinksForGetresponse) return '{{LINK "'.$result.'"}}';
        return $result;
    }

    public function makeAbsUrl($url) {
        if (strpos(strtolower($url), 'http') !== 0 && strpos(strtolower($url), 'mailto:') !== 0 && strpos(strtolower($url), 'tel:') !== 0) {
            $url = $this->absUrl . $url;
        }

        return $url;
    }

    public function getAbsUrl()
    {
        if(\Yii::$app instanceof \yii\web\Application) {
            return \Yii::$app->urlManager->hostInfo;
        }
        else {
            return \Yii::$app->urlManager->baseUrl;
        }
    }

    public function getAbsImgPath()
    {
        return $this->absUrl . \Yii::getAlias('@web_common').'/img/sands_cts';
    }

    public function getTreeMenuList()
    {
        return \common\models\Tree::find()
            ->andWhere(['level' => 2])
            ->andWhere(['active' => 'Y'])
            ->andWhere(['tree_type_id' => CATALOG_TREE_TYPE_ID])
            ->andWhere(['pid' => $this->tree_pid])
            ->orderBy(['priority' => SORT_ASC])
            ->all();
    }

    public static function getStaticTreeMenuList()
    {
        return \common\models\Tree::find()
            ->andWhere(['level' => 2])
            ->andWhere(['active' => 'Y'])
            ->andWhere(['tree_type_id' => CATALOG_TREE_TYPE_ID])
            ->andWhere(['pid' => 9])
            ->orderBy(['priority' => SORT_ASC])
            ->all();
    }
}