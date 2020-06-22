<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_ECommN1234".
 *
 * @property string $n1 N1
 * @property string $n2 N2
 * @property string $n3 N3
 * @property string $n4 N4
 * @property string $g1 G1
 * @property string $g2 G2
 * @property string $g3 G3
 * @property string $g4 G4
 * @property integer $rub Rub
*/
class BUFECommN1234 extends \common\ActiveRecord
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
        return 'BUF_ECommN1234';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['rub'], 'integer'],
            [['n1', 'n2', 'n3', 'n4', 'g1', 'g2', 'g3', 'g4'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'n1' => 'N1',
            'n2' => 'N2',
            'n3' => 'N3',
            'n4' => 'N4',
            'g1' => 'G1',
            'g2' => 'G2',
            'g3' => 'G3',
            'g4' => 'G4',
            'rub' => 'Rub',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\BUFECommN1234Query the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BUFECommN1234Query(get_called_class());
    }
}
