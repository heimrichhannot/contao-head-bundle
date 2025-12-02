<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag\Meta;

use HeimrichHannot\HeadBundle\HeadTag\MetaTag;

class HttpEquivMetaTag extends MetaTag
{
    public const TYPE = 'http-equiv';

    private const HttpEquivValues = [
        'refresh',
        'cache-control',
        'expires',
        'Content-Security-Policy',
    ];

    public function __construct(string $httpEquiv, string $content)
    {
        $this->setHttpEquiv($httpEquiv);
        $this->setContent($content);
    }

    public function setName(string $name): self
    {
        return $this;
    }

    public function getName(): string
    {
        return self::TYPE;
    }

    public function setHttpEquiv(string $value): self
    {
        if (!\in_array($value, self::HttpEquivValues)) {
            trigger_error('Invalid value for http-equiv attribute!', \E_USER_WARNING);
        }

        $this->setAttribute(self::TYPE, $value);

        return $this;
    }

    public function getHttpEquiv(): string
    {
        return $this->getAttributes()[self::TYPE] ?? '';
    }
}
