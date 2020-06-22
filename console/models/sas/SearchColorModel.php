<?

namespace console\models\sas;

use common\models\cmsContent\CmsContentProperty;
use Exception;
use skeeks\cms\components\Cms;
use common\models\cmsContent\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsContentPropertyEnum;
use skeeks\cms\models\CmsTree;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopProduct;
use Yii;

/**
 * @property string column0  "ID"
 * @property string column1  "NAME"
 * @property string column2  "SORT"
 * @property string column3  "STYLE"
 *
 * Class SeachColorModel
 * @package console\models\sas
 */
class SearchColorModel extends ImportModel
{

    /**
     *
     * @var CmsContent
     */
    private static $cmsContent = null;

//    const CMS_CONTENT_DICT_NAME = 'COLOR_SEARCH';
    const CMS_CONTENT_DICT_NAME = 'KFSS_COLOR';
    const CMS_CONTENT_DICT_PROPERTYS = [
        'CSS_STYLE' => [
            'name' => 'CSS_STYLE',
            'type' => 'S',
            'list_type' => 'L',
            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeString',
            'is_required' => Cms::BOOL_N,
            'data_column' => 'column3'
        ],
//        'BITRIX_ID' => [
//            'name' => 'ID Битрикса',
//            'type' => 'N',
//            'list_type' => 'L',
//            'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber',
//            'is_required' => Cms::BOOL_N,
//            'data_column' => 'column0'
//        ],
    ];

    public function init()
    {
        parent::init();

        if (!self::$cmsContent) {
            $cmsContent = CmsContent::find()
                ->andWhere('code = :code', [':code' => self::CMS_CONTENT_DICT_NAME])
                ->limit(1)
                ->one();

            if (!$cmsContent) {

                $cmsContent = new CmsContent();
                $cmsContent->code = self::CMS_CONTENT_DICT_NAME;
                $cmsContent->name = 'Цвет (поиск)';

                $cmsContent->active = Cms::BOOL_Y;
                $cmsContent->content_type = 'info';
                $cmsContent->is_allow_change_tree = Cms::BOOL_Y;
                $cmsContent->index_for_search = Cms::BOOL_Y;

                if ( !$cmsContent->save() )
                    throw new \skeeks\cms\Exception(json_encode([ 'row' => $cmsContent->attributes, 'errors' => $cmsContent->getErrors()]));

            }

            foreach (self::CMS_CONTENT_DICT_PROPERTYS as $code => $property ) {

                $cmsContentProperty = CmsContentProperty::find()
                    ->andWhere(['content_id' => $cmsContent->id])
                    ->andWhere(['code' => $code])
                    ->limit(1)
                    ->one();

                if (!$cmsContentProperty) {
                    $cmsContentProperty = new CmsContentProperty();
                    $cmsContentProperty->content_id = $cmsContent->id;
                    $cmsContentProperty->code = $code;
                    $cmsContentProperty->name = $property['name'];
                    $cmsContentProperty->property_type = $property['type'];
                    $cmsContentProperty->list_type = $property['list_type'];
                    $cmsContentProperty->is_required = $property['is_required'];
                    $cmsContentProperty->component = $property['component'];

                    $cmsContentProperty->save();
                }

            }

            self::$cmsContent = $cmsContent;
        }
    }

    public function addColor()
    {
        $cmsContentElement = ShopCmsContentElement::find()
//            ->innerJoinWith('relatedElementProperties map')
//            ->joinWith('relatedElementProperties.property property')
            ->andWhere(['cms_content_element.content_id' => self::$cmsContent->id])
//            ->andWhere(['property.code' => self::CMS_CONTENT_DICT_NAME])
            ->andWhere(['bitrix_id' => $this->column0])
            ->limit(1)
            ->one();

        if (!$cmsContentElement) {
            $cmsContentElement = self::$cmsContent->createElement();
        } else {
//            var_dump('Значение уже есть');
            return false;
        }

        $cmsContentElement->name = $this->column1;
        $cmsContentElement->code = $this->column0;
        $cmsContentElement->priority = $this->column2;
        $cmsContentElement->bitrix_id = $this->column0;

        foreach (self::CMS_CONTENT_DICT_PROPERTYS as $code => $property)
            $cmsContentElement->relatedPropertiesModel->setAttribute($code, $this->{$property['data_column']});

        if ($cmsContentElement->save()) {
            if (!$cmsContentElement->relatedPropertiesModel->save()) {
                var_dump($cmsContentElement->relatedPropertiesModel->getErrors());
                die();
            }
        } else {
            var_dump($cmsContentElement->getErrors());
            var_dump($cmsContentElement->code);
//            die();
        }

        return true;
    }
}