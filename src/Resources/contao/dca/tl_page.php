<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$dc = &$GLOBALS['TL_DCA']['tl_page'];

/**
 * palettes
 */
$dc['palettes']['root'] = str_replace(';{sitemap_legend', ';{twitter_legend},twitterSite;{sitemap_legend', $dc['palettes']['root']);

/**
 * fields
 */

$fields = [
    'twitterSite' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['twitterSite'],
        'inputType' => 'text',
        'exclude'   => true,
        'eval'      => ['tl_class' => 'w50'],
        'sql'       => "varchar(255) NOT NULL DEFAULT ''",
    ],
];

$dc['fields'] = array_merge($dc['fields'], $fields);