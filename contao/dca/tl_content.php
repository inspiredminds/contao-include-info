<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use InspiredMinds\IncludeInfoBundle\EventListener\ContentChildRecordCallbackListener;
use InspiredMinds\IncludeInfoBundle\EventListener\HeaderCallbackListener;
use InspiredMinds\IncludeInfoBundle\EventListener\OnloadCallbackListener;
use InspiredMinds\IncludeInfoBundle\Widget\IncludeInfoWidget;

$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = [OnloadCallbackListener::class, 'onLoadCallback'];
$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_callback'] = [ContentChildRecordCallbackListener::class, 'onChildRecordCallback'];
$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['header_callback'] = [HeaderCallbackListener::class, 'onHeaderCallback'];
$GLOBALS['TL_DCA']['tl_content']['fields']['includeInfo'] = ['inputType' => IncludeInfoWidget::class];
