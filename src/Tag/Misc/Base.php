<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\HeadBundle\Tag\Misc;

use HeimrichHannot\HeadBundle\Head\AbstractTag;

class Base extends AbstractTag
{
    /**
     * Generate the tag output.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf('<base href="%s">', $this->getContent());
    }
}
