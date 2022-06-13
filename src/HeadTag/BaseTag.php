<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag;

class BaseTag extends AbstractHeadTag
{
    const NAME = 'base';

    public function __construct(string $href)
    {
        $this->addAttribute('href', $href);
    }

    public function generate(): string
    {
        return sprintf('<base %s>', $this->generateAttributeString());
    }
}
