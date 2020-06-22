<?php

namespace modules\shopandshow\models\test;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "ss_gt_metriks".
 *
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property integer $gt_onload_time
 * @property integer $gt_page_elements
 * @property integer $gt_dom_content_loaded_time
 * @property integer $gt_dom_interactive_time
 * @property integer $gt_page_bytes
 * @property integer $gt_page_load_time
 * @property integer $gt_fully_loaded_time
 * @property integer $gt_html_load_time
 * @property integer $gt_rum_speed_index
 * @property integer $gt_yslow_score
 * @property integer $gt_pagespeed_score
 * @property integer $gt_backend_duration
 * @property string $gt_id
 * @property string $gt_report_url
 * @property string $test_url
 */
class GTMetrix extends \yii\db\ActiveRecord
{

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
            [['gt_onload_time',], 'required'],
            [['gt_onload_time', 'gt_page_elements', 'gt_dom_content_loaded_time', 'gt_dom_interactive_time',
                'gt_page_bytes', 'gt_page_load_time', 'gt_fully_loaded_time', 'gt_html_load_time', 'gt_rum_speed_index',
                'gt_yslow_score', 'gt_pagespeed_score', 'gt_backend_duration'
            ], 'integer'],
            [['gt_id'], 'string', 'max' => 50],
            [['gt_report_url', 'test_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            TimestampBehavior::className() =>
                [
                    'class' => TimestampBehavior::className(),
                ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создан',
            'updated_at' => 'Изменен',

            'gt_onload_time' => 'gt_onload_time',
            'gt_page_elements' => 'gt_page_elements',
            'gt_dom_content_loaded_time' => 'gt_dom_content_loaded_time',
            'gt_dom_interactive_time' => 'gt_dom_interactive_time',
            'gt_page_bytes' => 'gt_page_bytes',
            'gt_page_load_time' => 'gt_page_load_time',
            'gt_fully_loaded_time' => 'gt_fully_loaded_time',
            'gt_html_load_time' => 'gt_html_load_time',
            'gt_rum_speed_index' => 'gt_rum_speed_index',
            'gt_yslow_score' => 'gt_yslow_score',
            'gt_pagespeed_score' => 'gt_pagespeed_score',
            'gt_backend_duration' => 'gt_backend_duration',
            'gt_id' => 'gt_id',
            'gt_report_url' => 'gt_report_url',
            'test_url' => 'gt_report_url',
        ];
    }


    /**
     * @param $result
     * @param $url
     * @param $testId
     * @return bool
     */
    public static function add($result, $url, $testId)
    {

        $gtMetrix = new self();

        $gtMetrix->test_url = $url;
        $gtMetrix->gt_onload_time = $result['onload_time'];
        $gtMetrix->gt_page_elements = $result['page_elements'];
        $gtMetrix->gt_dom_content_loaded_time = $result['dom_content_loaded_time'];
        $gtMetrix->gt_dom_interactive_time = $result['dom_interactive_time'];
        $gtMetrix->gt_page_load_time = $result['page_load_time'];
        $gtMetrix->gt_fully_loaded_time = $result['fully_loaded_time'];
        $gtMetrix->gt_html_load_time = $result['html_load_time'];
        $gtMetrix->gt_rum_speed_index = $result['rum_speed_index'];
        $gtMetrix->gt_yslow_score = $result['yslow_score'];
        $gtMetrix->gt_pagespeed_score = $result['pagespeed_score'];
        $gtMetrix->gt_id = $testId;
        $gtMetrix->gt_report_url = $result['report_url'];

        return $gtMetrix->save();
    }

}
