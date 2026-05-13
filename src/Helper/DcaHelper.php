<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Helper;

class DcaHelper
{
    public const FILTER_TITLE = 'title';
    public const FILTER_BASE = 'base';
    public const FILTER_META = 'meta';

    private const TAGS_META = [
        'title',
        'description',
        'date',
        'language',
        'charset',
        'keywords',
        'robots',
        'og:title',
        'og:type',
        'og:url',
        'og:description',
        'og:image',
        'og:image:alt',
        'og:locale',
        'og:site_name',
        'twitter:card',
        'twitter:site',
        'twitter:creator',
        'twitter:title',
        'twitter:description',
        'twitter:image',
        'twitter:image:alt',
        'twitter:player',
        'twitter:player:width',
        'twitter:player:height',
        'twitter:player:stream',
    ];

    /**
     * Get available meta tags as options.
     *
     * Options:
     * - filter: (array|null) If set, only tags fulfill given filters will be returned. See FILTER constants for available options. Default null
     * - skip_tags: (array) Skip specific tags. Default empty
     */
    public function getTagOptions(array $options = []): array
    {
        $options = array_merge([
            'filter' => null,
            'skip_tag' => [],
        ], $options);

        $filter = $options['filter'];

        $return = [];

        if (!$filter || \in_array(static::FILTER_TITLE, $filter)) {
            $return['title'] = 'title';
        }

        if (!$filter || \in_array(static::FILTER_BASE, $filter)) {
            $return['base'] = 'base';
        }

        if (!$filter || \in_array(static::FILTER_META, $filter)) {
            foreach (self::TAGS_META as $metaTag) {
                $return['meta_'.$metaTag] = 'Meta '.$metaTag;
            }
        }

        $return = array_diff($return, $options['skip_tag']);

        return $return;
    }
}
