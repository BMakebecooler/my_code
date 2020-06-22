<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 02.06.17
 * Time: 14:16
 */

namespace common\lists;


class Bitrix
{

    /**
     * Получить пользователя битрикса по ИД
     * @param $userId
     * @return mixed
     */
    public static function getUserById($userId)
    {
        $sql = <<<SQL
        SELECT b_user.*, guids.guid
        FROM front2.b_user AS b_user
        LEFT JOIN front2.sands_guid_storage AS guids ON guids.LOCAL_ID = b_user.id AND ENTITY = 'USER'
        WHERE b_user.ID = :user_id
SQL;

        $user = \Yii::$app->db->createCommand($sql, [
            ':user_id' => $userId,
        ])->queryOne();

        return $user;
    }

    /**
     * Получить пользователя битрикса по логину или емайлу
     * @param $loginOrEmail
     * @return mixed
     */
    public static function getUserByLoginOrEmail($loginOrEmail)
    {
        $sql = <<<SQL
        SELECT b_user.*, guids.guid
        FROM front2.b_user AS b_user
        LEFT JOIN front2.sands_guid_storage AS guids ON guids.LOCAL_ID = b_user.id AND ENTITY = 'USER'
        WHERE b_user.LOGIN = :user_login_email OR b_user.EMAIL = :user_login_email
SQL;

        $user = \Yii::$app->db->createCommand($sql, [
            ':user_login_email' => $loginOrEmail,
        ])->queryOne();

        return $user;
    }

    /**
     * Получить пользователя битрикса по guid
     * @param $guid
     * @return mixed
     */
    public static function getUserByGuid($guid)
    {
        $sql = <<<SQL
        SELECT b_user.*
        FROM front2.b_user AS b_user
        INNER JOIN front2.sands_guid_storage AS guids ON guids.LOCAL_ID = b_user.id AND ENTITY = 'USER'
        WHERE guids.guid = :guid
SQL;

        $user = \Yii::$app->db->createCommand($sql, [
            ':guid' => $guid,
        ])->queryOne();

        return $user;
    }

    /**
     * Получить позиции заказа битрикса по guid заказа
     * @param $guid
     * @return mixed
     */
    public static function getOrderPositionsByOrderGuid($guid)
    {
        $sql = <<<SQL
        SELECT b_sale_basket.*, el.IBLOCK_ID
        FROM front2.b_sale_basket AS b_sale_basket
        INNER JOIN front2.sands_guid_storage AS guids ON guids.LOCAL_ID = b_sale_basket.order_id AND ENTITY = 'ORDER'
        INNER JOIN front2.b_iblock_element AS el ON el.ID = b_sale_basket.PRODUCT_ID
        WHERE guids.guid = :guid
SQL;

        $user = \Yii::$app->db->createCommand($sql, [
            ':guid' => $guid,
        ])->queryAll();

        return $user;
    }
}