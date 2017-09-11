<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\HeadBundle\Tag\Simple;

use HeimrichHannot\HeadBundle\Head\AbstractSimpleTag;
use HeimrichHannot\HeadBundle\Head\TagInterface;

class Title extends AbstractSimpleTag implements TagInterface
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