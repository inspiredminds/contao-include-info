<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use InspiredMinds\IncludeInfoBundle\EventListener\ContentLabelCallbackListener;
use InspiredMinds\IncludeInfoBundle\EventListener\HeaderCallbackListener;
use InspiredMinds\IncludeInfoBundle\EventListener\OnloadCallbackListener;
use InspiredMinds\IncludeInfoBundle\Widget\IncludeInfoWidget;

$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = [OnloadCallbackListener::class, 'onLoadCallback'];
$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['header_callback'] = [HeaderCallbackListener::class, 'onHeaderCallback'];
$GLOBALS['TL_DCA']['tl_content']['list']['label']['label_callback'] = [ContentLabelCallbackListener::class, 'onLabelCallback'];
$GLOBALS['TL_DCA']['tl_content']['fields']['includeInfo'] = ['inputType' => IncludeInfoWidget::class];
