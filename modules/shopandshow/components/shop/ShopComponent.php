<?php

namespace modules\shopandshow\components\shop;

use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopFuser;
use skeeks\cms\components\Cms;
use skeeks\cms\shop\components\ShopComponent AS SXShopComponent;

/**
 * Class ShopComponent
 * @property integer $favoritesCount
 * @property ShopFuser $shopFuser
 * @package modules\shopandshow\components\shop
 */
class ShopComponent extends SXShopComponent
{

    /**
     * @var ShopFuser
     */
    protected $_shopFuser;

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFavorites()
    {
        $query = ShopContentElement::find();
        $query->joinWith([
            'favorite',
        ]);
        $query->andWhere(['shop_fuser_favorites.active' => Cms::BOOL_Y]);
        $query->andWhere(['cms_content_element.active' => Cms::BOOL_Y]);

        ShopContentElement::catalogFilterQuery($query);

        return $query;
    }

    /**
     * @return int|string
     */
    public function getFavoritesCount()
    {
        return $this->getFavorites()->count();
    }

    /**
     * Если нет будет создан
     * @return ShopFuser
     */
    public function getShopFuser()
    {
        if ($this->_shopFuser instanceof ShopFuser) {
            return $this->_shopFuser;
        }

        //Если пользователь гость
        if (isset(\Yii::$app->user) && \Yii::$app->user && \Yii::$app->user->isGuest) {

            //Проверка сессии
            if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                $shopFuser = ShopFuser::find()->where(['id' => $fuserId])->one();
                //Поиск юзера
                if ($shopFuser) {
                    $this->_shopFuser = $shopFuser;
                }
            }

            if (!$this->_shopFuser) {
                $shopFuser = new ShopFuser();
                $shopFuser->save();

                \Yii::$app->getSession()->set($this->sessionFuserName, $shopFuser->id);
                $this->_shopFuser = $shopFuser;


            }
        } else {
            if (\Yii::$app instanceof \yii\console\Application) {
                return null;
            }

            $this->_shopFuser = ShopFuser::find()->where(['user_id' => \Yii::$app->user->identity->id])->one();

            //Если у авторизовнного пользоывателя уже есть пользователь корзины
            if ($this->_shopFuser) {
                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                    $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopFuser = ShopFuser::find()->where(['id' => $fuserId])->one();

                    /**
                     * @var $shopFuser ShopFuser
                     */
                    if ($shopFuser) {
                        $this->_shopFuser->addBaskets($shopFuser->shopBaskets);
                        $this->_shopFuser->addFavorites($shopFuser->shopFuserFavorites);
                        $shopFuser->delete();
                    }

                    //Эти данные в сессии больше не нужны
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                }
            } else {

                //Проверка сессии, а было ли чего то в корзине
                if (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) {
                    $fuserId = \Yii::$app->getSession()->get($this->sessionFuserName);
                    $shopFuser = ShopFuser::find()->where(['id' => $fuserId])->one();
                    //Поиск юзера
                    /**
                     * @var $shopFuser ShopFuser
                     */
                    if ($shopFuser) {
                        $shopFuser->user_id = \Yii::$app->user->identity->id;
                        $shopFuser->save();
                    }

                    $this->_shopFuser = $shopFuser;
                    \Yii::$app->getSession()->remove($this->sessionFuserName);
                } else {
                    $shopFuser = new ShopFuser([
                        'user_id' => \Yii::$app->user->identity->id
                    ]);

                    $shopFuser->save();
                    $this->_shopFuser = $shopFuser;
                }
            }
        }


        return $this->_shopFuser;
    }

    public function getShopFuserIdFromSession()
    {
        return (\Yii::$app->getSession()->offsetExists($this->sessionFuserName)) ? \Yii::$app->getSession()->get($this->sessionFuserName) : false;
    }

}