<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag;

class MetaTag extends AbstractHeadTag
{
    public function __construct(string $name, string $content = null)
    {
        $this->setName($name);
        $this->setContent($content);
    }

    public function setName(?string $name): self
    {
        if (null !== $name) {
            $this->setAttribute('name', $name);
        } else {
            $this->removeAttribute('name');
        }

        return $this;
    }

    public function getName(): string
    {
        if ($this->hasAttribute('name')) {
            return str_replace(':', '_', $this->getAttributes()['name']);
        }
    }

    public function setContent(?string $content): self
    {
        if (null !== $content) {
            $this->setAttribute('content', $content);
        } else {
            $this->removeAttribute('content');
        }

        return $this;
    }

    public function generate(): string
    {
        return sprintf('<meta %s>', $this->generateAttributeString());
    }
}
