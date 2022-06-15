<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag;

class MetaTag extends AbstractHeadTag
{
    public function setName(?string $name): self
    {
        if ($name) {
            $this->addAttribute('name', $name);
        } else {
            $this->removeAttribute('name');
        }

        return $this;
    }

    public function getName(): ?string
    {
        if ($this->hasAttribute('name')) {
            return $this->getAttributes()['name'];
        }

        return null;
    }

    public function setContent(?string $content): self
    {
        if ($content) {
            $this->addAttribute('content', $content);
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
