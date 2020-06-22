<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_ECommFeed".
 *
 * @property integer $OFFCNT_ID
 * @property string $LOT_CODE
 * @property string $DT_MAX
 * @property integer $F
 */
class BUFECommFeed extends \common\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'BUF_ECommFeed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['OFFCNT_ID', 'F'], 'integer'],
            [['DT_MAX'], 'safe'],
            [['LOT_CODE'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'OFFCNT_ID' => 'Offcnt  ID',
            'LOT_CODE' => 'Lot  Code',
            'DT_MAX' => 'Dt  Max',
            'F' => 'F',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\query\BUFECommFeedQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\generated\query\BUFECommFeedQuery(get_called_class());
    }
}
