<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta\Twitter;

use HeimrichHannot\HeadBundle\Head\AbstractMetaTag;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
class TwitterPlayer extends AbstractMetaTag
{
    /**
     * The tag name.
     *
     * @var string
     */
    protected static $name = 'twitter:player';
}
