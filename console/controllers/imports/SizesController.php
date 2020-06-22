<?php
/**
 * php ./yii imports/sizes/import-scales
 * php ./yii imports/sizes/import-sizes
 * php ./yii imports/sizes/import-relations
 *
 */

namespace console\controllers\imports;


use common\helpers\Exel;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\dicts\Content;

class SizesController extends \yii\console\Controller
{

    protected $dataSize = [
        'source_size' => 0,
        'dest_size' => 1,
        'source_scale' => 2,
        'dest_scale' =>3
    ];

    protected $srcFiles = [
        'relations' => 'SIZE_MAP.xls',
        'sizes' => 'MERCH_SIZE.xls',
        'scales' => 'GUID_SCALE.xls',
        'sizes_scales' => 'SIZE_IN_SCALE.xls'
    ];


    public function actionImportSizes()
    {
        $src = __DIR__.'/files/MERCH_SIZE.xls';
        $data = Exel::parceExel($src);
        $sizes = [];
        foreach ($data as $part){
            $sizes[$part[0]] = $part;
        }

        $srcScale = __DIR__.'/files/SIZE_IN_SCALE.xls';
        $dataScale = Exel::parceExel($srcScale);


        foreach ($dataScale as $k=>$part){
            if($k == 1)
                continue;

            $sizeScale = Guids::getEntityByGuid($part[2]);

            if (!$sizeScale) {
                Job::dump('Не найден родительский раздел '.$part[2]);
                continue;
            }
            $sizeData = $sizes[$part[1]];
            if(!$sizeData){
                Job::dump('Не найден размер '.$part[2]);
                continue;
            }

            Job::dump('--- MerchSize ----');
            Job::dump('Guid: '.$sizeData[0]);
            Job::dump('SizeName: '.$sizeData[2]);
            Job::dump('SizeCode: '.$sizeData[1]);

            $newContent = new Content();

            $newContent->setAttributes([
                'name' => $sizeScale->name,
                'code' => $sizeScale->code,
                'guid' => $part[2],
                'contentType' => Content::CONTENT_TYPE_KFSS_INFO_SIZES,
                'description' => $sizeScale->description,
                'contentElements' => [
                    'guid' => $sizeData[0],
                    'name' => $sizeData[2],
                    'code' => $sizeData[0],
                    'description' => '',
                    'active' => true,
                    'priority' => 500,
                ],
            ]);

            $newContent->addData();
        }
    }

    protected function generateCode($string)
    {
        $string = trim(str_replace('Bitrix.', '', $string));
        return 'KFSS_'.mb_strtoupper(str_replace('-', '_', \common\helpers\Strings::translit($string)));
    }


    public function actionImportScales()
    {
        $src = __DIR__.'/files/GUID_SCALE.xls';
        $data = Exel::parceExel($src);
        foreach ($data as $part){

            $sizeScale = Guids::getEntityByGuid($part[0]);
            if (!$sizeScale) {

                Job::dump('--- SizeScale ----');
                Job::dump('Guid: '.$part[0]);
                Job::dump('ScaleName: '. $part[1]);
                Job::dump('ScaleDescr: '.$part[2]);

                $code = $this->generateCode($part[1]);
                $newContent = new Content();

                $newContent->setAttributes([
                    'contentType' => Content::CONTENT_TYPE_KFSS_INFO_SIZES,
                    'description' => $part[2],
                    'guid' => $part[0],
                    'name' => $part[1],
                    'code' => $code,
                    'active' => true,
                    'contentProperties' => [
                        'content_id' => OFFERS_CONTENT_ID,
                        //'code' => $code,
                        'name' => $data[1],
                        'property_type' => 'L',
                        'list_type' => 'L',
                        'is_required' => 'N',
                        'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement',
                    ]
                ]);
                $newContent->addData();
            }
        }
    }

    public function actionImportRelations()
    {
        $src = __DIR__.'/files/SIZE_MAP.xls';

        $data = Exel::parceExel($src);


        foreach ($data as $k=>$row) {
            $sizesRelation = [];
            $scaleRelation = [];

            $sourceScaleGuid = $row[$this->dataSize['source_scale']];
            if ($sourceScaleGuid) {
                $sourceScale = Guids::getEntityByGuid($sourceScaleGuid);
                if ($sourceScale) {
                    $scaleRelation[] = $sourceScale->id;
                }
            }
            $destScaleGuid = $row[$this->dataSize['dest_scale']];
            if ($destScaleGuid) {
                $destScale = Guids::getEntityByGuid($destScaleGuid);
                if ($destScale) {
                    $scaleRelation[] = $destScale->id;
                }
            }

            $destSizeGuid = $row[$this->dataSize['dest_size']];
            if ($destSizeGuid) {
                $destSize = Guids::getEntityByGuid($destSizeGuid);

                if ($destSize) {
                    $sizesRelation[1] = $destSize->id;
                    $sizesRelation[3] = $destSize->name;
                }
            }

            $sourceSizeGuid = $row[$this->dataSize['source_size']];
            if ($sourceSizeGuid) {
                $sourceSize = Guids::getEntityByGuid($sourceSizeGuid);
                if ($sourceSize) {
                    $sizesRelation[0] = $sourceSize->id;
                    $sizesRelation[2] = $sourceSize->name;
                }else{
                    if($destSize && $sourceScale){
                        $sql = "SELECT id from cms_content_element where content_id=:content_id AND name = :name";
                        $id = \Yii::$app->db->createCommand($sql, [
                            ':content_id' => $sourceScale->id,
                            ':name' => $destSize->name
                        ])->queryOne();
                        if($id['id']) {
                            $sizesRelation[0] = $id['id'];
                        }
                    }
                }
            }


            if (count($sizesRelation) >= 2) {

                $sql = "INSERT IGNORE INTO cms_content_element_relation SET
                       content_element_id = :content_element_id,
                       related_content_element_id = :related_content_element_id";

                \Yii::$app->db->createCommand($sql, [
                    ':content_element_id' => $sizesRelation[0],
                    ':related_content_element_id' => $sizesRelation[1]
                ])->query();

            }

//            if (count($scaleRelation) == 2) {
//
//                $sql = "INSERT IGNORE INTO cms_content_relation SET
//                        content_id = :content_element_id {$scaleRelation[0]},
//                        related_content_id = :related_content_element_id {$scaleRelation[1]}";
//
//                \Yii::$app->db->createCommand($sql, [
//                    ':content_element_id' => $scaleRelation[0],
//                    ':related_content_element_id' => $scaleRelation[1]
//                ])->query();
//
//            }
        }
    }

}