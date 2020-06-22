<?php
/**
 * Наличие свойств в связанных таблицах
 */

namespace modules\shopandshow\models\behaviors\property;

use modules\shopandshow\models\relatedProperties\models\RelatedPropertiesModel;
use skeeks\cms\models\behaviors\HasRelatedProperties as SXHasRelatedProperties;


/**
 * Class HasRelatedProperties
 * @package skeeks\cms\models\behaviors
 */
class HasRelatedProperties extends SXHasRelatedProperties
{

    /**
     * @var RelatedPropertiesModel
     */
    public $_relatedAdminPropertiesModel = null;


    /**
     * @return RelatedPropertiesModel
     */
    public function createRelatedAdminPropertiesModel()
    {
        return new RelatedPropertiesModel([], [
            'relatedElementModel' => $this->owner,
            'relatedPropertiesName' => 'adminRelatedProperties',
        ]);
    }

    /**
     * @return RelatedPropertiesModel
     */
    public function getRelatedAdminPropertiesModel()
    {
        if ($this->_relatedAdminPropertiesModel === null)
        {
            $this->_relatedAdminPropertiesModel = $this->createRelatedAdminPropertiesModel();
        }

        return $this->_relatedAdminPropertiesModel;
    }
}