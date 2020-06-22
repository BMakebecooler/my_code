<?php
namespace modules\shopandshow\models\shares;


/**
 * This is the model class for table "ss_shares_type".
 *
 * @property integer $id
 * @property string $code
 * @property string $description
 */
class SsShareType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_shares_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'description'], 'string', 'max' => 255],
            [['code', 'description'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Код',
            'description' => 'Описание',
        ];
    }
}
