<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Misc;

use Contao\Config;
use HeimrichHannot\HeadBundle\Head\AbstractTag;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
class Title extends AbstractTag
{
    /**
     * Generate the tag output.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf(
            '<title>%s</title>',
            htmlentities($this->getContent(), ENT_COMPAT, Config::get('characterSet'))
        );
    }
}
