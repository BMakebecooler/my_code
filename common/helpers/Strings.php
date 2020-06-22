<?php
/**
 * Created by PhpStorm.
 * User: koval
 * Date: 02.04.17
 * Time: 22:56
 */

namespace common\helpers;


use skeeks\modules\cms\money\Money;

class Strings
{
    public static $emailDomains = [
        'shopandshow.ru',
        'mail.ru',
        'inbox.ru',
        'list.ru',
        'bk.ru',
        'yandex.ru',
        'yandex.kz',
        'yandex.ua',
        'ya.ru',
        'gmail.com',
        'rambler.ru'
    ];

    const NUM_DIGITS_LOT = 3;
    const COUNT_PARTS_LOT = 3;
    const YOUTUBE_CODE_LENGTH = 11;
    const YOUTUBE_GET_PARAM = 'v';

    /**
     * ucFirst для UTF-8
     * @param $string
     * @return string
     */
    public static function ucFirst($string)
    {
        $string = self::trim($string);
        return mb_strtoupper(self::getFirstSymbol($string), 'utf-8') . mb_substr($string, 1, mb_strlen($string) - 1, 'utf-8');
    }

    /**
     * Получение первого символа строки
     * @param $string
     * @return string
     */
    public static function getFirstSymbol($string)
    {
        return mb_substr(self::trim($string), 0, 1, 'utf-8');
    }

    /**
     * Генерация случайного строкового значения
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        mt_srand();

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Генерация случайного значения
     * @param int $length
     * @return string
     */
    public static function generateRandomInt($length = 10)
    {
        $characters = '0123456789';
        $randomString = '';

        mt_srand();

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Конвертировать string в bool
     * @param $value
     * @return string
     */
    public static function strToBool($value)
    {
        return ($value === 'true') ? true : false;
    }

    /**
     * Конвертировать bool в string
     * @param $value
     * @return string
     */
    public static function boolToStr($value)
    {
        return $value ? 'true' : 'false';
    }

    /**
     * Перенос строк
     * @param $string
     * @param int $width
     * @param string $break
     * @param bool $cut
     * @return mixed
     */
    public static function wordwrap($string, $width = 40, $break = "\n", $cut = false)
    {
        if (mb_strlen($string, 'UTF-8') > $width) {
            if ($cut) {
                $search = '/(.{1,' . $width . '})(?:\s|$)|(.{' . $width . '})/uS';
                $replace = '$1$2' . $break;
            } else {
                $search = '/(?=\s)(.{1,' . $width . '})(?:\s|$)/uS';
                $replace = '$1' . $break;
            }
            return preg_replace($search, $replace, $string);
        } else {
            return $string;
        }
    }


    public static function trim($value)
    {
        $value = trim($value);
        $value = str_replace(["\r", "\t", "\xC2xA0"], ['', '', ' '], $value);
        return preg_replace("/(\s){3,}/", "$1$1", $value);
    }

    /**
     * Удалить пробелы из строки
     * @param $value
     * @return mixed
     */
    public static function removeSpaces($value)
    {
        return preg_replace('/\s+/', '', $value);
    }

    /**
     * Замена символов пробела
     * @param $value
     * @param string $replacement
     * @return mixed
     */
    public static function replaceSpaces($value, $replacement = ' ')
    {
        return str_replace(["\s", "&nbsp;", "\xc2\xa0"], $replacement, $value);
    }

    /**
     * Удалить числа из строки
     * @param $string
     * @return string
     */
    public static function removeInteger($string)
    {
        return trim(str_replace(range(0, 9), '', $string));
    }

    /**
     * @param $value
     * @return Money
     */
    public static function getMoney($value)
    {
        return Money::fromString((string)$value, 'RUB');
    }

    /**
     * @param $value
     * @return string
     */
    public static function getMoneyFormat($value)
    {
        $money = self::getMoney($value);

        return \Yii::$app->money->convertAndFormat($money);
    }

    /**
     * @return string
     */
    public static function roubleSymbolSpan()
    {
        return ' <span class="rouble-money">Р</span>';
    }

    /**
     * Удалить все не цифровые символы из строки
     * @param $string
     * @return mixed
     */
    public static function onlyInt($string)
    {
        return preg_replace('/\D/', '', $string);
    }

    /**
     * Транслитерация строки с возможностью использования в url
     * @param $value
     *
     * @return mixed|string
     */
    public static function translit($value)
    {
        if (preg_match('/[^A-Za-z0-9_\-]/', $value)) {
            $tr = array(
                "А" => "a", "Б" => "b", "В" => "v", "Г" => "g",
                "Д" => "d", "Е" => "e", "Ж" => "j", "З" => "z", "И" => "i",
                "Й" => "y", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n",
                "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t",
                "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "ts", "Ч" => "ch",
                "Ш" => "sh", "Щ" => "sch", "Ъ" => "", "Ы" => "yi", "Ь" => "",
                "Э" => "e", "Ю" => "yu", "Я" => "ya", "а" => "a", "б" => "b",
                "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
                "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
                "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
                "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
                "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
                "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
                //" "=> "_", "."=> "", "/"=> "_"
                " " => "-", "." => "", "/" => "_"
            );
            $value = strtr($value, $tr);
            $value = preg_replace('/[^A-Za-z0-9_\-]/', '', $value);
        }

        return $value;
    }

    /**
     * Добавляет <br> для битриксовых записей
     * @param $value
     *
     * @return string
     */
    public static function bxHtml2br($value)
    {
        return nl2br(str_replace('<br />', '', \yii\helpers\Html::decode($value)));
    }

    /**
     * Содержит ли массив искомую строку
     * @param $str
     * @param array $arr
     * @return bool
     */
    public static function containsArray($str, array $arr)
    {
        foreach ($arr as $a) {
            if (stripos($str, $a) !== false) return true;
        }

        return false;
    }

    /**
     * Парсит название лота, получая из него номер, id и имя
     * @param string $str
     * @return array
     */
    public static function parsProductName(string $name)
    {
        $result = [];
        //Для старого формата (1С)
        preg_match('/^\[([\d\-]+)\]\s*(.+)\s*\(0?0?(\d+)\)/', trim($name), $match);
        //Для нвого формата (КФСС)
        preg_match('/^\[([\d\-]+)\]\s*(.+)\s*/', trim($name), $match2);

        if ($match) {
            $result['NUM'] = trim($match[1]);
            $result['NAME'] = trim($match[2]);
            $result['ID'] = trim($match[3]);
        } elseif ($match2) {
            $result['NUM'] = trim($match2[1]);
            $result['NAME'] = trim($match2[2]);
            $result['ID'] = self::onlyInt(trim($match2[1]));
        } else {
            \Yii::error("Can't parse product name: {$name}. Match: " . var_export($match, true) . ' | Match2: ' . var_export($match2, true), __METHOD__);
        }

        return $result;
    }

    /**
     * Получает номер лота из url
     * @param string $pathInfo
     * @return string|bool
     */
    public static function getLotNumFromStr(string $pathInfo, $flag = null)
    {

        if (!preg_match('/\/(?<id>\d+)\-(?<code>\S+)$/i', "/" . $pathInfo, $matches)) {
            return false;
        }
        if (!$matches[0]) {
            return false;
        }

        $parts = explode('-', str_replace('/', '', $matches[0]));
        $patsLot = [];
        foreach ($parts as $part) {
            if (strlen($part) == self::NUM_DIGITS_LOT) {
                $patsLot[] = $part;
                if (count($patsLot) == self::COUNT_PARTS_LOT) {
                    break;
                }
            }
        }

        if ($flag) {
            return count($patsLot) ? implode('-', $patsLot) : '-';
        } else {
            return count($patsLot) ? implode('-', $patsLot) : false;
        }
    }

    public static function formatPhone($phone, $format = "%s%s%s%s")
    {
        $phoneInt = self::onlyInt($phone);
        $phoneLength = strlen($phoneInt);
        if ($phoneLength >= 10) {
            $phoneCode = substr($phoneInt, -10, 3);
            $phonePart1 = substr($phoneInt, -7, 3);
            $phonePart2 = substr($phoneInt, -4, 2);
            $phonePart3 = substr($phoneInt, -2);

//            $result = sprintf("%s%s%s%s%s", '+7', $phoneCode, $phonePart1, $phonePart2, $phonePart3);
            $result = sprintf($format, $phoneCode, $phonePart1, $phonePart2, $phonePart3);
        }

        return $result ?? $phoneInt;
    }

    //Получить "чистый" номер телефона, пригодный для хранения в бд. Только цифры 11 знаков с ведущей 7
    public static function getPhoneClean($phone, $withCode = false)
    {
        $phoneInt = self::onlyInt($phone);
        $phoneLength = strlen($phoneInt);

        if ($phoneLength >= 10) {
            return ($withCode ? '7' : '') . substr($phoneInt, -10);
        }

        return false;
    }

    public static function clearParamName($name)
    {
        $return = explode(' ', $name);
        $return = $return[0];
        $return = explode('-', $return);
        $return = $return[0];
        $return = explode(',', $return);
        $return = $return[0];
        $return = explode('/', $return);
        $return = $return[0];
        $return = explode('\\', $return);
        $return = $return[0];
        $return = explode(')', $return);
        $return = $return[0];
        $return = explode('(', $return);
        $return = $return[0];
        $return = explode(';', $return);
        $return = $return[0];
        $return = explode(':', $return);
        $return = $return[0];

        $return = preg_replace("/[^0-9]/", '', $return);

        if (!$return) {
            $return = 'onesize';
        }

        return $return;
    }

    public static function checkEmailDomain($email)
    {
        //todo пака не нужно проверять на домены в email
        return true;

//        $emailParts = explode('@', $email);
//        if (count($emailParts) == 2) {
//            return in_array($emailParts[1], self::$emailDomains);
//        } else {
//            return false;
//        }

    }

    public static function getYoutubeCodeFromString(string $string)
    {
        $code = null;
        $urlData = parse_url($string);

        //это не ссылка
        if (count($urlData) == 1) {
            $code = $string;
        } else {

            //это ссылка вида https://www.youtube.com/watch?v=tSZ0608xHlk
            if (isset($urlData['query'])) {
                parse_str($urlData['query'], $query);
                if (isset($query[self::YOUTUBE_GET_PARAM])) {
                    $code = $query[self::YOUTUBE_GET_PARAM];
                } else {
                    \Yii::error('Invalid video link submitted: ' . $string, __METHOD__);
                }
            } else {
                //Это ссылка вида https://youtu.be/vqlN5PWCpQE
                $data = explode('/', $urlData['path']);
                if (count($data) <= 1) {
                    \Yii::error('Invalid video link submitted: ' . $string, __METHOD__);
                }
                $code = count($data) ? end($data) : null;
            }
        }

        //Код youtube не может содержать кириллические символы
        if (preg_match("/[А-Яа-я]/", $code)) {
            \Yii::error('Invalid video link submitted: ' . $string, __METHOD__);
        }

        //Код youtube строго определенной длинны
        if (strlen($code) != self::YOUTUBE_CODE_LENGTH) {
            \Yii::error('Invalid video link submitted: ' . $string, __METHOD__);
        }

        return $code;
    }

    /**
     * Определить разделитель строки
     *
     * @var string $str
     */
    public static function getSplitChar($str)
    {
        $s = preg_replace('/".+"/isU', '*', $str);
        $a = [',', ';', '|']; //список разделителей
        $r = '';
        $i = -1;
        foreach ($a as $c) {
            if (($n = sizeof(explode($c, $s))) > $i) {
                $i = $n;
                $r = $c;
            }
        }
        return $r;
    }
}