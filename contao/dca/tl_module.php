<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\IncludeInfoBundle\EventListener\OnloadCallbackListener;
use InspiredMinds\IncludeInfoBundle\Widget\IncludeInfoWidget;

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = [OnloadCallbackListener::class, 'onLoadCallback'];
$GLOBALS['TL_DCA']['tl_module']['fields']['includeInfo'] = ['inputType' => IncludeInfoWidget::class];
