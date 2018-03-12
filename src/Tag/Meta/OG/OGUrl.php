<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta\OG;

use HeimrichHannot\HeadBundle\Head\AbstractMetaTag;

class OGUrl extends AbstractMetaTag
{
    /**
     * The tag attribute key.
     *
     * @var string
     */
    protected static $key = 'property';

    /**
     * The tag name.
     *
     * @var string
     */
    protected static $name = 'og:url';
}
