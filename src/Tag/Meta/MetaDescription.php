<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta;

use Contao\Controller;
use Contao\StringUtil;
use HeimrichHannot\HeadBundle\Head\AbstractMetaTag;

class MetaDescription extends AbstractMetaTag
{
    /**
     * The tag name.
     *
     * @var string
     */
    protected static $name = 'description';

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $content = parent::getContent();

        $content = StringUtil::decodeEntities($content);
        $content = Controller::replaceInsertTags($content, false);
        $content = strip_tags($content);
        $content = str_replace("\n", ' ', $content);
        $content = \StringUtil::substr($content, 320);

        return sprintf('<%s %s="%s" content="%s">', static::$tag, static::$key, static::$name, $content);
    }
}
