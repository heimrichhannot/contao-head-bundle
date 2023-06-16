<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag;

class LinkTag extends AbstractHeadTag
{
    private string $name;

    /*
     * @param string $name An internal name of the link tag to identify it. Will not be used in the resulting code.
     */
    public function __construct(string $name, string $rel, string $href = null)
    {
        $this->name = $name;
        $this->setAttribute('rel', $rel);
        $this->setAttribute('href', $href);
    }

    public function generate(): string
    {
        return sprintf('<link %s>', $this->generateAttributeString());
    }

    /**
     * An internal name of the link tag to identify it. Will not be used in the resulting code.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * An internal name of the link tag to identify it. Will not be used in the resulting code.
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
