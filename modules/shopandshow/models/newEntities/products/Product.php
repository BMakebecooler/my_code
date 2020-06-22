<?php

namespace modules\shopandshow\models\newEntities\products;

use common\helpers\Strings;
use common\models\cmsContent\CmsContentElement;
use common\models\generated\models\ProductTreeNode;
use common\models\ProductProperty;
use console\controllers\queues\jobs\Job;
use console\jobs\UpdatePriceCardJob;
use console\jobs\UpdatePriceJob;
use console\jobs\UpdatePriceLotJob;
use console\jobs\UpdateSeoJob;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use skeeks\cms\components\Cms;
use Yii;

class Product extends CmsContentElementModel
{
    public $parent_guid;
    public $node_guid;
    public $type;
    public $is_base;
    public $kfss_id;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'Глобальный идентификатор',
            'parent_guid' => 'Глобальный идентификатор родителя',
            'node_guid' => 'Глобальный идентификатор рубрикатора',
            'active' => 'Признак активности лота',
            'type' => 'Тип продукта (лот/модификация)',
            'is_base' => 'Признак фейковой модификации',
            'kfss_id' => 'Локальный ID в KFSS'
        ];
    }

    public function setCmsContentElement(CmsContentElement $contentElement)
    {
        $this->cmsContentElement = $contentElement;

        $this->guid = $contentElement->guid->getGuid();
    }

    /**
     * Возвращает тип контента для указанного типа объекта
     *
     * @return int
     */
    public function getContentId()
    {
        static $typeMap = [
            'LOT' => PRODUCT_CONTENT_ID,
            'CARD' => CARD_CONTENT_ID,
            'MOD' => OFFERS_CONTENT_ID,
        ];

        return $typeMap[$this->type] ?? PRODUCT_CONTENT_ID;
    }

    public function getParentContentId()
    {
        static $parentMap = [
            OFFERS_CONTENT_ID => CARD_CONTENT_ID,
            CARD_CONTENT_ID => PRODUCT_CONTENT_ID,
            PRODUCT_CONTENT_ID => null,
        ];

        return $parentMap[$this->getContentId()];
    }

    /**
     * @return bool
     */
    public function addData()
    {
        $this->cmsContentElement = $this->cmsContentElement ?: new CmsContentElement();
        $isNewRecord = $this->cmsContentElement->isNewRecord;

        $productNameParsResult = [];

        if ($this->getContentId() == PRODUCT_CONTENT_ID) {
            //$productNameParsResult = $this->parsProductName();
            $productNameParsResult = Strings::parsProductName($this->name);
        }

        $this->cmsContentElement->noGuidAutoGenerate = false;
        $this->cmsContentElement->content_id = $this->getContentId();
        $this->cmsContentElement->name = !empty($productNameParsResult['NAME']) ? $productNameParsResult['NAME'] : $this->name;
        $this->cmsContentElement->active = ($this->active) ? Cms::BOOL_Y : Cms::BOOL_N;
        $this->cmsContentElement->guid->setGuid($this->guid);
        $this->cmsContentElement->kfss_id = $this->kfss_id;
        $this->cmsContentElement->code = !empty($productNameParsResult) ? $productNameParsResult['NUM'] : $this->guid;
        $this->cmsContentElement->bitrix_id = !empty($productNameParsResult) ? $productNameParsResult['ID'] : null;

        //* Рубрикатор *//

        if ($this->node_guid){
            if ($node = Guids::getEntityByGuid($this->node_guid)){
                Job::dump("Set node: [{$node->id}] {$node->name}");
                //TODO Раскоментировать когда на постоянку будем писать как текущий раздел дерева
                $this->cmsContentElement->tree_id = $node->id;
                $productTreeNode = ProductTreeNode::find()->where(['element_id' => $this->cmsContentElement->id])->one();

                if (!$productTreeNode){
                    $productTreeNode = new ProductTreeNode();
                    $productTreeNode->element_id = $this->cmsContentElement->id;
                    $productTreeNode->content_id = $this->cmsContentElement->content_id;
                    $productTreeNode->tree_id = 0;
                }

                $productTreeNode->node_id = $node->id;

                if (!$productTreeNode->save()){
                    Job::dump("ErrorTreeNode: " . var_export($productTreeNode->getErrors(), true));
                }

            }else{
                Job::dump("Can't find node for guid '{$this->node_guid}'");
            }
        }

        //* /Рубрикатор *//

        //set parent for modifications
        if ($this->getContentId() != PRODUCT_CONTENT_ID) {
            /** @var CmsContentElement $parentProduct */
            $parentProduct = $this->getOrCreateElement($this->parent_guid, $this->getParentContentId());
            if (!$parentProduct) {
                Job::dump(' failed get parent product, content_id=' . $this->getContentId());

                return false;
            }

            if ($this->is_base) {
                $this->cmsContentElement->is_base = Cms::BOOL_Y;
                if ($this->getContentId() == OFFERS_CONTENT_ID) {
                    $parentProduct->is_base = Cms::BOOL_Y;
                }
            }else{
                $this->cmsContentElement->is_base = Cms::BOOL_N;
            }

            if (!$parentProduct->isNewRecord && $parentProduct->content_id != $this->getParentContentId()) {
                $parentProduct->content_id = $this->getParentContentId();
            }

            if (!$parentProduct->save()) {
                Job::dump(' in parent product');
                Job::dump($parentProduct->getErrors());
                Job::dump($parentProduct->getAttributes());

                return false;
            }

            $this->cmsContentElement->parent_content_element_id = $parentProduct->id;
        }

        $saveResult = $this->cmsContentElement->save();
        if (!$saveResult) {
            // found conflict
            if ($this->cmsContentElement->getErrors('code')) {
                Job::dump("CodeConflict. Search Resolve...");
                $resolveResult = self::resolveConflict($this->cmsContentElement);
                //Проверка на то что нашелся не тот же самый товар
                if(!empty($resolveResult) && $resolveResult->id != $this->cmsContentElement->id) {
                    Job::dump("CodeConflict. Resolve found, elementId='{$resolveResult->id}'");
                    //Тупой момент, подменяем товар, но все изменения на него не переносим и по факту просто пересохраняем без изменений
                    //TODO Если это работает, то хотя бы перенести обновляния в найденный товар
                    $this->cmsContentElement = $resolveResult;
                }else{
                    Job::dump("CodeConflict. Resolve NOT found. Try resolveV2");

                    //Если у нас лот и есть конфликт по коду - ищем блокирующий элемент по этому самому коду, проверяя наличие у него потомков
                    //Если потомков нет - то это левый, непонятный лот-дубль, меняем его код, сохраняем и пробуем снова сохранить нормальный товар
                    if ($this->cmsContentElement->isLot() && !empty($productNameParsResult['NUM'])){
                        $productClone = \common\models\Product::find()
                            ->onlyLot()
                            ->where([
                                'code' => $productNameParsResult['NUM']
                            ])
                            ->one();

                        if ($productClone){
                            //клон найден, проверяем наличие потомков
                            $productCloneHasChilds = \common\models\Product::find()->byParent($productClone->id)->exists();

                            Job::dump("CodeConflict. Clone found (hasChilds=".($productCloneHasChilds ? 'Y':'N')."), elementId='{$productClone->id}'");

                            if (!$productCloneHasChilds) {
                                $productClone->code = $productClone->code . '_DOUBLE_TROUBLE';
                                //Все время ругается что обязательный
                                $productClone->meta_title = $productNameParsResult['NAME'];
                                if (!$productClone->save()) {
                                    Job::dump("CloneSaveErr: " . var_export($productClone->getErrors(), true));
                                } else {
                                    Job::dump("CloneSaveDone");
                                }
                            }

                        }else{
                            Job::dump("CodeConflict. Clone NOT found");
                        }
                    }
                }
                $saveResult = $this->cmsContentElement->save();
            }
        }

        if (!$saveResult) {
            Job::dump($this->cmsContentElement->getErrors());
            Job::dump($this->cmsContentElement->getAttributes());
            if (isset($parentProduct)) {
                if ($parentProduct instanceof CmsContentElement) {
                    Job::dump($parentProduct->getAttributes());
                } else {
                    Job::dump($parentProduct);
                }
            }
            return false;
        }


        /**
         * Устанавливаем типы продукта
         */
        /*$contentId = $this->getContentId();

        switch ($contentId) {
            case PRODUCT_CONTENT_ID :
            default:
                $productType = ShopProduct::TYPE_SIMPLE;
                break;
            case  CARD_CONTENT_ID :
                $productType = ShopProduct::TYPE_CARD;
                break;
            case  OFFERS_CONTENT_ID :
                $productType = ShopProduct::TYPE_OFFERS;
                break;
        }*/

        if (!$shopProduct = self::ensureShopProduct($this->cmsContentElement->id, [/*, 'product_type' => $productType*/])) {
            Job::dump(' failed to create shop product');

            return false;
        }

        // пересчет остатков при смене родителя
        if (!$isNewRecord && $this->cmsContentElement->isAttributeChanged('parent_content_element_id')) {
            $this->recalcQuantity($this->cmsContentElement);

            $oldParentId = $this->cmsContentElement->getOldAttribute('parent_content_element_id');
            $this->recalcQuantity(\common\lists\Contents::getContentElementById($oldParentId), true);
        }

        // установка номера лота
        if ($this->getContentId() == PRODUCT_CONTENT_ID) {
            // берем номер лота из строки вида "[lotNum] lotName"
            if (!empty($productNameParsResult['NUM']) && !empty($productNameParsResult['NAME'])) {
                ProductProperty::savePropByCode($this->cmsContentElement->id, 'LOT_NUM',$productNameParsResult['NUM']);
                ProductProperty::savePropByCode($this->cmsContentElement->id, 'LOT_NAME',$productNameParsResult['NAME']);
            }
        }

        //Пересчет цен в связи с возможно изменившейся активностью элемента
        switch ($this->cmsContentElement->content_id){
            case \common\models\Product::LOT:
                $updatePriceJobObject = new UpdatePriceLotJob(['id' => $this->cmsContentElement->id]);
                break;
            case \common\models\Product::CARD:
                $updatePriceJobObject = new UpdatePriceCardJob(['id' => $this->cmsContentElement->id]);
                break;
            case \common\models\Product::MOD:
                $updatePriceJobObject = new UpdatePriceJob(['id' => $this->cmsContentElement->id]);
                break;
        }

        if (!empty($updatePriceJobObject)){
            Yii::$app->queue->push($updatePriceJobObject);
        }else{
            \Yii::error("ProductUpdate. pushPriceJob err! productId={$this->cmsContentElement->id} | contentId={$this->cmsContentElement->content_id}", __METHOD__);
        }

        //Перегенерация СЕО (только для лота)
        if ($this->cmsContentElement->isLot()){
            Job::dump(" Add SEO update job.");
            Yii::$app->queueProduct->push(new UpdateSeoJob([
                'id' => $this->cmsContentElement->id,
            ]));
        }

        return true;
    }

    protected function parsProductName(){
        $result = [];
        preg_match('/^\[([\d\-]+)\]\s*(.+)\s*\(0?0?(\d+)\)$/', trim($this->name), $match);
        $result['NUM'] = trim($match[1]);
        $result['NAME'] = trim($match[2]);
        $result['ID'] = trim($match[3]);
        return $result;
    }

    /**
     * @param \common\models\cmsContent\CmsContentElement|\skeeks\cms\models\CmsContentElement $product
     *
     * @throws \yii\db\Exception
     */
    protected function recalcChildren($product)
    {
        if ($product->parent_content_element_id) {
            $sql
                = <<<SQL
UPDATE cms_content_element AS cce, (
    SELECT parent_content_element_id, COUNT(*) AS cnt 
    FROM cms_content_element
    WHERE parent_content_element_id = :parent_id
    GROUP BY parent_content_element_id
) AS child 
  SET cce.count_children = child.cnt 
WHERE cce.id = :parent_id 
  AND cce.id = child.parent_content_element_id ;
SQL;

            $affected = \Yii::$app->db->createCommand($sql, [':parent_id' => $product->parent_content_element_id])->execute();
            Job::dump("affected childs ".$affected);

            if ($affected) {
                $this->recalcChildren($product->parentContentElement);
            }
        }
    }
}