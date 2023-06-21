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
$lang['addHeadDefaultImage'] = ['Add fallback image for the meta tags', 'This image is used for og:image and twitter:image.'];
$lang['headDefaultImage'] = ['Fallback image (at least 200x200)', 'Choose an image here. Only PNG and JPG files allowed.'];
$lang['twitterSite'] = ['Twitter @username', 'The Twitter @username a twitter card should be attributed to (twitter:site attribute).'];
$lang['headAddOrganisationSchema'][0] = 'Output @Organization';
$lang['headAddOrganisationSchema'][1] = 'Output the @Organization schema.';
$lang['headOrganisationName'][0] = 'Name';
$lang['headOrganisationName'][1] = 'Enter the name of the organisation.';
$lang['headOrganisationLogo'][0] = 'Logo';
$lang['headOrganisationLogo'][1] = 'Choose a logo.';
$lang['headOrganisationWebsite'][0] = 'URL';
$lang['headOrganisationWebsite'][1] = 'Enter the URL of the organisation.';
$lang['headAddWebSiteSchema'][0] = 'Output @WebSite';
$lang['headAddWebSiteSchema'][1] = 'Output the @WebSite schema.';
$lang['headAddWebPageSchema'][0] = 'Output @WebPage';
$lang['headAddWebPageSchema'][1] = 'Output the @WebPage schema.';

/*
 * Legends
 */
$lang['head_legend'] = 'Head-Bundle';
$lang['head_twitter_legend'] = 'Head-Bundle - Twitter';
$lang['schema_legend'] = 'Structured data (Schema.org)';

if (version_compare(VERSION, '4.13', '<')) {
    $lang['canonical_legend'] = 'Canonical URL';
    $lang['enableCanonical'][0] = 'Enable rel="canonical"';
    $lang['enableCanonical'][1] = 'Add rel=&quot;canonical&quot; tags to the website.';
    $lang['canonicalLink'][0] = 'Custom URL';
    $lang['canonicalLink'][1] = 'Here you can set a custom canonical URL like https://example.com/.';
    $lang['canonicalKeepParams'][0] = 'Query parameters';
    $lang['canonicalKeepParams'][1] = 'By default, Contao strips the query parameters in the canonical URL. Here you can add a comma-separated list of query parameters to preserve. Use &quot;*&quot; as a wildcard.';
}
