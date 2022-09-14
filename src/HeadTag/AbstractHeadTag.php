<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag;

abstract class AbstractHeadTag
{
    private array $attributes = [];

    /**
     * Generate the tag output.
     */
    abstract public function generate(): string;

    /**
     * Return attributes as html string.
     */
    public function generateAttributeString(): string
    {
        if (empty($this->attributes)) {
            return '';
        }

        $compiled = implode('="%s" ', array_keys($this->attributes)).'="%s"';

        return vsprintf($compiled, array_map('htmlspecialchars', array_values($this->attributes)));
    }

    public function setAttribute(string $attribute, string $value): self
    {
        $this->attributes[$attribute] = $value;

        return $this;
    }

    public function removeAttribute(string $attribute): self
    {
        if (isset($this->attributes[$attribute])) {
            unset($this->attributes[$attribute]);
        }

        return $this;
    }

    public function hasAttribute(string $attribute): bool
    {
        return isset($this->attributes[$attribute]);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
