<?php

namespace modules\shopandshow\models\newEntities\common;

use common\helpers\Msg;
use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\components\Cms;
use skeeks\cms\components\storage\Cluster;
use skeeks\sx\File;
use yii\base\Model;

class CmsContentElementModel extends Model
{
    /** @var Cluster Хранилище картинок */
    protected $clusterId = 'element_images';

    const CONTENT_TYPE_KFSS_INFO = 'kffs-info';
    const CONTENT_TYPE_KFSS_INFO_CURRENCY = 'kffs-info-currency';
    const CONTENT_TYPE_KFSS_INFO_COLORS = 'kffs-info-colors';
    const CONTENT_TYPE_KFSS_INFO_SIZES = 'kffs-info-sizes';
    const CONTENT_TYPE_KFSS_INFO_PROPERTY = 'kffs-info-property';

    public $guid;
    public $name;
    public $code;
    public $description;
    public $model;
    public $active = true;
    public $weight;
    public $stop;
    public $prices;
    public $editUserId;
    public $bitrix_id;

    /**
     * @var CmsContentElement
     */
    protected $cmsContentElement;

    public $relatedPropertiesModel = [];

    /**
     * @var CmsContent
     */
    protected $cmsContent;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['weight', 'bitrix_id'], 'integer'],
            [['guid'], 'required'],
            [array_keys(get_object_vars($this)), 'safe'],
        ];
    }

    /**
     * @param $image
     * @return bool|File
     */
    protected function getFileImage($image)
    {
        try {
            if (!$image) {
                return false;
            }

            $image = preg_replace('#\\\+#', '/', $image);

            $vendorFilePath = \Yii::$app->params['storage']['kfssImagesPath'] . '/' . $image;

            $vendorFile = new File($vendorFilePath);

            if ($vendorFile->isExist() === false) {
                Job::dump('нет пикчи');
                Job::dump($vendorFilePath);
                return false;
            }

            return $vendorFile;

        } catch (\Exception $e) {
            Job::dump($e->getMessage());
        }

        return false;
    }

    /**
     * @param File $vendorFile
     * @param string|array $media
     *
     * @return bool | \skeeks\cms\models\StorageFile
     */
    protected function uploadFileImage(File $vendorFile, $media)
    {
        try {
            /** Копируем фаил чтобы не удалять у вендора (в нашем случае из папки оригиналов) */
            $tmpFile = new File('/tmp/'.md5(time().$vendorFile->getPath()).".".$vendorFile->getExtension());

            $vendorFile->copy($tmpFile);

            if (is_array($media)) {
                $storageParams = [
                    'name' => $media['MediaName'],
                    'original_name' => $media['MediaPath'],
                    'description_short' => $media['MediaNotes']
                ];
            }
            else {
                $storageParams = [
                    'name' => $this->cmsContentElement->name,
                    'original_name' => $media
                ];
            }

            $file = \Yii::$app->storage->upload($tmpFile, $storageParams,
                \Yii::$app->params['storage']['clusters'][$this->clusterId] // TODO: подумать как такой херни не плодить.
            );

            return $file;
        }
        catch (\Exception $e) {
            Job::dump($e->getMessage());
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function saveRelatedProperties()
    {
        if ($this->relatedPropertiesModel) {
            $this->cmsContentElement->relatedPropertiesModel->setAttributes($this->relatedPropertiesModel);

            if (!$this->cmsContentElement->relatedPropertiesModel->save()) {
                Job::dump($this->cmsContentElement->relatedPropertiesModel->getErrors());

                return false;
            }
        }

        return true;
    }

    /**
     * @param $guid
     * @param $content_id
     *
     * @return bool|CmsContent|CmsContentElement|\common\models\user\User|\modules\shopandshow\models\shop\ShopOrder|\yii\db\ActiveRecord
     */
    public static function getOrCreateElement($guid, $content_id = KFSS_PRODUCT_CONTENT_ID)
    {
        static $attempt = 0;

        if ($contentElement = Guids::getEntityByGuid($guid)) {
            return $contentElement;
        }

/*
        $attempt++;
        sleep(1);
        if ($attempt > 3) {
            Job::dump('product not found');
            return false;
        }

        return self::getOrCreateElement($guid, $content_id);
*/
        // могут возникать deadlocks, когда в параллельных очередях создаются такие же объекты

        $contentElement = new CmsContentElement([
            'content_id' => $content_id,
            'active' => Cms::BOOL_N,
            'name' => $guid
        ]);
        $contentElement->noGuidAutoGenerate = false;
        $contentElement->guid->setGuid($guid);
        try {
            if (!$contentElement->save(false)) {
                // found conflict
                if ($contentElement->getErrors('code') || $contentElement->getErrors('content_id') || $contentElement->getErrors('guid_id')) {
                    $result = self::resolveConflict($contentElement);
                    if ($result) {
                        return $result;
                    }
                }

                throw new \Exception(print_r($contentElement->getErrors(), true));
            }
            self::ensureShopProduct($contentElement->id);
        }
        catch (\Throwable $e) {
            sleep(1);
            $attempt++;
            if ($attempt > 3) {
                Job::dump($e->getMessage());
                Job::dump($e->getTraceAsString());
                return false;
            }

            // found conflict
            if ($e instanceof \yii\db\IntegrityException || $contentElement->getErrors('code') || $contentElement->getErrors('content_id') || $contentElement->getErrors('guid_id')) {
                self::resolveConflict($contentElement);
            }
            // когда прилетает пачка сообщений, бывает что гуид уже создался в другой очереди, поэтому пробуем еще раз
            return self::getOrCreateElement($guid, $content_id);

        }

        return $contentElement;
    }

    /**
     * из-за многопоточности может создаться одновременно несколько CmsContentElement, наводим порядок этим методом
     * @param $contentElement CmsContentElement
     * @return CmsContentElement
     */
    public static function resolveConflict($contentElement)
    {

        if ($guid = $contentElement->guid->getGuid()) {
            $result = Guids::getEntityByGuid($guid);
            if ($result) {
                return $result;
            }
        }

        return CmsContentElement::findOne(['code' => $contentElement->code, 'content_id' => [PRODUCT_CONTENT_ID, CARD_CONTENT_ID, OFFERS_CONTENT_ID, KFSS_PRODUCT_CONTENT_ID]]);
    }

    /**
     * @param int   $productId
     * @param array $attrs
     * @param bool   $useCache
     *
     * @return bool|ShopProduct
     */
    public static function ensureShopProduct($productId, $attrs = [], $useCache = false)
    {
        static $shopProducts = [];

        //Если можно использовать кеш, нет атрибутов и товар есть в кеше - вернем товар оттуда
        if ($useCache && !$attrs && isset($shopProducts[$productId])){
            return $shopProducts[$productId];
        }

        $shopProduct = ShopProduct::findOne($productId);

        if (!$shopProduct) {
            $shopProduct = new ShopProduct();
            $shopProduct->id = $productId;
            $shopProduct->quantity = 0;
            $shopProduct->quantity_reserved = 0;
        }

        if ($attrs) {
            $shopProduct->setAttributes($attrs, false);
        }

        if (!$shopProduct->save()) {
            Job::dump($shopProduct->getErrors());
            return false;
        }

        $shopProducts[$productId] = $shopProduct;

        return $shopProduct;
    }

    /**
     * @param \common\models\cmsContent\CmsContentElement|\skeeks\cms\models\CmsContentElement $product
     * @param bool $currentProduct - если надо начать пересчет с текущего элемента (т.е. уже передан parent)
     *
     * см.так же console/controllers/kfss/ItemsController.php :: updateShopProductQuantity(..)
     *
     * @throws \yii\db\Exception
     */
    protected function recalcQuantity($product, $currentProduct = false)
    {
        if ($product->parent_content_element_id || $currentProduct) {
            //ДЛЯ ПОДСЧЕТА В ТОВАР->КАРТОЧКА
            //Если родитель не база - суммируем все (это будут тоже не базы).
            //Если родитель база - то проверяем кол-во элементов, если ли только один (база) - берем число отсюда, если еще что есть - беремчисло из простых товаров
            //
            //ДЛЯ ПОДСЧЕТА В КАРТОЧКА->ЛОТ
            //Проверям кол-во карточек, если больше одной - берем обычное кол-во, если одна - берем базу.
            $queryProducts
                = "
                    UPDATE shop_product t1
                    INNER JOIN (
                        SELECT
                            sum(CASE WHEN ce.is_base!='Y' AND t.quantity > 0 THEN t.quantity ELSE 0 END) AS sum_quantity,
                            sum(CASE WHEN ce.is_base='Y' AND t.quantity > 0 THEN t.quantity ELSE 0 END) AS sum_quantity_base,
                            ce.parent_content_element_id AS element_id,
                            ce.content_id,
                            parent_element.is_base       AS parent_is_base,
                            COUNT(1) AS num
                        FROM cms_content_element ce, shop_product t, cms_content_element AS parent_element
                        WHERE
                            ce.content_id = :content_id
                            AND ce.tree_id IS NOT NULL
                            AND ce.active = 'Y'
                            AND ce.parent_content_element_id = :id
                            AND parent_element.id = ce.parent_content_element_id
                            AND t.id = ce.id
                            GROUP BY ce.parent_content_element_id
                    ) t2 ON t2.element_id=t1.id
                    SET t1.quantity = IF(
    t2.content_id = 10,
    (
      IF(
          t2.parent_is_base = 'Y' AND t2.num = 1,
          t2.sum_quantity_base,
          t2.sum_quantity
      )
    ),
    (
      IF(
          t2.num > 1,
          t2.sum_quantity,
          t2.sum_quantity_base
      )
    )
)
";
            $affected = \Yii::$app->db->createCommand($queryProducts, [
                ':id' => $currentProduct ? $product->id : $product->parent_content_element_id,
                ':content_id' => $product->content_id
            ])->execute();
            Job::dump("affected parent ".$affected);

            if ($affected) {
                $this->recalcQuantity($product->parentContentElement);
            }
        }
    }
}