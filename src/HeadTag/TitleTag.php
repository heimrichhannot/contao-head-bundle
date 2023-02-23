<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag;

class TitleTag extends AbstractHeadTag
{
    public const NAME = 'title';

    private string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function generate(): string
    {
        return sprintf('<title>%s</title>', $this->title);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
