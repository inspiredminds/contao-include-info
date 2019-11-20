<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\IncludeInfoBundle\EventListener\HeaderCallbackListener;

$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['header_callback'] = [HeaderCallbackListener::class, 'onHeaderCallback'];
