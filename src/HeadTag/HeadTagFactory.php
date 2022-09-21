<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag;

use HeimrichHannot\HeadBundle\HeadTag\Meta\CharsetMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\HttpEquivMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use HeimrichHannot\HeadBundle\Helper\LegacyHelper;

class HeadTagFactory
{
    public function createTagByName(string $name, string $value = null, array $options = []): ?AbstractHeadTag
    {
        $options = array_merge([
            'skip_legacy_mapping' => false,
        ], $options);

        if (!$options['skip_legacy_mapping']) {
            $name = LegacyHelper::mapServiceToTag($name, $name);
        }

        if ('base' === $name) {
            return new BaseTag($value);
        }

        if (str_starts_with($name, 'meta_')) {
            return $this->createMetaTag(substr($name, 5));
        }

        return null;
    }

    public function createMetaTag(string $name, string $content = null): MetaTag
    {
        if ('charset' === $name) {
            return new CharsetMetaTag($content ?? '');
        }

        if ('http-equiv' === $name) {
            return new HttpEquivMetaTag($content ?? '', '');
        }

        if (str_starts_with($name, 'og:')) {
            return new PropertyMetaTag($name, $content);
        }

        return new MetaTag($name, $content);
    }
}