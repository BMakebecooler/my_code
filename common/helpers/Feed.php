<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 14/01/2019
 * Time: 14:29
 */

namespace common\helpers;


class Feed
{


    /**
     * Removes invalid characters from a UTF-8 XML string
     *
     * @access public
     * @param string a XML string potentially containing invalid characters
     * @return string
     */
    public static function sanitizeXML($string)
    {
        if (!empty($string)) {
            $regex = '/(
            [\xC0-\xC1] # Invalid UTF-8 Bytes
            | [\xF5-\xFF] # Invalid UTF-8 Bytes
            | \xE0[\x80-\x9F] # Overlong encoding of prior code point
            | \xF0[\x80-\x8F] # Overlong encoding of prior code point
            | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
            | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
            | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
            | (?<=[\x0-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
            | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
            | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
            | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
            | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
        )/x';
            $string = preg_replace($regex, '', $string);

            $result = "";
            $current;
            $length = strlen($string);
            for ($i = 0; $i < $length; $i++) {
                $current = ord($string{$i});
                if (($current == 0x9) ||
                    ($current == 0xA) ||
                    ($current == 0xD) ||
                    (($current >= 0x20) && ($current <= 0xD7FF)) ||
                    (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                    (($current >= 0x10000) && ($current <= 0x10FFFF))) {
                    $result .= chr($current);
                } else {
//                    $ret;    // use this to strip invalid character(s)
                    $ret .= " ";    // use this to replace them with spaces
                }
            }
            $string = $result;
        }
        return $string;
    }


    public static function remove($some_string)
    {
        //reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ?
        $some_string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
            '|[\x00-\x7F][\x80-\xBF]+' .
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
            '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
            '?', $some_string);

//reject overly long 3 byte sequences and UTF-16 surrogates and replace with ?
        $some_string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]' .
            '|\xED[\xA0-\xBF][\x80-\xBF]/S', '?', $some_string);


        foreach (self::getMappingReplace() as $old => $new) {
            $some_string = mbereg_replace($old, $new, $some_string);
        }
        return $some_string;
    }

    public static function getMappingReplace()
    {
        return [
            '"' => '&quot;',
            '&' => '&quot;',
            '>' => '&gt;',
            '<' => '&lt;',
            '\'' => '&apos;'
        ];
    }

}