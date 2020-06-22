<?php
namespace modules\shopandshow\widgets\mail;

use ErikvdVen\Gif\GIFGenerator;
use yii\base\Widget;
use DateTime;

class GifTimerWidget extends Widget
{
    public $font;
    public $numFrames = 30;

    public $timerEnd;
    public $timerTemplate;

    public $textDefaults = array(
        'angle' => 0,
        'fonts-size' => 17,
        'fonts-color' => '#98002D',
        'y-position' => 35
    );

    public function init()
    {
        if (!$this->timerTemplate) {
            $this->timerTemplate = APP_DIR.'/'.\Yii::getAlias('@web_common').'/img/newsletter/cts-header-timer.jpg';
        }

        if (!$this->font) {
            $this->font = APP_DIR.'/'.\Yii::getAlias('@web_common').'/fonts/glober_bold.woff';
        }

        if (!$this->timerEnd) {
            $this->timerEnd = strtotime('tomorrow');
        }

        parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        $gif = new GIFGenerator(['fonts' => $this->font]);

        // Get pending time
        $now = new DateTime();
        $future_date = new DateTime();
        $future_date->setTimestamp($this->timerEnd);
        $interval = $future_date->diff($now);

        // Create a multidimensional array with all the image frames
        $imageFrames = array('repeat' => true, 'frames' => array());

        for ($i = 1; $i <= $this->numFrames; $i++) {

            // время вышло
            if ($interval->invert == 0) {
                $clockParts = array(
                    'h' => '00',
                    'd1' => ':',
                    'm' => '00',
                    'd2' => ':',
                    's' => '00'
                );
            }
            else {
                $clockParts = array(
                    'h' => $interval->format("%H"),
                    'd1' => ':',
                    'm' => $interval->format("%I"),
                    'd2' => ':',
                    's' => $interval->format("%S")
                );
            }

            $imageFrames['frames'][$i] = array('image' => $this->timerTemplate, 'delay' => 100);

            foreach ($clockParts as $key => $value) {
                $imageFrames['frames'][$i]['text'][$key] = $this->textDefaults;
                $imageFrames['frames'][$i]['text'][$key]['text'] = $value;
            }

            $imageFrames['frames'][$i]['text']['h']['x-position'] = 571;
            $imageFrames['frames'][$i]['text']['d1']['x-position'] = 601;
            $imageFrames['frames'][$i]['text']['m']['x-position'] = 611;
            $imageFrames['frames'][$i]['text']['d2']['x-position'] = 641;
            $imageFrames['frames'][$i]['text']['s']['x-position'] = 651;

            // конец
            if ($clockParts['h'] == '00' && $clockParts['m'] == '00' && $clockParts['s'] == '00') {
                break;
            }

            // // And again..
            $future_date = $future_date->modify("-1 second");
            $interval = $future_date->diff($now);
        }

        return $gif->generate($imageFrames);
    }
}