<?php

namespace common\seo;

use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopProduct;

class Product
{

    /**
     * @var ShopContentElement
     */
    protected $contentElement;
    protected $shopProduct;

    public function __construct(ShopContentElement $contentElement)
    {
        $this->contentElement = $contentElement;
        $this->shopProduct = ShopProduct::getInstanceByContentElement($contentElement);
    }

    /**
     * Заголовок товара
     * @return string
     */
    public function getTitle()
    {
        return sprintf('%s по цене %s рублей, Лот %s в телемагазине Shop & Show',
            $this->contentElement->getLotName(),
            $this->shopProduct->getBasePriceMoney(),
            $this->contentElement->relatedPropertiesModel->getAttribute('LOT_NUM')
        );
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return sprintf('%s  цена  лот видео смотреть купить интернет магазин продажа',
            $this->contentElement->getLotName()
        );
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return sprintf('Смотрите видео и приобретайте товар %s, %s в магазине на диване Shop & Show',
            mb_strtolower($this->contentElement->getLotName()),
            $this->contentElement->relatedPropertiesModel->getAttribute('LOT_NUM')
        );
    }


}