<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Head;

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

    /**
     * Generate the tag output.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf('<%s %s="%s" href="%s">', static::$tag, static::$key, static::$name, $this->getContent());
    }
}
