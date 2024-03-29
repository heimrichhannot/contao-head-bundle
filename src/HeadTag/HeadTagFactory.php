<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag;

use HeimrichHannot\HeadBundle\HeadTag\Meta\CharsetMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\HttpEquivMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use HeimrichHannot\HeadBundle\Helper\LegacyHelper;
use HeimrichHannot\HeadBundle\Helper\TagHelper;

class HeadTagFactory
{
    private TagHelper $tagHelper;

    public function __construct(TagHelper $tagHelper)
    {
        $this->tagHelper = $tagHelper;
    }

    /**
     * Create a tag by name.
     * For tags with multiple occurrences like meta, prefix name with tag name,
     * for example meta_ (meta_description, meta_og:title, ...) for meta tags.
     *
     * Options:
     * - skip_legacy_mapping: (bool) do not check for legacy tag service names
     */
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

        if ('title' === $name) {
            return $this->createTitleTag($value);
        }

        if (str_starts_with($name, 'meta_')) {
            return $this->createMetaTag(substr($name, 5), $value);
        }

        return null;
    }

    /**
     * Create title tag instance.
     *
     * $titleTagFormat parameter:
     * You can pass a title tag format like "{{page::pageTitle}} - {{page::rootPageTitle}}".
     * {{page::pageTitle}} or %s will replaced by the passed $pageTitle.
     * Leave empty (null) to use the default contao titleTag format (or a fallback if none defined).
     */
    public function createTitleTag(string $pageTitle, ?string $titleTagFormat = null): TitleTag
    {
        if (null === $titleTagFormat) {
            $titleTagFormat = $this->tagHelper->getContaoTitleTag();
        }

        $titleTagFormat = str_replace('{{page::pageTitle}}', '%s', $titleTagFormat);

        if (!empty($titleTagFormat)) {
            return new TitleTag($pageTitle, $titleTagFormat);
        }

        return new TitleTag($pageTitle);
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
