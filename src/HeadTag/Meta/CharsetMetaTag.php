<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag\Meta;

use HeimrichHannot\HeadBundle\HeadTag\MetaTag;

class CharsetMetaTag extends MetaTag
{
    public const TYPE = 'charset';

    public function __construct(string $content = null)
    {
        parent::__construct(static::TYPE, $content);
    }

    public function getName(): ?string
    {
        return static::TYPE;
    }

    public function setName(?string $name): self
    {
        return $this;
    }

    public function setContent(?string $content): self
    {
        return $this->setAttribute(static::TYPE, $content);
    }
}
