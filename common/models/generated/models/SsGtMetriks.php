<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_gt_metriks".
 *
 * @property integer $id ID
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $gt_onload_time Gt Onload Time
 * @property integer $gt_page_elements Gt Page Elements
 * @property integer $gt_dom_content_loaded_time Gt Dom Content Loaded Time
 * @property integer $gt_dom_interactive_time Gt Dom Interactive Time
 * @property integer $gt_page_bytes Gt Page Bytes
 * @property integer $gt_page_load_time Gt Page Load Time
 * @property integer $gt_fully_loaded_time Gt Fully Loaded Time
 * @property integer $gt_html_load_time Gt Html Load Time
 * @property integer $gt_rum_speed_index Gt Rum Speed Index
 * @property integer $gt_yslow_score Gt Yslow Score
 * @property integer $gt_pagespeed_score Gt Pagespeed Score
 * @property integer $gt_backend_duration Gt Backend Duration
 * @property string $gt_id Gt ID
 * @property string $gt_report_url Gt Report Url
 * @property string $test_url Test Url
*/
class SsGtMetriks extends \common\ActiveRecord
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
    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'ss_gt_metriks';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'gt_onload_time', 'gt_page_elements', 'gt_dom_content_loaded_time', 'gt_dom_interactive_time', 'gt_page_bytes', 'gt_page_load_time', 'gt_fully_loaded_time', 'gt_html_load_time', 'gt_rum_speed_index', 'gt_yslow_score', 'gt_pagespeed_score', 'gt_backend_duration'], 'integer'],
            [['gt_id'], 'string', 'max' => 50],
            [['gt_report_url', 'test_url'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'gt_onload_time' => 'Gt Onload Time',
            'gt_page_elements' => 'Gt Page Elements',
            'gt_dom_content_loaded_time' => 'Gt Dom Content Loaded Time',
            'gt_dom_interactive_time' => 'Gt Dom Interactive Time',
            'gt_page_bytes' => 'Gt Page Bytes',
            'gt_page_load_time' => 'Gt Page Load Time',
            'gt_fully_loaded_time' => 'Gt Fully Loaded Time',
            'gt_html_load_time' => 'Gt Html Load Time',
            'gt_rum_speed_index' => 'Gt Rum Speed Index',
            'gt_yslow_score' => 'Gt Yslow Score',
            'gt_pagespeed_score' => 'Gt Pagespeed Score',
            'gt_backend_duration' => 'Gt Backend Duration',
            'gt_id' => 'Gt ID',
            'gt_report_url' => 'Gt Report Url',
            'test_url' => 'Test Url',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsGtMetriksQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsGtMetriksQuery(get_called_class());
    }
}
