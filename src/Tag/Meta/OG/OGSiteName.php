<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta\OG;

use HeimrichHannot\HeadBundle\Head\AbstractMetaTag;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
class OGSiteName extends AbstractMetaTag
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
    protected static $name = 'og:site_name';
}
