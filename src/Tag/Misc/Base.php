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

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
class Base extends AbstractTag
{
    public function setContent($content)
    {
        System::getContainer()->get(HtmlHeadTagManager::class)->setBaseTag($content);
    }

    public function getContent()
    {
        if (($baseTag = System::getContainer()->get(HtmlHeadTagManager::class)->getBaseTag())
            && $baseTag->hasAttribute('href')) {
            return $baseTag->getAttributes()['href'];
        }

        return null;
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
