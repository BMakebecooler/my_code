<?php

namespace modules\shopandshow\models\shares;

use common\helpers\Dates;
use common\helpers\Strings;
use skeeks\cms\models\StorageFile;
use Yii;
use yii\data\ArrayDataProvider;
use modules\shopandshow\models\shop\ShopBasket;
use skeeks\cms\components\Cms;

class SharesStat extends \yii\base\Model
{
    public $date;
    public $dateFrom;
    public $dateTo;

    public $time;
    public $timeFrom;
    public $timeTo;

    public function init()
    {
        parent::init();

        if (!$this->date) {
            //$this->time = SsShare::getDate();
            $this->time = Dates::beginEfirPeriod();
            $this->date = date('Y-m-d', $this->time);
        }

        if (!$this->dateFrom){
            $this->timeFrom = Dates::beginEfirPeriod() - DAYS_7;
            $this->dateFrom = date('Y-m-d', $this->timeFrom);
        }

        if (!$this->dateTo){
            $this->timeTo = Dates::endEfirPeriod();
            $this->dateTo = date('Y-m-d', $this->timeTo);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date', 'dateTo', 'dateFrom'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date' => 'Дата',
            'dateFrom' => 'Дата С',
            'dateTo' => 'Дата По',
        ];
    }

    /**
     * Подготовка данных баннеров для статистического отчета по ним
     *
     * @return ArrayDataProvider
     */

    public function getDataProvider($template = 'site'){ // site || mail

        $shares = [];

        //* Баннеры не из сетки *//

        $unblockShares = SsShare::find()
            ->alias('shares')
            ->andWhere(['<=', 'shares.begin_datetime', $this->time])
            ->andWhere(['>=', 'shares.end_datetime', $this->time])
            ->andWhere("shares.banner_type NOT LIKE 'BANNER_%'")
            ->andWhere("shares.banner_type!='ACTION_BANNER'")
            ->andWhere(['!=', 'shares.banner_type', SsShare::BANNER_TYPE_SANDS_PROMO_CTS])
            ->andWhere(['!=', 'shares.banner_type', SsShare::BANNER_TYPE_SANDS_PROMO_CTS2])
            ->andWhere(['shares.active' => Cms::BOOL_Y])
            ->orderBy('shares.banner_type ASC, shares.id ASC')

            ->leftJoin(SsShareSeller::tableName() . ' AS banners_sales', "banners_sales.share_id=shares.id")
            ->leftJoin(ShopBasket::tableName(). ' AS basket', "basket.order_id=banners_sales.order_id AND basket.main_product_id=banners_sales.product_id")
            ->leftJoin(StorageFile::tableName() . ' AS image',"image.id = shares.image_id")
            ->groupBy('shares.id')
            ->addSelect("shares.*")
            ->addSelect("image.cluster_file AS image")
            ->addSelect("SUM(basket.price*basket.quantity) AS price")
            ->asArray()
            ->all();

        //Для ручного разбиения для правильной сортировки до/после баннерной сетки
        $sharesMainWide = [];
        $sharesCts      = [];
        $sharesOthers   = [];

        if ($unblockShares){

            foreach ($unblockShares as $share) {
                //$share['image_src'] = $share['image_id'] ? \Yii::$app->getUrlManager()->createAbsoluteUrl(\Yii::$app->storage->getCluster('local')->publicBaseUrl).$share['image'] : '';
                $share['grid_banner_num'] = 0;
                $share['image_src'] = $share['image_id'] ? \Yii::$app->getUrlManager()->createAbsoluteUrl('/uploads/all/').$share['image'] : '';
                $share['page_views'] = $share['count_page_views'];
                $share['views'] = self::getBannerViews($share['id']);
                $share['block_type'] = $share['banner_type'];

                switch ($share['block_type']){
                    case 'MAIN_WIDE_1':
                        $share['block_row_num'] = 1;
                        $share['in_block_num'] = count($sharesMainWide) + 1;
                        $sharesMainWide[] = $share;
                        break;
                    case 'MAIN_CTS':
                        $share['block_row_num'] = 2;
                        $share['in_block_num'] = count($sharesCts) + 1;
                        $sharesCts[] = $share;
                        break;
                    default:
                        $share['block_row_num'] = $share['block_type'];
                        $share['in_block_num'] = 1;
                        $sharesOthers[] = $share;
                }
            }

            $shares = array_merge($sharesMainWide, $sharesCts);
        }

        //* /Баннеры не из сетки *//

        //* Баннерные блоки ( сетка ) *//
        $sharesBlocks = SsShareSchedule::findByDate($this->time)->all();

        $blockNum = ($sharesMainWide ? 1 : 0) + ($sharesCts ? 1 : 0);

        if ( $sharesBlocks ){
            $shareNum = 0;
            foreach ($sharesBlocks as $sharesBlock) {
                /** @var $sharesBlock SsShareSchedule */

                $blockNum++;
                $blockTypeNum = \common\helpers\Strings::onlyInt($sharesBlock->block_type);

                //Проверка кейса на неномерные типы блоков
                if (!$blockTypeNum){
                    $blockTypeNum = str_replace('BLOCK', '', $sharesBlock->block_type);
                }

                $blockShares = SsShare::find()
                    ->alias('shares')
                    ->andWhere(['<=', 'shares.begin_datetime', $this->time])
                    ->andWhere(['>=', 'shares.end_datetime', $this->time])
                    ->andWhere("shares.banner_type LIKE 'BANNER_{$blockTypeNum}%'")
                    ->andWhere(['shares.active' => Cms::BOOL_Y])
                    ->andWhere(['shares.share_schedule_id' => $sharesBlock->id])
                    ->orderBy('shares.banner_type ASC')

                    ->leftJoin(SsShareSeller::tableName() . ' AS banners_sales', "banners_sales.share_id=shares.id")
                    ->leftJoin(ShopBasket::tableName(). ' AS basket', "basket.order_id=banners_sales.order_id AND basket.main_product_id=banners_sales.product_id")
                    ->leftJoin(StorageFile::tableName() . ' AS image',"image.id = shares.image_id")
                    ->groupBy('shares.id')
                    ->addSelect("shares.*")
                    ->addSelect("image.cluster_file AS image")
                    ->addSelect("SUM(basket.price*basket.quantity) AS price")
                    ->asArray()
                    ->all();

                if ($blockShares){
                    foreach ($blockShares as $share) {
                        $share['block_row_num'] = $blockNum;
                        $share['grid_banner_num'] = ++$shareNum;
//                        $share['image_src'] = $share['image_id'] ? \Yii::$app->getUrlManager()->createAbsoluteUrl(\Yii::$app->storage->getCluster('local')->publicBaseUrl) . $share['image'] : '';
                        $share['image_src'] = $share['image_id'] ? \Yii::$app->getUrlManager()->createAbsoluteUrl('/uploads/all/') . $share['image'] : '';
                        $share['page_views'] = $share['count_page_views'];
                        $share['views'] = self::getBannerViews($share['id']);

                        //Номерные или описательные типы баннеров
                        $matchCondition = is_numeric($blockTypeNum) ? "(\d+)_*(\d*)" : "(\w+)";

                        preg_match("/^BANNER_{$matchCondition}$/", $share['banner_type'], $matches);
                        $share['block_type'] = $matches[1];
                        $share['in_block_num'] = !empty($matches[2]) ? $matches[2] : 1;

                        $shares[] = $share;
                    }
                }
            }
        }

        //* /Баннерные блоки ( сетка ) *//

        //Если у нас альтернативный тип отображения - приходится пересчитывать номера рядов для Остальных баннеров
        if ($shares && empty($_GET['v']) && $sharesOthers){
            foreach ($sharesOthers as $k => $sharesOther) {
                $sharesOthers[$k]['block_row_num'] = ++$blockNum;
            }
        }

        $shares = array_merge($shares, $sharesOthers);

        //Для альтернотивного типа отображения требуется другой формат данных - пересобираем
        if ($shares && empty($_GET['v'])){
            //Для сравнения со средними значениями выберем данные за последние 4 недели до даты отчета

            //Из-за выявленной некоректности данных ограничим выборку для среднего значения периодом когда ошибка была исправлена
            $countClickTimeProper = strtotime('2018-06-29');

            $modelForAvg = new self();
            $modelForAvg->setDateFrom( date('Y-m-d', max($this->time - DAYS_30, $countClickTimeProper)) );
            $modelForAvg->setDateTo( date('Y-m-d', $this->time - DAYS_1) );

            $dataProviderByDateAndType = $modelForAvg->prepareBannersByDateAndTypeForReport($modelForAvg->getBannersByDateAndTypeData())->getModels();

            $sharesV2 = [];
            foreach ($shares as $share) {
                if (!isset($sharesV2[$share['block_row_num']])){
                    $sharesV2[$share['block_row_num']] = [
                        'block_row_num' => $share['block_row_num'],
                        'block_type' => $share['block_type'],
                        'block_clicks'  => 0,
                        'banner_1'  => '',
                        'banner_2'  => '',
                        'banner_3'  => '',
                        'banner_4'  => '',
                        'banner_5'  => '',
                    ];
                }

                $sharesV2[$share['block_row_num']]['block_clicks'] += $share['count_click'];

                $shareImage = '';
                if ($share['image_id']){
                    $shareImage = \yii\helpers\Html::img($share['image_src'], ['style' => 'max-width: 100%; max-height: 100px;']);
                }

                $shareCtr = ($share['count_click'] && $share['views']) ? $share['count_click']/$share['views'] : 0;

                if (!empty($dataProviderByDateAndType[$share['block_type']]['avg'][$share['in_block_num']])){
                    $shareCtrAvg = $dataProviderByDateAndType[$share['block_type']]['avg'][$share['in_block_num']]['ctr'];
                }else{
                    $shareCtrAvg = 0;
                }

                $shareCtrPercent = \Yii::$app->formatter->asPercent($shareCtr, 1);
                $shareCtrAvgPercent = \Yii::$app->formatter->asPercent($shareCtrAvg, 1);
                $sharePrice = \Yii::$app->formatter->asDecimal(round($share['price']));

                $ctrBgColor = $shareCtr >= $shareCtrAvg ? 'green' : 'red';

                switch ($template){
                    case 'mail':

                        $borderColor = '#286090';

                        $shareStat = "
{$shareImage}

<table style='table-layout: fixed; width: 100%; border-collapse: collapse; border: 1px solid {$borderColor}; margin-top: 6px;' cellpadding='0' cellspacing='0'>
    <thead>
        <tr style='font-size: 12px;'>
            <th style='padding: 1px; border-right: 1px solid {$borderColor};'>Просм.<br>Бан./Стр.</th>
            <th style='padding: 1px; border-right: 1px solid {$borderColor};'>Клик</th>
            <th style='padding: 1px; border-right: 1px solid {$borderColor};'>$</th>
            <th style='padding: 1px; border-right: 1px solid {$borderColor};'>CTR</th>
            <th style='padding: 1px;'>CTRср</th>
        </tr>    
    </thead>
    <tbody>
        <tr>
            <td style='text-align: center; border-right: 1px solid {$borderColor};'>{$share['views']} / {$share['page_views']}</td>
            <td style='text-align: center; border-right: 1px solid {$borderColor};'>{$share['count_click']}</td>
            <td style='text-align: center; border-right: 1px solid {$borderColor};'>{$sharePrice}</td>
            <td style='text-align: center; border-right: 1px solid {$borderColor};'>
                <span style='padding: 1px 3px; background-color: {$ctrBgColor}; color: #ffffff;'>{$shareCtrPercent}</span>
            </td>
            <td style='text-align: center;'>{$shareCtrAvgPercent}</td>
        </tr>
    </tbody>
</table>
";

                        break;
                    case 'site':
                    default:
                        $shareStat = "
{$shareImage}

<table class='table' style='table-layout: fixed;'>
    <thead>
        <tr style='font-size: 10px;'>
            <th class='text-center' style='padding: 1px;'>Просм.<br>Бан./Стр.</th>
            <th class='text-center' style='padding: 1px;'>Клик</th>
            <th class='text-center' style='padding: 1px;'>$</th>
            <th class='text-center' style='padding: 1px;'>CTR</th>
            <th class='text-center' style='padding: 1px;'>CTRср</th>
        </tr>    
    </thead>
    <tbody>
        <tr>
            <td>{$share['views']} / {$share['page_views']}</td>
            <td>{$share['count_click']}</td>
            <td>{$sharePrice}</td>
            <td><span style='padding: 1px 3px; background-color: {$ctrBgColor}; color: #ffffff;'>{$shareCtrPercent}</span></td>
            <td>{$shareCtrAvgPercent}</td>
        </tr>
    </tbody>
</table>
";
                }

                $sharesV2[$share['block_row_num']]["banner_{$share['in_block_num']}"] = $shareStat;
            }
            $shares = $sharesV2;
        }

        return new ArrayDataProvider([
            'allModels' => $shares,
            'sort' => [
                'attributes' => ['block_row_num'],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    /**
     * Подготовка данных баннеров для отчета в разрезе типов баннеров и периодов
     *
     * @return ArrayDataProvider
     */

    public function getBannersByDateAndTypeData(){

        $blocksByDateAndType = [];

        //* Для НЕ сеточных баннеров *//

        $unblockShares = SsShare::find()
            ->alias('shares')
            ->andWhere(['<=', 'shares.begin_datetime', $this->timeTo])
            ->andWhere(['>=', 'shares.end_datetime', $this->timeFrom])
            ->andWhere("shares.banner_type NOT LIKE 'BANNER_%'")
            ->andWhere("shares.banner_type!='ACTION_BANNER'")
            ->andWhere(['shares.active' => Cms::BOOL_Y])
            ->orderBy('shares.begin_datetime, shares.banner_type ASC, shares.id ASC')

            ->leftJoin(SsShareSeller::tableName() . ' AS banners_sales', "banners_sales.share_id=shares.id")
            ->leftJoin(ShopBasket::tableName(). ' AS basket', "basket.order_id=banners_sales.order_id AND basket.main_product_id=banners_sales.product_id")
            ->leftJoin(StorageFile::tableName() . ' AS image',"image.id = shares.image_id")
            ->groupBy('shares.id')
            ->addSelect("shares.*")
            ->addSelect("image.cluster_file AS image")
            ->addSelect("SUM(basket.price*basket.quantity) AS price")
            ->asArray();

        $unblockShares = $unblockShares->all();

        if ($unblockShares){

            foreach ($unblockShares as $share) {
                $date = date('Y-m-d', $share['begin_datetime']);

                $share['image_src'] = $share['image_id'] ? \Yii::$app->getUrlManager()->createAbsoluteUrl('/uploads/all/').$share['image'] : '';
                $share['views'] = self::getBannerViews($share['id']);
                $share['block_type'] = $share['banner_type'];

                $blocksByDateAndType[ $date ][ $share['banner_type'] ][] = $share;

            }
        }

        //* Для сетки баннеров *//
        //Первичная группировка - по дням (отталкиваемся от даты начала)
        //Вторичная - по типу блока

        //Ищем блоки для нашего диапазона
        $sharesBlocks = SsShareSchedule::findByDatePeriod($this->timeFrom, $this->timeTo)->all();

        if ( $sharesBlocks ){
            $sharesByType = [];

            foreach ($sharesBlocks as $sharesBlock) {
                $blockTime = $sharesBlock['begin_datetime'];
                $blockDate = date('Y-m-d', $blockTime);
                $blockTypeNum = \common\helpers\Strings::onlyInt($sharesBlock->block_type);

                if (!isset($sharesByType[$blockTypeNum])){
                    $sharesByType[$blockTypeNum] = [];
                }

                $blockShares = SsShare::find()
                    ->alias('shares')
                    ->andWhere(['<=', 'shares.begin_datetime', $blockTime])
                    ->andWhere(['>=', 'shares.end_datetime', $blockTime])
                    ->andWhere("shares.banner_type LIKE 'BANNER_{$blockTypeNum}%'")
                    ->andWhere(['shares.active' => Cms::BOOL_Y])
//                    ->andWhere(['NOT IN', 'shares.id', $sharesUpper])
                    ->andWhere(['shares.share_schedule_id' => $sharesBlock->id])
                    ->orderBy('shares.banner_type ASC')

                    ->leftJoin(SsShareSeller::tableName() . ' AS banners_sales', "banners_sales.share_id=shares.id")
                    ->leftJoin(ShopBasket::tableName(). ' AS basket', "basket.order_id=banners_sales.order_id AND basket.main_product_id=banners_sales.product_id")
                    ->leftJoin(StorageFile::tableName() . ' AS image',"image.id = shares.image_id")
                    ->groupBy('shares.id')
                    ->addSelect("shares.*")
                    ->addSelect("image.cluster_file AS image")
                    ->addSelect("SUM(basket.price*basket.quantity) AS price")
                    ->asArray()
                    ->all();

                if ($blockShares){
                    foreach ($blockShares as $share) {
                        $share['views'] = self::getBannerViews($share['id']);

                        preg_match("/^BANNER_(\d+)_*(\d*)$/", $share['banner_type'], $matches);
                        $share['block_type'] = $matches[1];
                        $share['in_block_num'] = !empty($matches[2]) ? $matches[2] : 1;

                        $blocksByDateAndType[$blockDate][$blockTypeNum][$sharesBlock->id][] = $share;
                    }
                }
            }
        }

        return new ArrayDataProvider([
            'allModels' => $blocksByDateAndType,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    /**
     * Пересобираем исходные "сырые" данные статистики по баннерам в разрезе периода и типа
     * в более понятный и структурированный вид
     *
     * @param ArrayDataProvider $dataProvider
     * @return ArrayDataProvider
     */

    public function prepareBannersByDateAndTypeForReport(ArrayDataProvider $dataProvider){
        $result = [];
        $models = $dataProvider->getModels();

        $sharesTypes = [
            'MAIN_WIDE_1',
            'MAIN_CTS',
            'BLOCK1',
            'BLOCK2',
            'BLOCK3',
            'BLOCK4',
            'BLOCK5',
            'BLOCK6',
            'BLOCK7',
            'BLOCK8',
            'BLOCK9',
            'BLOCK10',
            'BLOCK11',
            'BLOCK12',
            'MAIN_SITE_SALE_1',
            'MAIN_SITE_SALE_2',
            'MAIN_SITE_SALE_3',
            //'SANDS_PROMO_CTS2',
        ];

        $dateTo = \DateTime::createFromFormat('Y-m-d H:i:s', $this->dateTo . ' 23:59:59');

        foreach ($sharesTypes AS $sharesType){
            if (stripos($sharesType, 'block') !== false){
                $sharesType = (int)Strings::onlyInt($sharesType);
            }

            $daysNumWithShare = 0;
            $sharesTypeBlocksNum = 0;

            $result[$sharesType] = [
                'block_type'    => $sharesType,
                'days_num'      => 0,
                'blocks_num'    => 0,
                'total'         => [],
                'avg'           => [],
            ];

            $date = (new \DateTime())->setTimestamp($this->timeFrom);
            while ($date <= $dateTo){

                $dateStr = $date->format('Y-m-d');
                $result[$sharesType]['date_'.$dateStr] = [];

                if ( !empty($models[$dateStr][$sharesType]) ){
                    $daysNumWithShare++;

                    //Данные для конкретного типа блока (ибо может быть несколько баннеров одного типа)
                    $sharesNum = count($models[$dateStr][$sharesType]); //Не сетка - кол-во баннеров данного типа. Сетка - кол-во блоков данного типа
                    $sharesTypeBlocksNum += $sharesNum; //Кол-во блоков данного типа за период

                    foreach ($models[$dateStr][$sharesType] as $shareNumOrBlockId => $share) {
                        //В блоках сетки дополнительный уровен вложенности - учитываем
                        if (stripos($sharesType, 'block') !== false || is_int($sharesType) ){
                            $sharesBlockId = $shareNumOrBlockId;
                            //Считаем данные блока из его элементов
                            foreach ($share as $shareBlockItem) {
                                $bannerType = $shareBlockItem['in_block_num'];

                                //Дневные данные
                                if (!isset($result[$sharesType]["date_{$dateStr}"][$bannerType])){
                                    $result[$sharesType]["date_{$dateStr}"][$bannerType] = [
                                        'views'         => ['total' => 0, 'html' => '', 'by_blocks' => []],
                                        'count_click'   => ['total' => 0, 'html' => '', 'by_blocks' => []],
                                        'price'         => ['total' => 0, 'html' => '', 'by_blocks' => []],
                                        'ctr'           => ['total' => 0, 'html' => '', 'by_blocks' => []],
                                    ];
                                }

                                $result[$sharesType]["date_{$dateStr}"][$bannerType]['views']['total'] += $shareBlockItem['views'];
                                $result[$sharesType]["date_{$dateStr}"][$bannerType]['views']['by_blocks'][$sharesBlockId] = $shareBlockItem['views'];

                                $result[$sharesType]["date_{$dateStr}"][$bannerType]['count_click']['total'] += $shareBlockItem['count_click'];
                                $result[$sharesType]["date_{$dateStr}"][$bannerType]['count_click']['by_blocks'][$sharesBlockId] = (int)$shareBlockItem['count_click'];

                                $result[$sharesType]["date_{$dateStr}"][$bannerType]['price']['total'] += $shareBlockItem['price']?:0;
                                $result[$sharesType]["date_{$dateStr}"][$bannerType]['price']['by_blocks'][$sharesBlockId] = $shareBlockItem['price']?:0;

                                //Общие данные
                                if (!isset($result[$sharesType]['total'][$bannerType])){
                                    $result[$sharesType]['total'][$bannerType] = [
                                        'views'         => 0,
                                        'count_click'   => 0,
                                        'price'         => 0,
                                        'ctr'           => 0,
                                    ];
                                }

                                $result[$sharesType]['total'][$bannerType]['views'] += $shareBlockItem['views'];
                                $result[$sharesType]['total'][$bannerType]['count_click'] += $shareBlockItem['count_click'];
                                $result[$sharesType]['total'][$bannerType]['price'] += $shareBlockItem['price']?:0;

                            }

                        }else{

                            //+1 Что бы в отчете начиналось с 1, а не с 0
                            $shareNum = $shareNumOrBlockId + 1;

                            //Дневные данные
                            if (!isset($result[$sharesType]["date_{$dateStr}"][$shareNum])){
                                $result[$sharesType]["date_{$dateStr}"][$shareNum] = [
                                    'views'         => ['total' => 0, 'by_blocks' => []],
                                    'count_click'   => ['total' => 0, 'by_blocks' => []],
                                    'price'         => ['total' => 0, 'by_blocks' => []],
                                    'ctr'           => ['total' => 0, 'by_blocks' => []],
                                ];
                            }

                            $result[$sharesType]["date_{$dateStr}"][$shareNum]['views']['total'] += $share['views'];
                            $result[$sharesType]["date_{$dateStr}"][$shareNum]['count_click']['total'] += (int)$share['count_click'];
                            $result[$sharesType]["date_{$dateStr}"][$shareNum]['price']['total'] += $share['price']?:0;

                            //Общие данные
                            if (!isset($result[$sharesType]['total'][$shareNum])){
                                $result[$sharesType]['total'][$shareNum] = [
                                    'views'         => 0,
                                    'count_click'   => 0,
                                    'price'         => 0,
                                    'ctr'           => 0,
                                ];
                            }

                            $result[$sharesType]['total'][$shareNum]['views'] += $share['views'];
                            $result[$sharesType]['total'][$shareNum]['count_click'] += $share['count_click'];
                            $result[$sharesType]['total'][$shareNum]['price'] += $share['price']?:0;

                        }
                    }

                    //Дневной CTR для каждого элемента блока
                    foreach ($result[$sharesType] as $statElement => $statData) {
                        if ( stripos($statElement, 'total') !== false || stripos($statElement, 'date_') !== false ){
                            foreach ($statData as $statItemPos => $statDataItem) {

                                if ( $statElement == 'total' ){
                                    $views = $statDataItem['views'];
                                    $clicks = $statDataItem['count_click'];
                                }else{
                                    $views = $statDataItem['views']['total'];
                                    $clicks = $statDataItem['count_click']['total'];
                                }

                                $ctr = $views ? $clicks / $views : 0;
                                $result[$sharesType][$statElement][$statItemPos]['ctr'] = $ctr;
                            }
                        }
                    }

                }

                $date->add(new \DateInterval('P1D'));
            }

            $result[$sharesType]['days_num'] = $daysNumWithShare;
            $result[$sharesType]['blocks_num'] = $sharesTypeBlocksNum;

            //Вычислим средний CTR за период
            foreach ($result[$sharesType] as $statElement => $statData) {
                if ( stripos($statElement, 'total') !== false){
                    $sharesTypeBlocksNum = $result[$sharesType]['blocks_num'];
                    foreach ($statData as $statItemPos => $statDataItem) {

                        $result[$sharesType]['avg'][$statItemPos]['views'] = $statDataItem['views'] / $sharesTypeBlocksNum;
                        $result[$sharesType]['avg'][$statItemPos]['count_click'] = $statDataItem['count_click'] / $sharesTypeBlocksNum;
                        $result[$sharesType]['avg'][$statItemPos]['price'] = $statDataItem['price'] / $sharesTypeBlocksNum;

                        $ctr = $statDataItem['views'] ? $statDataItem['count_click'] / $statDataItem['views'] : 0;
                        $result[$sharesType]['avg'][$statItemPos]['ctr'] = $ctr;
                    }
                }
            }
        }

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    /**
     * Значеня ячеек GridView составные, собираем этим методом что бы дать больше информации пользователю
     *
     * @param ArrayDataProvider $dataProvider
     * @return ArrayDataProvider
     */
    public function getBannersByDateAndTypeGridData(ArrayDataProvider $dataProvider){
        $result = [];

        $models = $this->prepareBannersByDateAndTypeForReport($dataProvider)->getModels();

        if ($models){
            foreach ($models as $blockType => $blockData) {

                $result[$blockType] = [
                    'block_type'    => $blockType,
                    'total'         => [
                        'views'         => '',
                        'count_click'   => '',
                        'price'         => '',
                        'ctr'           => '',
                    ],
                    'avg'           => [
                        'views'         => '',
                        'count_click'   => '',
                        'price'         => '',
                        'ctr'           => '',
                    ],
                ];

                foreach ($blockData as $statElement => $statData) {
                    if ( stripos($statElement, 'total') !== false || stripos($statElement, 'avg') !== false || stripos($statElement, 'date_') !== false ){
                        foreach ($statData as $statItemPos => $statDataItem) {
                            if (!isset($result[$blockType][$statElement])){
                                $result[$blockType][$statElement] = [
                                    'views'         => '',
                                    'count_click'   => '',
                                    'price'         => '',
                                    'ctr'           => '',
                                ];
                            }

                            $prefix = '';
                            $views = $countClick = $price = $ctr = '';

                            if ($statElement == 'total' || $statElement == 'avg'){
                                $views = $statElement == 'total' ?
                                    \Yii::$app->formatter->asInteger($statDataItem['views']) : \Yii::$app->formatter->asDecimal($statDataItem['views'], 1);

                                $countClick = $statElement == 'total' ?
                                    \Yii::$app->formatter->asInteger($statDataItem['count_click']) : \Yii::$app->formatter->asDecimal($statDataItem['count_click'], 1);

                                $price = $statElement == 'total' ?
                                    \Yii::$app->formatter->asInteger($statDataItem['price']) : \Yii::$app->formatter->asDecimal($statDataItem['price'], 1);
                            }else{

                                if (count($statDataItem['views']['by_blocks']) > 1){
                                    $prefix .= ' = ';
                                    foreach ($statDataItem['views']['by_blocks'] as $amount) {
                                        $views .= ($views ? '+':'') . \Yii::$app->formatter->asInteger($amount);
                                    }

                                    foreach ($statDataItem['count_click']['by_blocks'] as $amount) {
                                        $countClick .= ($countClick ? '+':'') . \Yii::$app->formatter->asInteger($amount);
                                    }

                                    foreach ($statDataItem['price']['by_blocks'] as $amount) {
                                        $price .= ($price ? '+':'') . \Yii::$app->formatter->asInteger($amount);
                                    }
                                }

                                $views      .= $prefix . \Yii::$app->formatter->asInteger($statDataItem['views']['total']);
                                $countClick .= $prefix . \Yii::$app->formatter->asInteger($statDataItem['count_click']['total']);
                                $price      .= $prefix . \Yii::$app->formatter->asInteger($statDataItem['price']['total']);
                            }

                            $ctr = \Yii::$app->formatter->asPercent($statDataItem['ctr'], 1);

                            $result[$blockType][$statElement]['views'] .= "{$statItemPos}: {$views}<br>";
                            $result[$blockType][$statElement]['count_click'] .= "{$statItemPos}: {$countClick}<br>";
                            $result[$blockType][$statElement]['price'] .= "{$statItemPos}: {$price}<br>";
                            $result[$blockType][$statElement]['ctr'] .= "{$statItemPos}: {$ctr}<br>";
                        }
                    }
                }
            }
        }

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }

    /**
     * @param mixed $date
     * @return SharesStat
     */
    public function setDate($date)
    {
        $this->date = $date;
        $this->time = Dates::beginEfirPeriod(strtotime($this->date));
        return $this;
    }

    /**
     * @param mixed $dateFrom
     * @return SharesStat
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;
        $this->timeFrom = Dates::beginEfirPeriod(strtotime($this->dateFrom));
        return $this;
    }

    /**
     * @param mixed $dateTo
     * @return SharesStat
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;
        $this->timeTo = Dates::endEfirPeriod(strtotime($this->dateTo));
        return $this;
    }

    /**
     * Получение данных о кол-ве просмотров указанного баннера
     *
     * @param $shareId
     * @return int
     */
    public static function getBannerViews($shareId){
        return \Yii::$app->redis->get("view_banner_{$shareId}") ?: 0;
    }
}










