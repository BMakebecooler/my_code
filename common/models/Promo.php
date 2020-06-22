<?php


namespace common\models;

use common\behaviors\SeoBehavior;
use common\helpers\ArrayHelper;
use common\helpers\Dates;
use common\helpers\Image;
use common\helpers\Promo as PromoHelper;
use common\models\query\PromoQuery;
use common\seo\SeoFields;
use skeeks\cms\components\storage\ClusterLocal;
use skeeks\cms\controllers\ElfinderFullController;
use yii\helpers\Url;
use yii\web\UploadedFile;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\db\Exception;

class Promo extends \common\models\generated\models\Promo implements SeoFields
{

//    public $in_actions;
    public $attachments;
    public $attachment;

    const NAME_ATTACHMENTS = 'promo.attachments';
    const NAME_ATTACHMENT = 'promo.attachment';

    public $forceUpdateSeoFields = false;
    public static $onAirRelatedPromoNum = 100;
    public static $onAirRelatedPromoTreeLvlMax = 3;
    public static $onAirRelatedPromoTreeLvlMin = 2;
    public static $randomAction = false;

    /**
     * @inheritdoc
     */
    public function rulesCustom()
    {
        return [
            [['active', 'segment_id', 'created_at', 'updated_at', 'have_image', 'updated_by', 'tree_id_onair',
                'created_by', 'count_views', 'count_views_day', 'rating', 'start_timestamp', 'end_timestamp',
                'in_menu', 'in_main', 'in_actions', 'priority'], 'integer'],
            [['name', 'link'], 'required'],
            [['description', 'meta_description'], 'string'],
            [['name', 'link', 'meta_title', 'meta_keywords', 'image', 'image_banner', 'url_link', 'promocode'], 'string', 'max' => 255],
            [['link'], 'unique'],
        ];
    }


    public function rules()
    {
        return ArrayHelper::merge($this->rulesCustom(), [
            [['attachments', 'attachment'], 'safe'],
        ]);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $return = array_merge([
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
                'slugAttribute' => 'link',
                'ensureUnique' => true
            ],
            'seo' => [
                'class' => SeoBehavior::class,
                'titleAttribute' => function () {
                    try {
                        if (!empty($this->meta_title)) {
                            $meta_title = $this->meta_title;
                        } else {
                            $meta_title = $this->name;
                        }

                        if (empty($meta_title)) {
                            return null;
                        }
                        return $meta_title;
                    } catch (\Throwable $e) {
                        Yii::error($e->getTraceAsString(), __METHOD__);
                    }
                    return null;
                },
                'h1Attribute' => function () {
                    if (empty($this->h1)) {
                        return "{$this->name} – официальный сайт телемагазина Shop&Show";
                    }
                    return $this->h1;
                },
                'descriptionAttribute' => function () {
                    try {
                        if (!empty($this->meta_description)) {
                            $meta_description = $this->meta_description;
                        } else {
                            $meta_description = $this->name;
                        }
                        return $meta_description;
                    } catch (\Throwable $e) {
                        Yii::error($e->getTraceAsString(), __METHOD__);
                    }
                    return null;
                },
                'slugAttribute' => 'link',
                'forceAttribute' => function () {
                    return $this->forceUpdateSeoFields;
                },
            ],
        ], $behaviors);

        //TODO добавить комппонгент yii2-file-kit
//        $return = ArrayHelper::merge($return, [
//            [
//                'class' => \ignatenkovnikita\imagemanager\behaviors\UploadBehavior::className(),
//                'attribute' => 'attachments',
//                'multiple' => true,
//                'tag' => self::NAME_ATTACHMENTS,
//                'pathAttribute' => 'path',
//                'uploadRelation' => 'promoAttachments',
//                'baseUrlAttribute' => 'base_url',
//                'orderAttribute' => 'order',
//                'typeAttribute' => 'type',
//                'sizeAttribute' => 'size',
//                'nameAttribute' => 'name',
//            ],
//            [
//                'class' => \ignatenkovnikita\imagemanager\behaviors\UploadBehavior::className(),
//                'attribute' => 'attachment',
//                'multiple' => false,
//                'tag' => self::NAME_ATTACHMENT,
//                'uploadRelation' => 'promoAttachment',
//                'pathAttribute' => 'path',
//                'baseUrlAttribute' => 'base_url',
//                'orderAttribute' => 'order',
//                'typeAttribute' => 'type',
//                'sizeAttribute' => 'size',
//                'nameAttribute' => 'name',
//            ],
//        ]);

        return $return;
    }

//    /**
//     * @return \yii\db\ActiveQuery
//     * @throws \Exception
//     */
//    public function getPromoAttachments()
//    {
//        return $this->hasMany(ImageManager::class, ['owner_id' => 'id'])->andWhere(['tag' => self::NAME_ATTACHMENTS]);
//    }
//
//    /**
//     * @return \yii\db\ActiveQuery
//     * @throws \Exception
//     */
//    public function getPromoAttachment()
//    {
//        return $this->hasOne(ImageManager::class, ['owner_id' => 'id'])->andWhere(['tag' => self::NAME_ATTACHMENT]);
//    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Активно',
            'name' => 'Название',
            'link' => 'Ссылка',
            'description' => 'Описание',
            'meta_description' => 'Мета описание',
            'meta_title' => 'Мета тайтл',
            'meta_keywords' => 'Мета кейвордс',
            'segment_id' => 'Сегмент',
            'image' => 'Изображение',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'tree_id_onair' => 'Раздел для Сегодня в эфире (выбирать только 2 уровень!',
            'have_image' => 'Есть основная картинка на странице промо',
            'have_image_banner' => 'Есть баннер',
            'count_views' => 'Количество просмотров за все время',
            'image_banner' => 'Изображение баннер',
            'count_views_day' => 'Количество просмотров за сутки',
            'url_link' => 'Ссылка',
            'rating' => 'Рейтинг',
            'in_menu' => 'Показывать в Меню',
            'promocode' => 'Промокод',
            'start_timestamp' => 'Время начала активности',
            'end_timestamp' => 'Время конца активности',
            'in_main' => 'Показывать на Главной',
            'in_actions' => 'Показывать в Акциях',
            'tree_ids' => 'Привязанные категории первого уровня для горизонтального меню',
            'priority' => 'Приоритет',
        ];
    }

    /**
     *  Получает заголовок для сео
     */
    public function getSeoTitle()
    {
        return $this->meta_title ? $this->meta_title : $this->name;
//        return $this->getSeoValue('title');
    }

    /**
     * Получает описанеие для сео
     */
    public function getSeoDescription()
    {
        return $this->meta_description;
//        return $this->getSeoValue('meta_description');
    }

    /**
     * Получает описанеие для сео
     */
    public function getOpenGraphDescription()
    {
        return $this->meta_description;
//        return $this->getSeoValue('meta_description');
    }

    /**
     * todo:: Данный мета тег считается устаревшим
     * @deprecated
     */
    public function getSeoKeywords()
    {
        return $this->getSeoValue('meta_keywords');
    }

    /**
     * @param $attribute
     * @param $defaultValue
     * @return mixed
     */
    public function getSeoValue($attribute, $defaultValue = 'name')
    {
        return $this->seo && $this->seo->{$attribute} ? $this->seo->{$attribute} : $this->{$defaultValue};
    }

    public function getUrl()
    {
        return Url::to(['promo/view', 'slug' => $this->link]);
    }

    public function getImageBanner()
    {
//        return 'http://yii2-starter-kit.example/uploads/all/'.$this->image_banner;
        return 'https://shopandshow.ru/uploads/all/' . $this->image_banner;
    }

    public function getImage()
    {
        return 'https://shopandshow.ru/uploads/all/' . $this->image;
    }

    public function delete()
    {
        PromoHelper::deletePromoTrees($this->id);
        return parent::delete(); // TODO: Change the autogenerated stub
    }

    public function deleteImageBanner()
    {
        if ($this->image_banner) {
            $image_banner = $this->getImageBanner();
            Image::clearImageCache($image_banner);
        }
        $rootBasePath = Yii::getAlias("@frontend/web/uploads/all");
        @unlink($rootBasePath . DIRECTORY_SEPARATOR . $this->image_banner);

    }

    public function deleteImage()
    {
        $rootBasePath = Yii::getAlias("@frontend/web/uploads/all");

//        $file = basename($this->image);
//        $dir = str_replace($file,'',$this->image);

        @unlink($rootBasePath . DIRECTORY_SEPARATOR . $this->image);
    }

    /**
     * @inheritdoc
     * @return PromoQuery
     */
    public static function find()
    {
        return new PromoQuery(get_called_class());
    }

    public function upload()
    {
        $rootBasePath = Yii::getAlias("@frontend/web/uploads/all");
        $image = UploadedFile::getInstance($this, 'image');

        if ($image) {
            try {

                $this->deleteImage();

                $newName = md5(microtime() . rand(0, 100));
                $cluster = new ClusterLocal();
                $dir = $cluster->getClusterDir($newName);

                mkdir($rootBasePath . DIRECTORY_SEPARATOR . $dir, 0777, true);
                $image->saveAs($rootBasePath . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $newName . '.' . $image->getExtension());

                return $dir . DIRECTORY_SEPARATOR . $newName . '.' . $image->getExtension();

            } catch (\Exception $e) {
                throw new Exception("Не удалось загрузить файл: " . $e->getMessage());
            }
        }
        return false;
    }

    public function uploadBanner()
    {
        $rootBasePath = Yii::getAlias("@frontend/web/uploads/all");
        $image = UploadedFile::getInstance($this, 'image_banner');

        if ($image) {
            try {

                $this->deleteImageBanner();

                $newName = md5(microtime() . rand(0, 100));
                $cluster = new ClusterLocal();
                $dir = $cluster->getClusterDir($newName);

                mkdir($rootBasePath . DIRECTORY_SEPARATOR . $dir, 0777, true);
                $image->saveAs($rootBasePath . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $newName . '.' . $image->getExtension());

                return $dir . DIRECTORY_SEPARATOR . $newName . '.' . $image->getExtension();

            } catch (\Exception $e) {
                throw new Exception("Не удалось загрузить файл: " . $e->getMessage());
            }
        }
        return false;
    }

    //TODO реализовать тут сортировку по популярности(количество просмотров за текущий месяц)
    public static function findPromosActionQuery()
    {
        return self::find()
            ->onlyActive()
            ->onlyInActions()
            ->onlyHaveImageBanner()
            ->onlyActiveTime()
            ->addOrderBy(['priority' => SORT_ASC])
//            ->addOrderBy(['rating' => SORT_ASC])
                
//            ->addOrderBy(['rating' => SORT_DESC])
            ->addOrderBy(['count_views_day' => SORT_DESC])
            ->addOrderBy(['count_views' => SORT_DESC]);
    }

    public static function findPromosQuery()
    {
        return self::find()
            ->onlyActive()
            ->onlyHaveImageBanner()
            ->onlyActiveTime();
    }

    public static function findPromosMainQuery()
    {
        return self::find()
            ->onlyActive()
            ->onlyInMain()
            ->onlyHaveImageBanner()
            ->onlyActiveTime()
            ->addOrderBy(['priority' => SORT_ASC])
//            ->addOrderBy(['rating' => SORT_ASC])
//            ->addOrderBy(['rating' => SORT_DESC])
            ->addOrderBy(['count_views_day' => SORT_DESC])
            ->addOrderBy(['count_views' => SORT_DESC]);
    }

    public static function findPromoActionMenu($treeIds = [], $mainTreeId = null)
    {
        $contoller = \Yii::$app->controller->id;

        $bannerWithoutCategory = self::find()
            ->onlyActive()
            ->onlyHaveImageBanner()
            ->onlyInMenu()
            ->onlyActiveTime()
            ->andFilterWhere(['is', 'tree_id_onair', new \yii\db\Expression('null')])
            ->orderBy(new \yii\db\Expression('rand()'))
            ->one();

        if ($mainTreeId) {
            $bannerWithCategory = self::find()
                ->onlyActive()
                ->onlyHaveImageBanner()
                ->onlyInMenu()
                ->onlyActiveTime()
                ->leftJoin(PromoTree::tableName(),
                    PromoTree::tableName() . '.promo_id = ' . Promo::tableName() . '.id and ' . PromoTree::tableName() . '.tree_id = ' . $mainTreeId)
                ->andWhere(['not', [PromoTree::tableName() . '.id' => null]])
                ->orderBy(new \yii\db\Expression('rand()'))
                ->one();

            if ($bannerWithCategory) {
                return $bannerWithCategory;
            }
        }

        if (count($treeIds)) {
            $bannerWithCategory = self::find()
                ->onlyActive()
                ->onlyHaveImageBanner()
                ->onlyInMenu()
                ->onlyActiveTime()
                ->andFilterWhere(['IN', 'tree_id_onair', $treeIds])
                ->orderBy(new \yii\db\Expression('rand()'))
                ->one();

            if ($bannerWithCategory) {
                return $bannerWithCategory;
            } else {
                return $bannerWithoutCategory;
            }

        } else {

            if ($contoller == 'category') {

                $slug = \Yii::$app->request->get('slug');
                $slug = \common\helpers\Url::prepareSlug($slug);

                if (!$slug || $slug == \common\helpers\Url::$catalogSlug) {
                    $slug = \common\helpers\Url::$catalogSlug;
                } else {
                    $slug = \common\helpers\Url::$catalogSlug . '/' . $slug;
                }

                $treeNode = Tree::find()->where([
                    "dir" => $slug,
                    "site_code" => \Yii::$app->cms->site->code,
                ])->one();

                if ($treeNode) {

                    $bannerWithCategory = self::find()
                        ->onlyActive()
                        ->onlyHaveImageBanner()
                        ->onlyInMenu()
                        ->onlyActiveTime()
                        ->andWhere(['tree_id_onair' => $treeNode->id])
                        ->orderBy(new \yii\db\Expression('rand()'))
                        ->one();

                    if ($bannerWithCategory) {
                        if (self::$randomAction) {
                            $return = [];
                            $return[] = $bannerWithCategory;
                            $return[] = $bannerWithoutCategory;
                            return $return[rand(0, 1)];
                        } else {
                            return $bannerWithCategory;
                        }
                    } else {
                        return $bannerWithoutCategory;
                    }

                } else {
                    return $bannerWithoutCategory;
                }
            } else {
                return $bannerWithoutCategory;
            }
        }
    }

    public static function getOnAirRelated()
    {
        $promos = [];

        //Товар сейчас в эфире или более ранние
        $onAirProducts = \common\models\SsMediaplanAirDayProductTime::find()
            ->where(['<', 'begin_datetime', time()])
            ->andWhere(['>=', 'begin_datetime', Dates::getHourBegin(time() - 3600)]) //выбор и предыдущего часа
            ->orderBy(['begin_datetime' => SORT_DESC]) //Сортировка в обратном порядке что бы если есть товар сейчас в эфире то выбрался именно он
            ->all();

        //Перебираем товары и для каждого находим рубрику 3 и 2 уровня и записываем ее в список интересующих нас рубрик
        $treeIds = [];
        foreach ($onAirProducts as $onAirProduct) {
            /** @var Product $product */
            $product = Product::getFromCache($onAirProduct->lot_id);

            if ($product && $product->tree_id) {
                $tree = \skeeks\cms\models\Tree::findOne($product->tree_id);

                if ($tree->level >= self::$onAirRelatedPromoTreeLvlMin && $tree->level <= self::$onAirRelatedPromoTreeLvlMax) {
                    //могут быть товары связанные с одинаковыми рубриками, дубли нам не нужны
                    if (!in_array($tree->id, $treeIds)) {
                        $treeIds[] = $tree->id;
                    }
                }

                if ($tree->level > self::$onAirRelatedPromoTreeLvlMin) {
                    $treeParents = $tree->parents;
                    $treePromoLvl = array_filter($treeParents, function ($tree) {
                        return $tree->level <= self::$onAirRelatedPromoTreeLvlMax && $tree->level >= self::$onAirRelatedPromoTreeLvlMin;
                    });

                    if ($treePromoLvl) {
                        foreach ($treePromoLvl as $treeItem) {
//                            echo "[{$product->id} | {$tree->id} | ".date('Y-m-d H:i:s', $onAirProduct->begin_datetime)."] {$product->name}<br>";
                            //могут быть товары связанные с одинаковыми рубриками, дубли нам не нужны
                            if (!in_array($treeItem->id, $treeIds)) {
                                $treeIds[] = $treeItem->id;
                            }
                        }
                    }
                }
            }
        }

        if ($treeIds) {
            foreach ($treeIds as $treeId) {
                //ищем подборки для текущего раздела
                $promosFound = self::find()
                    ->where(['tree_id_onair' => $treeId])
                    ->onlyActive()
                    ->onlyActiveTime()
                    ->limit(self::$onAirRelatedPromoNum)
                    ->all();

                if ($promosFound) {
                    $promos = ArrayHelper::merge($promos, $promosFound);
                }

                //Мы набрали необходимое кол-во акций
                if (count($promos) >= self::$onAirRelatedPromoNum) {
                    break;
                }
            }
        }

        return array_slice($promos, 0, self::$onAirRelatedPromoNum);
    }

    public function addCountViews($count = null, $count_day = null)
    {
        if ($count) {
            $this->count_views += $count;
        } else {
            $this->count_views++;
        }
        if ($count_day) {
            $this->count_views_day += $count;
        } else {
            $this->count_views_day++;
        }
        if (!$this->promocode)
            $this->promocode = '';
        $this->save();
    }

    public function getSegment()
    {
        if ($this->segment_id) {
            return Segment::findOne($this->segment_id);
        } else {
            return null;
        }
    }

    public function getLink()
    {
        return '/promo/page/' . $this->link;
    }
}