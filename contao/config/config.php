<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use InspiredMinds\IncludeInfoBundle\Model\InsertTagIndexModel;
use InspiredMinds\IncludeInfoBundle\Widget\IncludeInfoWidget;

$GLOBALS['BE_FFL'][IncludeInfoWidget::class] = IncludeInfoWidget::class;
$GLOBALS['TL_MODELS']['tl_inserttag_index'] = InsertTagIndexModel::class;
