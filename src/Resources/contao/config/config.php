<?php

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getPageLayout']['huh.head-bundle'] = ['huh.head.listener.hooks', 'getPageLayout'];
$GLOBALS['TL_HOOKS']['generatePage']['huh.head-bundle']  = ['huh.head.listener.hooks', 'generatePage'];