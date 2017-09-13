<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\HeadBundle\Tag\Misc;

use HeimrichHannot\HeadBundle\Head\AbstractTag;

class Title extends AbstractTag
{
    /**
     * Generate the tag output
     * @return string
     */
    public function generate()
    {
        return sprintf('<title>%s</title>', $this->getContent());
    }
}