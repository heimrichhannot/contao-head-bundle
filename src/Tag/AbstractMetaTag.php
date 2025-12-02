<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Head;

use Contao\System;
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
abstract class AbstractMetaTag extends AbstractTag
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
    protected static $key = 'name';

    /**
     * Generate the tag output.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf('<%s %s="%s" content="%s">', static::$tag, static::$key, static::$name, $this->escapeForHtmlAttribute($this->getContent()));
    }

    public function setContent($content): void
    {
        if (isset(static::$name)) {
            if (null !== $content) {
                if ('property' === static::$key) {
                    $tag = new PropertyMetaTag(static::$name, $content);
                } else {
                    $tag = new MetaTag(static::$name, $content);
                }
                System::getContainer()->get(HtmlHeadTagManager::class)->addMetaTag($tag);
            } else {
                if ('property' === static::$key) {
                    $name = str_replace(':', '_', static::$name);
                    System::getContainer()->get(HtmlHeadTagManager::class)->removeMetaTag($name);
                } else {
                    System::getContainer()->get(HtmlHeadTagManager::class)->removeMetaTag(static::$name);
                }
            }
        } else {
            parent::setContent($content);
        }
    }

    public function getContent()
    {
        if (isset(static::$name)) {
            if ($tag = System::getContainer()->get(HtmlHeadTagManager::class)->getMetaTag(static::$name)) {
                return $tag->getContent();
            }

            return null;
        }

        return parent::getContent();
    }
}
