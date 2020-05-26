<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta;

use HeimrichHannot\HeadBundle\Head\AbstractTag;

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
}
