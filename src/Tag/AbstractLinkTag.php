<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Head;

use Contao\System;
use HeimrichHannot\HeadBundle\HeadTag\Link\CanonicalLink;
use HeimrichHannot\HeadBundle\HeadTag\LinkTag;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
abstract class AbstractLinkTag extends AbstractTag
{
    /**
     * The tag type.
     *
     * @var string
     */
    protected static $tag = 'link';

    /**
     * The tag.
     *
     * @var string
     */
    protected static $key = 'rel';

    public function setContent($content): void
    {
        if (isset(static::$name)) {
            if ('canonical' === static::$name) {
                $tag = new CanonicalLink($content);
            } else {
                $tag = new LinkTag(static::$name, static::$name, $content);
            }
            System::getContainer()->get(HtmlHeadTagManager::class)->addLinkTag($tag);
        } else {
            parent::setContent($content);
        }
    }

    public function getContent(): ?string
    {
        if (isset(static::$name)) {
            $tag = System::getContainer()->get(HtmlHeadTagManager::class)->getLinkTag(static::$name);

            if ($tag) {
                return $tag->getAttributes()['href'];
            }

            return null;
        }

        return parent::getContent();
    }

    /**
     * Generate the tag output.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf('<%s %s="%s" href="%s">', static::$tag, static::$key, static::$name, $this->escapeForHtmlAttribute($this->getContent()));
    }
}
