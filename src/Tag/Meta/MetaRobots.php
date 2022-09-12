<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta;

use HeimrichHannot\HeadBundle\Head\AbstractMetaTag;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
class MetaRobots extends AbstractMetaTag
{
    /**
     * The tag name.
     *
     * @var string
     */
    protected static $name = 'robots';
}
