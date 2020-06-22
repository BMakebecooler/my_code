<?php

namespace common\components\email\services\modules\newsLetters;

use yii\base\Model;

/**
 * @link http://apidocs.getresponse.com/v3/resources/newsletters#newsletters.create
 * Class GRCreateNewsLettersOptions
 */
class GRCreateNewsLettersOptions extends Model
{
    public $name;
    public $type;
    public $editor;
    public $subject; //*
    public $fromField; //*
    public $replyTo;
    public $campaign; //*
    public $content; //*
    public $flags;
    public $attachments;
    public $sendSettings; //*
}