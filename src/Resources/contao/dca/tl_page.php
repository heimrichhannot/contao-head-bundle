<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$dca = &$GLOBALS['TL_DCA']['tl_page'];

/*
 * palettes
 */
PaletteManipulator::create()
    ->addField('addHeadDefaultImage', 'meta_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('twitterSite', 'meta_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page');

PaletteManipulator::create()
    ->addField('addHeadDefaultImage', 'meta_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('regular', 'tl_page');

PaletteManipulator::create()
    ->addLegend('schema_legend', 'meta_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('headAddOrganisationSchema', 'schema_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('root', 'tl_page')
    ->applyToPalette('rootfallback', 'tl_page');

/*
 * Subpalettes
 */
$dca['palettes']['__selector__'][] = 'addHeadDefaultImage';
$dca['palettes']['__selector__'][] = 'headAddOrganisationSchema';
$dca['subpalettes']['addHeadDefaultImage'] = 'headDefaultImage';
$dca['subpalettes']['headAddOrganisationSchema'] = 'headOrganisationName,headOrganisationWebsite,headOrganisationLogo';

/**
 * fields.
 */
$fields = [
    'addHeadDefaultImage' => [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['addHeadDefaultImage'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['submitOnChange' => true, 'tl_class' => 'w50 clr'],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'headDefaultImage' => [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['headDefaultImage'],
        'exclude' => true,
        'inputType' => 'fileTree',
        'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'jpg,jpeg,png', 'mandatory' => true],
        'sql' => 'binary(16) NULL',
    ],
    'twitterSite' => [
        'label' => &$GLOBALS['TL_LANG']['tl_page']['twitterSite'],
        'inputType' => 'text',
        'exclude' => true,
        'eval' => ['tl_class' => 'w50 clr'],
        'sql' => "varchar(255) NOT NULL DEFAULT ''",
    ],
    'headAddOrganisationSchema' => [
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => [
            'tl_class' => 'w50 clr',
            'submitOnChange' => true,
        ],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'headOrganisationName' => [
        'inputType' => 'text',
        'exclude' => true,
        'eval' => [
            'tl_class' => 'w50',
            'maxlength' => 128,
        ],
        'sql' => "varchar(128) NOT NULL DEFAULT ''",
    ],
    'headOrganisationWebsite' => [
        'inputType' => 'text',
        'exclude' => true,
        'eval' => [
            'tl_class' => 'w50',
            'maxlength' => 128,
            'rgxp' => 'url',
        ],
        'sql' => "varchar(128) NOT NULL DEFAULT ''",
    ],
    'headOrganisationLogo' => [
        'inputType' => 'fileTree',
        'exclude' => true,
        'eval' => ['tl_class' => 'w50 clr', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'jpg,jpeg,png'],
        'sql' => 'binary(16) NULL',
    ],
];

$dca['fields'] = array_merge($dca['fields'], $fields);
