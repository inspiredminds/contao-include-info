<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\IncludeInfoBundle\Widget\IncludeInfoWidget;

$GLOBALS['BE_FFL'][IncludeInfoWidget::class] = IncludeInfoWidget::class;
