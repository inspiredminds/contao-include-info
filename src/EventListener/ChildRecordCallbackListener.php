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

use Contao\DC_Table;
use InspiredMinds\IncludeInfoBundle\Widget\IncludeInfoWidget;
use tl_content;

class ChildRecordCallbackListener
{
    public function onChildRecordCallback(array $row)
    {
        $childRecord = (new tl_content())->addCteType($row);

        if ('tl_article' !== $row['ptable']) {
            return $childRecord;
        }

        $widget = new IncludeInfoWidget(['strTable' => 'tl_content']);
        $widget->dataContainer = new DC_Table('tl_content');
        $widget->dataContainer->activeRecord = (object) $row;
        $includeInfo = $widget->parse();

        $pos = strpos($childRecord, '</div>');
        if (false !== $pos) {
            $childRecord = substr_replace($childRecord, $includeInfo, $pos + 6, 0);
        }

        return $childRecord;
    }
}
