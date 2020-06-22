<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-06
 * Time: 13:23
 */

namespace common\models;

use modules\shopandshow\models\common\StorageFile;
use Yii;
use common\behaviors\SeoBehavior;
use common\helpers\ArrayHelper;
use common\models\query\CmsContentElementQuery;
use ignatenkovnikita\arh\interfaces\ModelHistoryInterface;
use yii\db\Expression;

class CmsContentElement extends generated\models\CmsContentElement implements ModelHistoryInterface
{
    public $h1;
    public $forceUpdateSeoFields = false;

    public function formatValue($attribute, $value)
    {
        return $value;
        // TODO: Implement formatValue() method.
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return array_merge([
            'seo' => [
                'class' => SeoBehavior::class,
                'titleAttribute' => function () {
                    try {
                        if (empty($this->name)) {
                            return null;
                        }
                        return "{$this->name} – официальный сайт телемагазина Shop&Show";

                    } catch (\Throwable $e) {
                        Yii::error($e->getTraceAsString(), __METHOD__);
                    }
                    return null;

                },
                'h1Attribute' => function () {
                    if (empty($this->name)) {
                        return $this->h1;
                    }
                    return $this->name;
                },
                'descriptionAttribute' => function () {

                    try {

                        if (empty($this->name)) {
                            return $this->meta_description;
                        }

                        $delivery = ['Оперативная', 'Быстрая'];
                        $range = ['Широкий', 'Огромный'];
                        $deliveryIndex = array_rand($delivery);
                        $rangeIndex = array_rand($range);


                        return "{$this->tree->name} – официальный сайт магазина на диване Shop&Show. 
                        ✔{$range[$rangeIndex]} ассортимент товаров ✔{$delivery[$deliveryIndex]} доставка по всей России ✔Регулярные акции и скидки 
                        ✔Гарантия на продукцию. Вежливые менеджеры круглосуточно окажут консультацию по телефону ☎ 8 (800) 301-60-10.";


                    } catch (\Throwable $e) {
                        Yii::error($e->getTraceAsString(), __METHOD__);
                    }
                    return null;

                },
                'slugAttribute' => 'code',
                'forceAttribute' => function() {
                    return $this->forceUpdateSeoFields;
                }
            ],
        ], $behaviors);
    }


    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['h1'], 'safe'];
        return $rules;
    }

    public static function find()
    {
        return new CmsContentElementQuery(get_called_class());
    }

    public function getTree()
    {
        return $this->hasOne(CmsTree::class, ['id' => 'tree_id']);
    }

    /**
     * @return string
     */
    public function getSeoTitle()
    {
        /*if ($this->isLot() || $this->isCard() || $this->isOffer()) {
            $product = Product::getLot($this->id);
            $name = empty($product->new_lot_name) ? $product->name : $product->new_lot_name;
            $lotNum = $product->new_lot_num;

            return "{$name} – лот {$lotNum} – купить по низкой цене в интернет-магазине Shop&Show";
        }*/
        return $this->getSeoValue('title');
    }

    /**
     * @return string
     */
    public function getSeoH1()
    {
        return ArrayHelper::getValue($this, 'seo.h1', $this->name);
    }

    /**
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->getSeoValue('meta_description');
    }

    /**
     * @return string
     */
    public function getOpenGraphDescription()
    {
        return $this->getSeoDescription() ?: $this->description_full;
    }

    /**
     * @return string
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_id']);
    }


    public static function getImages($id)
    {
        return StorageFile::getDb()->cache(function ($db) use ($id) {
            //Главное фото
            $subQuery = CmsContentElement::find()->select(['image_id AS storage_file_id', 'id AS content_element_id', new Expression("0 AS priority")])->where(['id' => $id]);
            //Доп фото
            $subQuery2 = CmsContentElementImage::find()->select(['storage_file_id', 'content_element_id', 'priority'])->where(['content_element_id' => $id]);

            //Объединяем что бы получить общий список фото для элемента
            $subQuery->union($subQuery2);

            return StorageFile::find()
                ->alias('files')
                ->select([
                    'files.*',
                    'cce_image.content_element_id',
                    'cce_image.priority',
                ])
                ->innerJoin('(' . $subQuery->createCommand()->getRawSql() . ') AS cce_image', "files.id = cce_image.storage_file_id")
                ->orderBy('cce_image.priority')
                ->groupBy('cce_image.storage_file_id')
                ->all();
        }, MIN_25);
    }
}