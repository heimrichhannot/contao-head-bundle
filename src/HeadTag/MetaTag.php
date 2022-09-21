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

        if ($content) {
            $this->setContent($content);
        }
    }

    public function setName(string $name): self
    {
        $this->setAttribute('name', $name);

        return $this;
    }

    public function getName(): string
    {
        if ($this->hasAttribute('name')) {
            return $this->getAttributes()['name'];
        }

        throw new \Exception('Meta tag must have a name!');
    }

    public function setContent(string $content): self
    {
        $this->setAttribute('content', $content);

        return $this;
    }

    public function getContent(): string
    {
        return $this->getAttributes()['content'] ?? '';
    }

    public function generate(): string
    {
        return sprintf('<meta %s>', $this->generateAttributeString());
    }
}
