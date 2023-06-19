<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_page'];

/*
 * Fields
 */
$lang['addHeadDefaultImage'] = ['Fallback-Bild für die Meta-Tags hinzufügen', 'Dieses Bild wird bspw. für og:image und twitter:image verwendet.'];
$lang['headDefaultImage'] = ['Fallback-Bild (mind. 200x200)', 'Wählen Sie hier ein Bild aus. Nur PNG- und JPG-Dateien sind erlaubt.'];
$lang['twitterSite'] = ['Twitter @username', 'Der Twitter @username der einer Twitter-Karte zugewiesen werden soll (twitter:site Attribut).'];

/*
 * Legends
 */
$lang['head_legend'] = 'Head-Bundle';
$lang['head_twitter_legend'] = 'Head-Bundle - Twitter';

if (version_compare(VERSION, '4.13', '<')) {
    $lang['canonical_legend'] = 'Kanonische URL';
    $lang['enableCanonical'][0] = 'rel=&quot;canonical&quot; aktivieren';
    $lang['enableCanonical'][1] = 'Der Website rel=&quot;canonical&quot;-Tags hinzufügen.';
    $lang['canonicalLink'][0] = 'Individuelle URL';
    $lang['canonicalLink'][1] = 'Hier können Sie eine individuelle kanonische URL wie z. B. https://example.com/ setzen.';
    $lang['canonicalKeepParams'][0] = 'Query-Parameter';
    $lang['canonicalKeepParams'][1] = 'Standardmäßig entfernt Contao die Query-Parameter in der kanonischen URL. Hier können Sie eine kommagetrennte Liste von Query-Parametern hinzufügen, die erhalten bleiben sollen. Verwenden Sie &quot;*&quot; als Platzhalter.';
}
