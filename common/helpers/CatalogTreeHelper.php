<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.06.2016
 */
namespace common\helpers;

use skeeks\cms\helpers\CmsTreeHelper;
use yii\helpers\ArrayHelper;

/**
 * @property string $viewType
 *
 * Class CatalogTreeHelper
 * @package common\helpers
 */
class CatalogTreeHelper extends CmsTreeHelper
{
    const VIEW_TREE = 'tree';
    const VIEW_PRODUCT = 'product';

    public function getViewType()
    {
        if (!$value = ArrayHelper::getValue($this->model->relatedPropertiesModel, 'viewType')) {
            return self::VIEW_TREE;
        }
        $property = $this->model->relatedPropertiesModel->getRelatedProperty('viewType');
        $enum = $property->getEnums()->andWhere(['id' => $value])->one();
        return $enum->code;
    }

    public static function checkActive($category)
    {
        $thisSort = \Yii::$app->request->get('sort');

        $specialParts = [
            'novinki' => 'new',
            'hityi' => 'popular',
            'hot' => 'popular',
            'maksimalnyie-skidki' => 'sale',
            'maksimalnaya-skidka' => 'sale',
            'posledniy-razmer' => 'quantity'
        ];

        foreach ($specialParts as $part => $sort){
            if($part == $category['code'] && $thisSort == $sort){
                $category['active'] = true;
            }
        }

        return $category;
    }
}