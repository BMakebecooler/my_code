<?php
/**
 * Translit
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 20.10.2014
 * @since 1.0.0
 */

namespace modules\shopandshow\filters\string;

use skeeks\sx\Filter as SXFilter;
use skeeks\sx\filters\string\Translit;

/**
 * Class Cx_Filter_String_Strtoupper
 */
class FilterSeoPageName // extends SXFilter
{

    public $maxLength = 64;
    /**
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $value = trim($value);

        if (mb_strlen($value) < 2)
        {
            $value = $value . "-" . md5(microtime());
        }

        if (mb_strlen($value) > $this->maxLength)
        {
            $value = mb_substr($value, 0, $this->maxLength);
        }

        $value = \common\helpers\Strings::translit(trim($value));
        $value = strtolower($value);

        $result = [];
        if ($array = explode("-", $value))
        {
            foreach ($array as $node)
            {
                if (trim($node))
                {
                    $result[] = trim($node);
                }
            }
        }
        //Убрать - с начала строки, и с несоклько - из середины
        $value = implode("-", $result);


        //Небольшая рекурсия
        if (strlen($value) < 2 || strlen($value) > $this->maxLength)
        {
            $value = $this->filter($value);
        }

        return $value;
    }
}