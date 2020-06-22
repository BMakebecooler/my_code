<?php

namespace common\helpers;

use common\models\ProductParamType;

class Property
{
    public static $defaultSizePropertyProperName = 'размер';

    public static $propertyProperNameMap = [
        'KFSS_RAZMER_BYUSTGALTERA'              => '',
        'KFSS_RAZMER_BYUSTGALTERA_1'            => '',
        'KFSS_RAZMER_BYUSGALTERA_1S'            => '',
        'KFSS_RAZMER_KOLTSA'                    => '',
        'KFSS_RAZMER_KOLTSA_1'                  => '',
        'KFSS_RAZMER_OBSCHEE_OPISANIE'          => '',
        'KFSS_RAZMER_OBUVI'                     => '',
        'KFSS_RAZMER_OBUVI_1'                   => '',
        'KFSS_RAZMER_OBUVI_2'                   => '',
        'KFSS_RAZMER_OBUVI_OSNOVNAYA_SHKALA'    => '',
        'KFSS_RAZMER_ODEJDYI'                   => '',
        'KFSS_RAZMER_ODEJDYI_1'                 => '',
        'KFSS_RAZMER_ODEJDYI___BUKVYI'          => '',
        'KFSS_RAZMER_ODEJDYI___BUKVYI_1'        => '',
        'KFSS_RAZMER_ODEJDYI___TSIFRYI'         => '',
        'KFSS_RAZMER_ODEJDYI___TSIFRYI_1'       => '',
        'KFSS_RAZMER_PODUSHKI'                  => '',
        'KFSS_RAZMER_PODUSHKI_1'                => '',
        'KFSS_RAZMER_POSTELNOGO_BELYA'          => '',
        'KFSS_RAZMER_POSTELNOGO_BELYA_1'        => '',
        'KFSS_RAZMER_TEKSTILYA'                 => '',
        'KFSS_RAZMER_TEKSTILYA_1'               => '',
        'KFSS_RAZMER_TRUSOV'                    => '',
        'KFSS_DIOPTRII'                         => '',
    ];

    public static $sizePropsForProductCard = [
        'KFSS_RASCHMER_KOROBA',
        'KFSS_RAZMER_BYUSGALTERA_1S',
        'KFSS_RAZMER_BYUSTGALTERA',
        'KFSS_RAZMER_BYUSTGALTERA_1',
//        'KFSS_COLOR',
//        'KFSS_COLOR_BX',
        'KFSS_KOLICHESTVO',
        'KFSS_KOLICHESTVO_LAMP',
        'KFSS_RAZMER_OBUVI_1',
        'KFSS_RAZMER_OBUVI_2',
        'KFSS_DLINA',
        'KFSS_DLINA_SM',
        'KFSS_RAZMER_KOLTSA',
        'KFSS_RAZMER_KOLTSA_1',
        'KFSS_DIAMETR',
        'KFSS_DIAMETR_SM',
        'KFSS_RAZMER_TEKSTILYA',
        'KFSS_RAZMER_TEKSTILYA_1',
        'KFSS_RAZMER_ODEJDYI___BUKVYI_1',
        'KFSS_RAZMER_PODUSHKI_1',
        'KFSS_RAZMER_ODEJDYI___TSIFRYI',
        'KFSS_RAZMER_ODEJDYI___TSIFRYI_1',
        'KFSS_EMKOST_BATAREI_1',
        'KFSS_OBYEM',
        'KFSS_OBYEM_1',
        'KFSS_DIOPTRII',
    ];

    public static function getPropertyProperName($propCode)
    {
        if (isset(self::$propertyProperNameMap[$propCode])){
            return self::$propertyProperNameMap[$propCode] ?: self::$defaultSizePropertyProperName;
        }
        return false;
    }

    public static function isSizeproperty($propertyId)
    {
        $arr = [
            'KFSS_ETALON___ODEJDA',
            'KFSS_RAZMER_OBUVI',
            'KFSS_RAZMER_KOLTSA'
        ];
        $model = ProductParamType::findOne($propertyId);
        if($model){
            if(in_array($model->code,$arr)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}