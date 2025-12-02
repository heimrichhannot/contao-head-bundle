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
    private string $format;

    public function __construct(string $title, string $format = '%s')
    {
        $this->title = $title;
        $this->format = $format;
    }

    public function generate(): string
    {
        return '<title>' . $this->generateTitle() . '</title>';
    }

    public function generateTitle(): string
    {
        return sprintf($this->format, $this->title);
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

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }
}
