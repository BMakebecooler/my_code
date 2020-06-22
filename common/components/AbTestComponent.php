<?php

namespace common\components;

use yii\base\Component;

/**
 *
 * @property string $main
 * @property string $ab
 * @property string $script
 */
class AbTestComponent extends Component
{
    public $ABgetParam = 'gaexp';

    private $abValue;

    /**
     * is Main AB domain
     * @return bool
     */
    public function isMain()
    {
        return empty(\Yii::$app->request->get($this->ABgetParam));
    }

    /**
     * get A or B domain
     * @return string
     */
    public function getAb()
    {
        if (!$this->abValue) {
            $abValue = \Yii::$app->request->get($this->ABgetParam);
            $this->abValue = !empty($abValue) ? strtoupper($abValue) : 'A';
        }

        return $this->abValue;
    }

    /**
     * Метод для простой проверки АБтеста с помощью магических вызовов isA(), isB() и т.д.
     * @param string $name
     * @param array $params
     * @return bool|mixed|null
     */
    public function __call($name, $params)
    {
        if (preg_match('/^is(.+)$/i', $name, $match)) {
            return $this->ab == strtoupper($match[1]);
        }
        return parent::__call($name, $params);
    }

    /**
     * Код Google Experiments
     * Никаких registerJS, никаких ассетов
     * Код должен вставляться сразу после <head>, чтобы успеть средиректить на Б, пока не загрузилось ничего остального
     * @return string
     */
    public function getScript()
    {
        return '';
        if (
            \Yii::$app->appComponent->isSiteSS()
            && $this->isMain()
            && \Yii::$app->controller
            // не выводить скрипт на этом урле: site/reset-password
            && !in_array(\Yii::$app->controller->route, ['site/reset-password', 'site/request-password-reset', 'site/login', 'site/signup', 'site/check-user', 'site/back'])
        ) {
            return <<<SCRIPT
<!-- Google Analytics Content Experiment code -->
<script>function utmx_section(){}function utmx(){}(function(){var
k='151138637-45',d=document,l=d.location,c=d.cookie;
if(l.search.indexOf('utm_expid='+k)>0)return;
function f(n){if(c){var i=c.indexOf(n+'=');if(i>-1){var j=c.
indexOf(';',i);return escape(c.substring(i+n.length+1,j<0?c.
length:j))}}}var x=f('__utmx'),xx=f('__utmxx'),h=l.hash;d.write(
'<sc'+'ript src="'+'http'+(l.protocol=='https:'?'s://ssl':
'://www')+'.google-analytics.com/ga_exp.js?'+'utmxkey='+k+
'&utmx='+(x?x:'')+'&utmxx='+(xx?xx:'')+'&utmxtime='+new Date().
valueOf()+(h?'&utmxhash='+escape(h.substr(1)):'')+
'" type="text/javascript" charset="utf-8"><\/sc'+'ript>')})();
</script><script>utmx('url','A/B');</script>
<!-- End of Google Analytics Content Experiment code -->
SCRIPT;

            //OLD
            return <<<SCRIPT
<!-- Google Analytics Content Experiment code -->
<script>function utmx_section(){}function utmx(){}(function(){var
k='162933736-35',d=document,l=d.location,c=d.cookie;
if(l.search.indexOf('utm_expid='+k)>0)return;
function f(n){if(c){var i=c.indexOf(n+'=');if(i>-1){var j=c.
indexOf(';',i);return escape(c.substring(i+n.length+1,j<0?c.
length:j))}}}var x=f('__utmx'),xx=f('__utmxx'),h=l.hash;d.write(
'<sc'+'ript src="'+'http'+(l.protocol=='https:'?'s://ssl':
'://www')+'.google-analytics.com/ga_exp.js?'+'utmxkey='+k+
'&utmx='+(x?x:'')+'&utmxx='+(xx?xx:'')+'&utmxtime='+new Date().
valueOf()+(h?'&utmxhash='+escape(h.substr(1)):'')+
'" type="text/javascript" charset="utf-8"><\/sc'+'ript>')})();
</script><script>utmx('url','A/B');</script>
<!-- End of Google Analytics Content Experiment code -->
SCRIPT;
        }


        return '';
    }
}