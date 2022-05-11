<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_HOOKS']['getPageLayout']['huh.head-bundle'] = ['huh.head.listener.hooks', 'getPageLayout'];
