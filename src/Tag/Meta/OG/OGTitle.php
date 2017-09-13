<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta\OG;

use HeimrichHannot\HeadBundle\Head\AbstractMetaTag;

class OGTitle extends AbstractMetaTag
{
    /**
     * The tag attribute key
     * @var string
     */
    protected static $key = 'property';


    /**
     * The tag name
     * @var string
     */
    protected static $name = 'og:title';
}