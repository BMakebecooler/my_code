<?php
/**
 * User: koval
 * Date: 21.02.17
 * Time: 13:02
 */

namespace common\components;

use yii\helpers\Html;

class CustomPagination extends \yii\widgets\LinkPager
{
//    public function init()
//    {
//        parent::init();
//        $this->pagination = new \common\components\Pagination();
//    }
//
//    public function run()
//    {
//        parent::run();
//    }

    /**
     * Renders the page buttons.
     * @return string the rendering result
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false && $currentPage > $this->maxButtonCount) { //тут показываем 1 страницу если только мы ушли за пределы maxButtonCount
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false && $currentPage > $this->maxButtonCount) {//тут показываем 1 страницу если только мы ушли за пределы maxButtonCount
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false && $currentPage < ($pageCount - $this->maxButtonCount)) { //тут показываем последнюю страницу если только мы не ушли за пределы maxButtonCount
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }


        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false && $currentPage < ($pageCount - $this->maxButtonCount)) {//тут показываем последнюю страницу если только мы не ушли за пределы maxButtonCount
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }


        // Стрелочка - переход на след страницу
        if (($page = $currentPage + 1) < $pageCount) {
            $buttons[] = $this->renderPageButton('', $page, 'pagination_arrow', $currentPage >= $pageCount - 1, false);
        }


        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }
}