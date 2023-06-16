<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Link;

use HeimrichHannot\HeadBundle\Head\AbstractLinkTag;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
class LinkPrev extends AbstractLinkTag
{
    /**
     * The tag name.
     *
     * @var string
     */
    protected static $name = 'prev';
}
