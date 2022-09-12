<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag\Meta;

use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\Helper\MetaTagHelper;

class DescriptionMetaTag extends MetaTag
{
    public const TYPE = 'description';

    public function __construct(string $name = self::TYPE, string $content = null)
    {
        parent::__construct($name, $content);
    }

    public function generateAttributeString(): string
    {
        $description = $this->getAttributes()['content'] ?? null;

        if ($description) {
            $this->setAttribute('content', MetaTagHelper::prepareDescription($description));
        }

        $buffer = parent::generateAttributeString();

        $this->setAttribute('content', $description);

        return $buffer;
    }
}
