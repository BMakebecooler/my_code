<?php
namespace modules\shopandshow\models\newEntities\products;

use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use skeeks\cms\components\Cms;

class LinkList extends CmsContentElementModel
{
    const MAP = ['62E002700E5A053AE0538201090A2908' => 'PLUS_BUY'];

    public $links = [];

    protected $properties = [];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'Глобальный идентификатор',
        ];
    }

    public function setCmsContentElement(CmsContentElement $contentElement)
    {
        $this->cmsContentElement = $contentElement;

        $this->setAttributes([
            'guid' => $contentElement->guid->getGuid(),
        ]);
    }

    public function setLinksList(array $linksList = [])
    {
        $this->links = $linksList;
    }

    /**
     * @return bool
     */
    public function addData()
    {
        foreach ($this->links as $link) {
            Job::dump('LotGuid: '.$link['LotGuid']);
            Job::dump('LnkGuid: '.$link['LnkGuid']);

            if (!array_key_exists($link['LnkGuid'], self::MAP)) {
                Job::dump(' Link guid not supported');
                continue;
            }

            $this->properties[self::MAP[$link['LnkGuid']]][] = $link['LotGuid'];
        }

        return $this->saveProperties();
    }

    /**
     * @return bool
     */
    protected function saveProperties()
    {
        foreach ($this->properties as $propertyCode => $productGuids) {

            /** @var CmsContentProperty $property */
            $property = CmsContentProperty::find()->andWhere('code =:code AND content_id = :content_id', [
                ':code' => $propertyCode,
                ':content_id' => PRODUCT_CONTENT_ID, // todo если появятся связи для модификаций, надо будет допилить
            ])->one();

            \Yii::$app->db->createCommand("DELETE FROM cms_content_element_property WHERE property_id = :property_id AND element_id = :element_id", [
                ':property_id' => $property->id,
                ':element_id' => $this->cmsContentElement->id,
            ])->execute();

            $productsForSql = join(',', \common\helpers\ArrayHelper::arrayToString($productGuids));

            $query = <<<SQL
    INSERT INTO cms_content_element_property (property_id, element_id, value, value_num, value_enum)
    SELECT
        :property_id,
        :element_id,
        ce.id,
        ce.id,
        ce.id
    FROM cms_content_element ce
    INNER JOIN ss_guids AS guid ON guid.id = ce.guid_id AND guid.guid IN($productsForSql)
SQL;

            \Yii::$app->db->createCommand($query, [
                ':element_id' => $this->cmsContentElement->id,
                ':property_id' => $property->id,
            ])->execute();
        }

        return true;
    }
}