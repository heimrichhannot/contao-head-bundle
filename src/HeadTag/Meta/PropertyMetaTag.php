<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag\Meta;

use HeimrichHannot\HeadBundle\HeadTag\MetaTag;

class PropertyMetaTag extends MetaTag
{
    /**
     * Alias for setProperty.
     */
    public function setName(string $name): self
    {
        return $this->setProperty($name);
    }

    /**
     * Return property value with : replaced by _.
     */
    public function getName(): string
    {
        return str_replace(':', '_', $this->getProperty());
    }

    public function setProperty(?string $name): self
    {
        if (null !== $name) {
            $this->setAttribute('property', $name);
        } else {
            $this->removeAttribute('property');
        }

        return $this;
    }

    public function getProperty(): ?string
    {
        if ($this->hasAttribute('property')) {
            return $this->getAttributes()['property'];
        }

        return null;
    }
}
