<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\HeadTag\Meta;

use HeimrichHannot\HeadBundle\Helper\MetaTagHelper;

class PropertyDescriptionMetaTag extends PropertyMetaTag
{
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
