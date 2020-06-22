<?php

    namespace modules\shopandshow\models\shares;


    use common\helpers\ArrayHelper;
    use common\helpers\Url;
    use common\helpers\User;
    use common\lists\Contents;
    use common\lists\TreeList;
    use common\models\cmsContent\CmsContentElement;
    use common\models\Product;
    use common\thumbnails\BaseThumbnail;
    use modules\shopandshow\behaviors\files\HasStorageFile;
    use skeeks\cms\helpers\UrlHelper;
    use skeeks\cms\models\StorageFile;
    use Yii;
    use yii\behaviors\TimestampBehavior;
    use yii\data\BaseDataProvider;
    use yii\db\AfterSaveEvent;
    use yii\db\Expression;

    /**
     * This is the model class for table "ss_banners".
     *
     * @property integer $id
     * @property string $created_at
     * @property string $updated_at
     * @property integer $begin_datetime
     * @property integer $end_datetime
     * @property integer $bitrix_sands_schedule_id
     * @property integer $bitrix_banner_id
     * @property integer $bitrix_info_block_id
     * @property integer $image_id
     * @property integer $promo_type
     * @property integer $count_page_views
     * @property integer $count_click
     * @property integer $count_click_email
     * @property string $banner_type
     * @property string $promo_type_code
     * @property string $name
     * @property string $code
     * @property string $active
     * @property string $promocode
     * @property string $url
     * @property integer $bitrix_product_id
     * @property integer $image_product_id
     * @property string $description
     * @property integer $schedule_tree_id
     * @property integer $share_schedule_id
     * @property integer $is_hidden_catalog
     *
     * @property SsShareProduct[] $shareProducts
     * @property integer $productsCount
     * @property CmsContentElement $product
     * @property StorageFile $image
     * @property SsShareSchedule $shareSchedule
     */
    class SsShare extends \yii\db\ActiveRecord
    {
        /** @var SsShareProduct[] $relatedProducts */
        public $relatedProducts;
        public $updateFile;
        public $updateProducts = false;

        // связанные с cms_content_element атрибуты
        public $cce_image_id;
        public $cce_description;

        const CATALOG_SECTION_ACTION = 'CATALOG_SECTION_ACTION';

        const BANNER_TYPE_MAIN_SMALL_EFIR = 'MAIN_SMALL_EFIR';
        const BANNER_TYPE_MAIN_SMALL_EFIR_2 = 'MAIN_SMALL_EFIR_2';

        const BANNER_TYPE_MAIN_WIDE_1 = 'MAIN_WIDE_1';
        const BANNER_TYPE_MAIN_WIDE_2 = 'MAIN_WIDE_2';

        const BANNER_TYPE_MAIN_WIDE_MOBILE = 'MAIN_WIDE_MOBILE';

        const BANNER_TYPE_SANDS_PROMO_CTS = 'SANDS_PROMO_CTS';
        const BANNER_TYPE_SANDS_PROMO_CTS2 = 'SANDS_PROMO_CTS2';

        const BANNER_TYPE_CTS = 'MAIN_CTS';
        const BANNER_TYPE_CTS_MOBILE = 'MAIN_CTS_MOBILE';

        const BANNER_TYPE_MAIN_SITE_SALE_1 = 'MAIN_SITE_SALE_1';
        const BANNER_TYPE_MAIN_SITE_SALE_2 = 'MAIN_SITE_SALE_2';
        const BANNER_TYPE_MAIN_SITE_SALE_3 = 'MAIN_SITE_SALE_3';

        const BANNER_TYPE_ACTION_BANNER = 'ACTION_BANNER';

        const BANNER_TYPE_MAIN_WIDE_ROSTELECOM = 'ROSTELECOM';

        const BANNER_TYPE_EAR = 'ear';
        const BANNER_TYPE_POPUP = 'popup';
        const BANNER_TYPE_LABEL = 'label';

        const BANNER_PREVIEW_KEY = 'banner_date';


        const BID_PARAM = 'bid';

        //Описания в бд так себе, так что запишем отдельно более понятно
        public static $bannersLabels = [
            self::BANNER_TYPE_MAIN_WIDE_1 => 'Главный баннер',
            self::BANNER_TYPE_MAIN_WIDE_MOBILE => 'Главный баннер мобильная версия',
            self::BANNER_TYPE_MAIN_WIDE_2 => 'Главный баннер',
            self::BANNER_TYPE_CTS => 'ЦТС баннер',
            self::BANNER_TYPE_CTS_MOBILE => 'ЦТС баннер моб. версия',
            self::BANNER_TYPE_MAIN_SITE_SALE_1 => 'Моб.версия',
            self::BANNER_TYPE_MAIN_SITE_SALE_2 => 'Моб.версия',
            self::BANNER_TYPE_MAIN_SITE_SALE_3 => 'Моб.версия',
        ];

        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return 'ss_shares';
        }

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['created_at', 'updated_at', 'relatedProducts', 'updateProducts', 'updateFile', 'cce_image_id', 'cce_description'], 'safe'],
                [['begin_datetime', 'end_datetime', 'banner_type'], 'required'],
                [['begin_datetime', 'end_datetime', 'bitrix_sands_schedule_id', 'bitrix_banner_id',
                    'bitrix_info_block_id', 'image_id', 'promo_type', 'count_page_views', 'count_click', 'count_click_email', 'bitrix_product_id', 'image_product_id',
                    'share_schedule_id', 'is_hidden_catalog', 'schedule_tree_id'
                ], 'integer'],
                [['name', 'code', 'promocode', 'active', 'banner_type', 'promo_type_code',], 'string', 'max' => 256],
                [['description'], 'string', 'max' => 255],
                [['url'], 'string', 'max' => 1056],
                [['bitrix_product_id'], 'checkTypes', 'skipOnEmpty' => false],
            ];
        }

        /**
         * Дополнительная валидация для типов баннеров
         * @param $attribute
         * @param $params
         */
        public function checkTypes($attribute, $params)
        {
            switch ($this->banner_type) {
                case self::BANNER_TYPE_CTS:
//            case self::BANNER_TYPE_SANDS_PROMO_CTS:
//            case self::BANNER_TYPE_SANDS_PROMO_CTS2:
                    if (!($this->bitrix_product_id || $this->url)) {
                        $this->addError($attribute, 'Для баннера с типом ЦТС необходимо заполнить "Ид лота" либо указать ссылку');
                    }
                    break;
            }
        }

        /**
         * @return array
         */
        public function behaviors()
        {
            return array_merge(parent::behaviors(), [
                HasStorageFile::className() =>
                    [
                        'class' => HasStorageFile::className(),
                        'fields' => ['image_id'],
                        'isUpdatedFile' => true,
                    ],
                TimestampBehavior::className() =>
                    [
                        'class' => TimestampBehavior::className(),
                    ],
            ]);
        }

        /**
         * @inheritdoc
         */
        public function attributeLabels()
        {
            return [
                'id' => 'ID',
                'created_at' => 'Создан',
                'updated_at' => 'Изменен',
                'begin_datetime' => 'Дата начала',
                'end_datetime' => 'Дата окончания',
                'bitrix_sands_schedule_id' => 'bitrix_sands_schedule_id',
                'bitrix_banner_id' => 'bitrix_banner_id',
                'bitrix_info_block_id' => 'bitrix_info_block_id',
                'image_id' => 'Изображение',
                'banner_type' => 'Тип баннера',
                'name' => 'Название',
                'description' => 'Описание',
                'code' => 'Код акции',
                'promocode' => 'Промокод',
                'promo_type_code' => 'Тип цены',
                'url' => 'Ссылка',
                'active' => 'Активность',
                'promo_type' => 'promo_type',
                'count_page_views' => 'Кол-во просмотров страницы с баннером',
                'count_click' => 'Кол-во кликов по баннеру',
                'count_click_email' => 'Кол-во кликов по баннеру с рассылки',
                'bitrix_product_id' => 'ИД лота (Bitrix_Id)',
                'image_product_id' => 'ИД товара с изображения',
                'relatedProducts' => 'Товары в акции',
                'updateFile' => 'Импорт из файла .csv',

                'cce_image_id' => 'Баннер для промостраницы',
                'cce_description' => 'Описание под баннером',
                'share_schedule_id' => 'Блок баннерной сетки',
                'schedule_tree_id' => 'Категория эфира',
                'is_hidden_catalog' => 'Скрывать товары в каталоге',

            ];
        }

        public function init()
        {
            parent::init();

            $this->on(self::EVENT_AFTER_UPDATE, [$this, '_afterUpdate']);
            $this->on(self::EVENT_AFTER_INSERT, [$this, '_afterUpdate']);
            $this->on(self::EVENT_BEFORE_UPDATE, [$this, '_beforeUpdate']);
            $this->on(self::EVENT_BEFORE_INSERT, [$this, '_beforeUpdate']);
        }

        /**
         * @return \yii\db\ActiveQuery
         */
        public function getImage()
        {
            return $this->hasOne(StorageFile::className(), ['id' => 'image_id']);
        }

        /**
         * @return \yii\db\ActiveQuery
         */
        public function getShareProducts()
        {
            return $this->hasMany(SsShareProduct::className(), ['banner_id' => 'id'])->orderBy(SsShareProduct::tableName() . '.priority');
        }

        /**
         * @return int|string
         */
        public function getProductsCount()
        {
            return $this->hasMany(SsShareProduct::className(), ['banner_id' => 'id'])->count();
        }

        /**
         * @return \yii\db\ActiveQuery
         */
        public function getProduct()
        {
            return $this->hasOne(CmsContentElement::className(), ['bitrix_id' => 'bitrix_product_id'])
                ->andWhere(['content_id' => [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]]);
        }

        /**
         * @return \yii\db\ActiveQuery
         */
        public function getShareSchedule()
        {
            return $this->hasOne(SsShareSchedule::className(), ['id' => 'share_schedule_id']);
        }

        private function getShareUrl()
        {
            $url = trim($this->url);

            if ($url) {
                $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . sprintf('%s=%s', self::BID_PARAM, $this->id);
            }

            return $url;
        }

        protected $shareUrl;

        /**
         * @return null|string
         */
        public function getUrl()
        {

//        return $this->getShareUrl();

            if ($this->shareUrl) {
                return $this->shareUrl;
            }

            switch ($this->banner_type) {
                case self::BANNER_TYPE_MAIN_SMALL_EFIR :
                    return $this->shareUrl = $this->getUrlMainSmallEfir();

                case self::BANNER_TYPE_MAIN_WIDE_1 :
                case self::BANNER_TYPE_MAIN_WIDE_MOBILE :
                case self::BANNER_TYPE_SANDS_PROMO_CTS2:
                case self::BANNER_TYPE_MAIN_SITE_SALE_1 :
                case self::BANNER_TYPE_MAIN_SITE_SALE_2 :
                case self::BANNER_TYPE_MAIN_SITE_SALE_3 :
                case self::BANNER_TYPE_MAIN_WIDE_2 :
                case self::CATALOG_SECTION_ACTION :
                case self::BANNER_TYPE_MAIN_SMALL_EFIR_2 :
                case self::BANNER_TYPE_SANDS_PROMO_CTS:
                case self::BANNER_TYPE_ACTION_BANNER:
                    return $this->shareUrl = $this->getUrlMainWide();
                    break;
                case self::BANNER_TYPE_CTS_MOBILE :
                case self::BANNER_TYPE_CTS :
                    return $this->shareUrl = $this->getUrlCts();
                    break;
            }

            if (substr($this->banner_type, 0, 7) === 'BANNER_') {
                return $this->shareUrl = $this->getUrlMainWide();
            }

            return $this->shareUrl = $this->getShareUrl();
        }

        /**
         * Получить ссылку на товар ЦТС
         * @return string
         */
        protected function getUrlCts()
        {
            if ($url = $this->getShareUrl()) {
                return $url;
            } elseif ($this->bitrix_product_id) {

                $product = Product::find()->where(['bitrix_id' => $this->bitrix_product_id])->one();
                if ($product){
                    return $product->url;
                }

                //скекс тему убираем
                if (false) {
                    $cmsContentElement = Contents::getContentElementByBitrixId($this->bitrix_product_id, [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);
                    if ($cmsContentElement) {
                        return UrlHelper::construct(['/cms/content-element/view', 'model' => $cmsContentElement, self::BID_PARAM => $this->id]);
                    }
                }

            }

            return '';
        }

        /**
         * @return string
         */
        protected function getUrlMainWide()
        {
            $shareUrl = $this->getShareUrl();

            if ($this->code && !$shareUrl) {
                $content = Contents::getContentByCode('promo'); //127
                $cmsContentElement = Contents::getContentElementByCode($this->code, $content);

                if ($cmsContentElement) {
                    return UrlHelper::construct(['/cms/content-element/view', 'model' => $cmsContentElement, self::BID_PARAM => $this->id]);
                }
            } elseif ($this->bitrix_product_id) {
                return $this->getUrlCts();
            }

            return $shareUrl;

            /*
                    $code = null;
                    $queryStr = parse_url($this->getShareUrl(), PHP_URL_QUERY);
                    parse_str($queryStr, $queryParams);

                    if (isset($queryParams['action_code'])) {
                        $code = $queryParams['action_code'];
                    } elseif ($this->code) {
                        $code = $this->code;
                    }

                    if ($code) {

                        $content = Contents::getContentByCode('promo'); //127
                        $cmsContentElement = Contents::getContentElementByCode($code, $content);

                        if ($cmsContentElement) {
                            return UrlHelper::construct(['/cms/content-element/view', 'model' => $cmsContentElement]);
                        }
                    }

                    return $this->getShareUrl();
            */
        }

        /**
         * Получить ссылку для эффирных баннеров
         * @return null|string
         */
        protected function getUrlMainSmallEfir()
        {
            if (!$this->getShareUrl()) {
                return null;
            }

            // /promo/onair/?SECTION_ID=26

            $queryStr = parse_url($this->getShareUrl(), PHP_URL_QUERY);
            parse_str($queryStr, $queryParams);

            if (isset($queryParams['SECTION_ID'])) {

                $category = TreeList::getIdTreeByBitrixId($queryParams['SECTION_ID']);

                return UrlHelper::construct(['/onair',
                    'category' => $category,
                    '#' => 'schedule'
                ]);
            }

            return $this->getShareUrl();
        }

        /**
         * @return string
         */
        protected function getUrlFromActionCode()
        {
            $queryStr = parse_url($this->getShareUrl(), PHP_URL_QUERY);
            parse_str($queryStr, $queryParams);

            if (isset($queryParams['action_code'])) {

                $content = Contents::getContentByCode('promo');
                $cmsContentElement = Contents::getContentElementByCode($queryParams['action_code'], $content->id);

                if ($cmsContentElement) {
                    return UrlHelper::construct(['/cms/content-element/view', 'model' => $cmsContentElement]);
                }
            }

            return $this->getShareUrl();
        }

        public function isCts()
        {
            return $this->banner_type == self::BANNER_TYPE_CTS;
        }

        public function isCtsMobile()
        {
            return $this->banner_type == self::BANNER_TYPE_CTS_MOBILE;
        }

        public function isWithText()
        {
            return !empty($this->bitrix_product_id) && strpos($this->banner_type, 'BANNER_') === 0;
        }

        /**
         * @inheritdoc
         */
        public function afterFind()
        {
            parent::afterFind();

            if ($this->code) {
                $content = Contents::getContentByCode('promo'); //127
                $cmsContentElement = Contents::getContentElementByCode($this->code, $content->id);

                if ($cmsContentElement) {
                    $this->cce_image_id = $cmsContentElement->image_id;
                    $this->cce_description = $cmsContentElement->description_full;
                }
            }
        }

        /**
         * Сохраняет список продуктов через веб форму
         * @param AfterSaveEvent $event
         */
        public function _afterUpdate(AfterSaveEvent $event)
        {
            if ($this->code) {
                if (!$cmsContentElement = $this->createContentElement()) {
                    echo 'Не удалось создать страницу для акции';
                    return;
                }
            }

            // чистим кеш картинок
            if ($this->bitrix_product_id && ($this->isAttributeChanged('bitrix_product_id') || $this->isAttributeChanged('image_id'))) {
                $this->image->cluster->deleteTmpDir($this->image->cluster_file);
            }

            // если метод вызывался в форме, а не в каком-нибудь консольном контроллере
            if ($this->updateProducts) {
                $this->updateShareProducts();
            }

        }

        /**
         * Ищет подходящий блок, куда можно вставить баннер
         * @param $event
         */
        public function _beforeUpdate($event)
        {
            if (!$this->share_schedule_id) {
                $availSchedules = SsShareSchedule::getAvailSchedulesForBanner($this);
                if ($availSchedules) {
                    $schedule = array_shift($availSchedules);
                    $this->share_schedule_id = $schedule->id;
                }
            } elseif ($this->share_schedule_id < 0) {
                $this->share_schedule_id = null;
            }
        }

        /**
         * Создает / редактирует страницу для акций
         * @return bool
         */
        protected function createContentElement()
        {
            $content = Contents::getContentByCode('promo');
            $cmsContentElement = Contents::getContentElementByCode($this->code, $content->id);

            if (!$cmsContentElement) {
                $cmsContentElement = new CmsContentElement();
                $cmsContentElement->content_id = $content->id;
                $cmsContentElement->tree_id = $content->default_tree_id;
            }

            $cmsContentElement->name = $this->name;
            $cmsContentElement->code = $this->code;

            $cmsContentElement->description_full = $this->cce_description;
            $cmsContentElement->image_id = $this->cce_image_id;

            if (!$cmsContentElement->save(false)) {
                var_dump($cmsContentElement->getErrors());
                return false;
            }
            return true;
        }


        /**
         * Обновляет список связанных продуктов
         */
        protected function updateShareProducts()
        {
            // загрузка из файла
            $this->updateFile = \yii\web\UploadedFile::getInstance($this, 'updateFile');
            if ($this->updateFile) {
                echo '<div class="alert alert-info">' . $this->importFromFile() . '</div>';;
                return;
            }

            SsShareProduct::updateAll([
                'is_hidden_catalog' => $this->is_hidden_catalog,
            ], [
                'banner_id' => $this->id
            ]);

            return;
            /*
            // загрузка из формы
            $currentProducts = $this->getShareProducts()->indexBy('product_id')->asArray()->all();
            if (!$this->relatedProducts) $this->relatedProducts = [];
            $this->relatedProducts = array_combine($this->relatedProducts, $this->relatedProducts);

            $addProductsList = array_diff_key($this->relatedProducts, $currentProducts);
            $delProductsList = array_diff_key($currentProducts, $this->relatedProducts);

            if ($delProductsList) {
                if (!SsShareProduct::deleteAll(['banner_id' => $this->id, 'product_id' => array_keys($delProductsList)])) {
                    echo 'Не удалось удалить выбранные товары из акции';
                };
            }

            $priority = array_reduce($currentProducts, function ($carry, $product) {
                return max($carry, $product['priority']);
            }, 0);

            foreach ($addProductsList as $product_id) {
                $priority++;
                $ssShareProduct = new SsShareProduct();
                $ssShareProduct->banner_id = $this->id;
                $ssShareProduct->product_id = $product_id;
                $ssShareProduct->bitrix_id = Contents::getContentElementById($product_id)->bitrix_id;
                $ssShareProduct->priority = $priority;

                if (!$ssShareProduct->save()) {
                    var_dump($ssShareProduct->getErrors());
                }
            }
            */
        }

        /**
         * Загружает связанные товары из загруженного файла
         */
        protected function importFromFile()
        {
            $data = @file($this->updateFile->tempName);
            if (empty($data)) {
                $this->addError('updateFile', 'Не удалось распознать файл');
                return $this->getFirstError('updateFile');
            }

            // данные из файла
            $result = [];
            foreach ($data as $row) {
                if (empty(trim($row))) {
                    continue;
                }

                $priority = 1;
                $items = preg_split('/[;,\t]/', $row);

                if (count($items) > 1 && !empty(trim($items[1]))) {
                    list($lot, $priority) = $items;
                } else {
                    $lot = trim($items[0]);
                }

                /*           if (!preg_match('/^[\d\-\s]+$/', $lot)) {
                                $this->addError('updateFile', 'Некорректный номер лота: ' . $lot . "'$row'");
                //                continue;
                                return $this->getFirstError('updateFile');
                            }*/

                $bitrixId = ltrim(str_replace(['[', ']', '-', ' '], '', $lot), '0');
                $result[$bitrixId] = trim($priority);
            }

            // соответствие bitrix_id -> id
            /*        $cmsContentElementIdmap = \common\lists\Contents::getIdsByBitrixIds(array_keys($result));
                    if (sizeof($cmsContentElementIdmap) != sizeof($result)) {
                        $this->addError('updateFile', 'Не найдены лоты: ' . join(', ', array_diff(array_keys($result), array_keys($cmsContentElementIdmap))));
                        return $this->getFirstError('updateFile');
                    }*/

            if (($res = SsShareProduct::deleteAll(['banner_id' => $this->id])) === false) {
                var_dump($res);
                $this->addError('updateFile', 'Не удалось удалить старые данные');
                return $this->getFirstError('updateFile');
            }

            foreach ($result as $bitrixId => $priority) {
                $product = Contents::getContentElementByBitrixId($bitrixId, [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);

                if (!$product) {
                    continue; //Экспресс правда что бы не отваливался весь импорт из-за не найденного товара
                }

                $ssShareProduct = new SsShareProduct();
                $ssShareProduct->banner_id = $this->id;
                $ssShareProduct->product_id = $product->id;
                $ssShareProduct->bitrix_id = $bitrixId;
                $ssShareProduct->priority = $priority;
                $ssShareProduct->is_hidden_catalog = $this->is_hidden_catalog;

                if (!$ssShareProduct->save()) {
                    var_dump($ssShareProduct->getErrors());
                }
            }

            return 'Данные из файла загружены';
        }

        /**
         * @inheritdoc
         * @return SsShareQuery the active query used by this AR class.
         */
        public static function find()
        {
            return new SsShareQuery(get_called_class());
        }

        /**
         * Признак просмотра баннеров за определенную дату
         * @return bool
         */
        public static function isBannerDate()
        {
            return (bool)\Yii::$app->request->get(self::BANNER_PREVIEW_KEY);
        }

        /**
         * Глобальный метод для определения даты баннеров
         * @return int
         */
        public static function getDate()
        {
            if ($bannerDate = \Yii::$app->request->get(self::BANNER_PREVIEW_KEY)) {
                return strtotime($bannerDate) ?: time();
            }

            return time();
        }


        /**
         * Получить путь до картинки
         * @return mixed
         */
        public function getImageSrc()
        {
            if (!$this->image_id || empty($this->image)) {
                return null;
            }


            if ($this->isCts()) {
                return \common\thumbnails\BannerCts::getShareImageWithText($this, []);
            } elseif ($this->isCtsMobile()) {
                return \common\thumbnails\BannerCtsMobile::getShareImageWithText($this, []);
            } elseif ($this->isWithText()) {
                return \common\thumbnails\BannerText::getShareImageWithText($this, []);
            }

            return sprintf('%s?%s=%d', $this->image->src, BaseThumbnail::NO_CACHE_PARAM, $this->updated_at);
        }

        public function getDataForExportCsv(BaseDataProvider $dataProvider)
        {
            $csvHeaders = ["id", "Дата начала", "Дата окончания", "Тип баннера", "Название", "Код акции", "Активность", "Ссылка", "ИД лота (Bitrix id)",
                "Кол-во кликов по баннеру", "Кол-во кликов по баннеру с рассылки", "Оформили товаров (шт.)", "Оформили товаров (руб.)", "Добавили в корзину"];

            $result = join(';', ArrayHelper::arrayToString($csvHeaders)) . PHP_EOL;
            $result = iconv('UTF8', 'CP1251', $result);

            /** @var SsShare $model */
            foreach ($dataProvider->models as $model) {
                $sql = <<<SQL
                        SELECT count_order_product, summ_order, count_add_basket
                        FROM `ss_shares_selling` AS t

                        LEFT JOIN ( 
                            SELECT share_id, count(sell.id) AS count_order_product, ROUND(SUM(spp.price)) AS summ_order 
                            FROM ss_shares_selling AS sell
                            INNER JOIN ss_shop_product_prices AS spp ON spp.product_id = sell.product_id
                            WHERE sell.status = :status_order AND sell.share_id = :share_id
                        ) AS sell ON sell.share_id = t.share_id

                        LEFT JOIN ( 
                            SELECT sell_basket.share_id, count(sell_basket.id) AS count_add_basket
                            FROM ss_shares_selling AS sell_basket
                            WHERE sell_basket.status = :status_basket AND sell_basket.share_id = :share_id
                        ) AS sell_basket ON sell_basket.share_id = t.share_id

                        WHERE t.share_id = :share_id
                        GROUP BY t.share_id
SQL;

                $data = \Yii::$app->db->createCommand($sql, [
                    ':share_id' => $model->id,
                    ':status_order' => \modules\shopandshow\models\shares\SsShareSeller::STATUS_ORDER,
                    ':status_basket' => \modules\shopandshow\models\shares\SsShareSeller::STATUS_ADD_PRODUCT_BASKET,
                ])->queryOne();

                $csvRow = [
                    $model->id,
                    \Yii::$app->formatter->asDatetime($model->begin_datetime, 'php:Y-m-d H:i:s'),
                    \Yii::$app->formatter->asDatetime($model->end_datetime, 'php:Y-m-d H:i:s'),
                    $model->banner_type,
                    iconv('UTF8', 'CP1251', $model->name),
                    $model->code,
                    $model->active,
                    $model->url,
                    $model->bitrix_product_id,
                    $model->count_click,
                    $model->count_click_email,
                    $data['count_order_product'] ?? 0,
                    $data['summ_order'] ?? 0,
                    $data['count_add_basket'] ?? 0

                ];
                $result .= join(';', ArrayHelper::arrayToString($csvRow)) . PHP_EOL;
            }

            //$result = iconv('UTF8', 'CP1251', $result);
            return $result;
        }

        public static function getBannerTypeLabel($bannerType)
        {
            return self::$bannersLabels[$bannerType] ?? '';
        }

        /**
         * Описание акции
         * @param string $description
         * @return string
         */
        public function getDescription($description = 'Каждый день мы представляем один товар по суперцене')
        {
            return ($this->description) ? $this->description : $description;
        }

        /**
         * Название акции
         * @param string $label
         * @return string
         */
        public function getLabel($label = 'Цена только сегодня')
        {
            return ($this->name) ? $this->name : $label;
        }

    }
