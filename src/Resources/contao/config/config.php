<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_HOOKS']['getPageLayout']['huh.head-bundle'] = ['huh.head.listener.hooks', 'getPageLayout'];
$GLOBALS['TL_HOOKS']['parseFrontendTemplate']['huh.head-bundle'] = ['huh.head.listener.hooks', 'parseFrontendTemplate'];
