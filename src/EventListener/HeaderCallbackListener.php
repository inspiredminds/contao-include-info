<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

use Contao\ArticleModel;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use InspiredMinds\IncludeInfoBundle\Widget\IncludeInfoWidget;

class HeaderCallbackListener
{
    public function onHeaderCallback(array $add, DataContainer $dc): array
    {
        $widget = new IncludeInfoWidget(['strTable' => 'tl_article']);
        $widget->dataContainer = new DC_Table('tl_article');
        $widget->dataContainer->activeRecord = ArticleModel::findByPk((int) Input::get('id'));
        $includeInfo = trim($widget->parse());

        if (!empty($includeInfo)) {
            $add[$GLOBALS['TL_LANG']['tl_content']['includeinfo_legend']] = $includeInfo;
        }

        return $add;
    }
}
