<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta;

use Contao\System;
use HeimrichHannot\HeadBundle\Head\AbstractTag;
use HeimrichHannot\HeadBundle\HeadTag\Meta\CharsetMetaTag;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
class MetaCharset extends AbstractTag
{
    /**
     * The tag type.
     *
     * @var string
     */
    protected static $tag = 'meta';

    /**
     * The tag.
     *
     * @var string
     */
    protected static $key = 'charset';

    /**
     * Generate the tag output.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf('<%s %s="%s">', static::$tag, static::$key, $this->escapeForHtmlAttribute($this->getContent()));
    }

    public function setContent($content)
    {
        if (null !== $content) {
            $tag = new CharsetMetaTag($content);
            System::getContainer()->get(HtmlHeadTagManager::class)->addMetaTag($tag);
        } else {
            System::getContainer()->get(HtmlHeadTagManager::class)->removeMetaTag(static::$name);
        }
    }

    public function getContent()
    {
        if ($tag = System::getContainer()->get(HtmlHeadTagManager::class)->getMetaTag('charset')) {
            return $tag->getAttributes()['charset'] ?? null;
        }

        return null;
    }
}
