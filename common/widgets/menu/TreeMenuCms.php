<?php
/**
 * Created by PhpStorm.
 * User: koval
 * Date: 25.01.17
 * Time: 15:36
 */

namespace common\widgets\menu;

use skeeks\cms\cmsWidgets\treeMenu\TreeMenuCmsWidget;

class TreeMenuCms extends TreeMenuCmsWidget
{

    public $path = null;
    public $data = [];
    public $showSubMenu = true;
    public $filters = null;

}