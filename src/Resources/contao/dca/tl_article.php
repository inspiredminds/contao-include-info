<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\IncludeInfoBundle\EventListener\OnloadCallbackListener;
use InspiredMinds\IncludeInfoBundle\Widget\IncludeInfoWidget;

$GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = [OnloadCallbackListener::class, 'onLoadCallback'];
$GLOBALS['TL_DCA']['tl_article']['fields']['includeInfo'] = ['inputType' => IncludeInfoWidget::class];
