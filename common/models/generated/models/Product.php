<?php

namespace common\models\generated\models;


/**
 * This is the model class for table "product".
 *
 * @property integer $id ID
 * @property integer $published_at Published At
 * @property integer $published_to Published To
 * @property integer $priority Priority
 * @property string $active Active
 * @property string $name Name
 * @property integer $image_id Image ID
 * @property integer $image_full_id Image Full ID
 * @property string $code Code
 * @property integer $content_id Content ID
 * @property integer $tree_id Tree ID
 * @property integer $bitrix_id Bitrix ID
 * @property string $is_base Is Base
 * @property integer $count_children Count Children
 * @property integer $guid_id Guid ID
 * @property integer $kfss_id Kfss ID
*/
class Product extends \common\ActiveRecord
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
        return 'product';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id', 'published_at', 'published_to', 'priority', 'image_id', 'image_full_id', 'content_id', 'tree_id', 'bitrix_id', 'count_children', 'guid_id', 'kfss_id'], 'integer'],
            [['name', 'code'], 'required'],
            [['active', 'is_base'], 'string', 'max' => 1],
            [['name', 'code'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'published_at' => 'Published At',
            'published_to' => 'Published To',
            'priority' => 'Priority',
            'active' => 'Active',
            'name' => 'Name',
            'image_id' => 'Image ID',
            'image_full_id' => 'Image Full ID',
            'code' => 'Code',
            'content_id' => 'Content ID',
            'tree_id' => 'Tree ID',
            'bitrix_id' => 'Bitrix ID',
            'is_base' => 'Is Base',
            'count_children' => 'Count Children',
            'guid_id' => 'Guid ID',
            'kfss_id' => 'Kfss ID',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ProductQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ProductQuery(get_called_class());
    }
}
