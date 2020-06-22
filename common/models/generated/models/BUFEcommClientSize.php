<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_EcommClientSize".
 *
 * @property integer $CLIENT_ID Client  ID
 * @property integer $PHONE_MOBILE_NUM Phone  Mobile  Num
 * @property string $xTop X Top
 * @property string $xBottom X Bottom
 * @property string $xCompl X Compl
 * @property string $xShoe X Shoe
 * @property string $xChain X Chain
 * @property string $xRing X Ring
*/
class BUFEcommClientSize extends \common\ActiveRecord
{
    private $called_class_namespace;

    public function __construct()
    {
        $this->called_class_namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
        parent::__construct();
    }

                                
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'BUF_EcommClientSize';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['PHONE_MOBILE_NUM'], 'integer'],
            [['xTop', 'xBottom', 'xCompl', 'xShoe', 'xChain', 'xRing'], 'string', 'max' => 50],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'CLIENT_ID' => 'Client  ID',
            'PHONE_MOBILE_NUM' => 'Phone  Mobile  Num',
            'xTop' => 'X Top',
            'xBottom' => 'X Bottom',
            'xCompl' => 'X Compl',
            'xShoe' => 'X Shoe',
            'xChain' => 'X Chain',
            'xRing' => 'X Ring',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\BUFEcommClientSizeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BUFEcommClientSizeQuery(get_called_class());
    }
}
