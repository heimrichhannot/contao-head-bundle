<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Misc;

use Contao\System;
use HeimrichHannot\HeadBundle\Head\AbstractTag;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;

class Base extends AbstractTag
{
    public function setContent($content)
    {
        System::getContainer()->get(HtmlHeadTagManager::class)->setBaseTag($content);
    }

    /**
     * Generate the tag output.
     *
     * @return string
     */
    public function generate()
    {
        return sprintf('<base href="%s">', $this->escapeForHtmlAttribute($this->getContent()));
    }
}
