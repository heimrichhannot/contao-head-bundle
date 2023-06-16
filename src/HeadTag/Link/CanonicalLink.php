<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag\Link;

use HeimrichHannot\HeadBundle\HeadTag\LinkTag;

class CanonicalLink extends LinkTag
{
    const TYPE = 'canonical';
    const LEGACY_NAME = 'huh.head.tag.link.canonical';

    public function __construct(string $href)
    {
        parent::__construct(self::TYPE, self::TYPE, $href);
    }
}
