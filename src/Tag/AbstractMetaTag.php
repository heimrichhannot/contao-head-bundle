<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Head;

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
}
