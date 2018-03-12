<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dca = &$GLOBALS['TL_DCA']['tl_page'];

/**
 * palettes
 */
$dca['palettes']['root'] = str_replace(';{sitemap_legend', ';{head_legend},addHeadDefaultImage;{head_twitter_legend},twitterSite;{sitemap_legend', $dca['palettes']['root']);

/**
 * Subpalettes
 */
$dca['palettes']['__selector__'][] = 'addHeadDefaultImage';
$dca['subpalettes']['addHeadDefaultImage'] = 'headDefaultImage';

/**
 * fields
 */

$fields = [
    'addHeadDefaultImage' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['addHeadDefaultImage'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true],
        'sql'       => "char(1) NOT NULL default ''"
    ],
    'headDefaultImage'    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['headDefaultImage'],
        'exclude'   => true,
        'inputType' => 'fileTree',
        'eval'      => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'jpg,jpeg,png', 'mandatory' => true],
        'sql'       => "binary(16) NULL"
    ],
    'twitterSite'         => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['twitterSite'],
        'inputType' => 'text',
        'exclude'   => true,
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "varchar(255) NOT NULL DEFAULT ''",
    ],
];

$dca['fields'] = array_merge($dca['fields'], $fields);