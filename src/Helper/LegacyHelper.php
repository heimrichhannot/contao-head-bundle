<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Helper;

/**
 * @internal
 */
class LegacyHelper
{
    public const SERVICE_MAP = [
        'huh.head.tag.base' => 'base',
        'huh.head.tag.title' => 'title',
        'huh.head.tag.meta_date' => 'meta_date',
        'huh.head.tag.meta_language' => 'meta_language',
        'huh.head.tag.meta_charset' => 'meta_charset',
        'huh.head.tag.meta_title' => 'meta_title',
        'huh.head.tag.meta_description' => 'meta_description',
        'huh.head.tag.meta_keywords' => 'meta_keywords',
        'huh.head.tag.meta_robots' => 'meta_robots',
        'huh.head.tag.og_title' => 'meta_og:title',
        'huh.head.tag.og_type' => 'meta_og:type',
        'huh.head.tag.og_url' => 'meta_og:url',
        'huh.head.tag.og_description' => 'meta_og:description',
        'huh.head.tag.og_image' => 'meta_og:image',
        'huh.head.tag.og_locale' => 'meta_og:locale',
        'huh.head.tag.og_site_name' => 'meta_og:site_name',
        'huh.head.tag.twitter_card' => 'meta_twitter:card',
        'huh.head.tag.twitter_site' => 'meta_twitter:site',
        'huh.head.tag.twitter_creator' => 'meta_twitter:creator',
        'huh.head.tag.twitter_title' => 'meta_twitter:title',
        'huh.head.tag.twitter_description' => 'meta_twitter:description',
        'huh.head.tag.twitter_image' => 'meta_twitter:image',
        'huh.head.tag.twitter_image_alt' => 'meta_twitter:image:alt',
        'huh.head.tag.twitter_player' => 'meta_twitter:player',
        'huh.head.tag.twitter_player_width' => 'meta_twitter:player:width',
        'huh.head.tag.twitter_player_height' => 'meta_twitter:player:height',
        'huh.head.tag.twitter_player_stream' => 'meta_twitter:player:stream',
        'huh.head.tag.twitter_player_stream_content_type' => 'twitter:player:stream:content_type',
        'huh.head.tag.link_prev' => 'link_prev',
        'huh.head.tag.link_next' => 'link_next',
        'huh.head.tag.link_canonical' => 'link_canonical',

        // External bundles
        //        'huh.head.tag.pwa.link_manifest' => 'link_manifest',
        'huh.head.tag.pwa.meta_themecolor' => 'meta_theme-color',
        //        'huh.head.tag.pwa.script' => '',
    ];

    public static function mapTagToService(string $tag): ?string
    {
        if ($service = array_search($tag, static::SERVICE_MAP)) {
            return $service;
        }

        return null;
    }

    public static function mapServiceToTag(string $service, $fallbackValue = null): ?string
    {
        return static::SERVICE_MAP[$service] ?? $fallbackValue;
    }
}
