<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Tag\Meta\OG;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\HeadBundle\Head\AbstractMetaTag;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
class OGDescription extends AbstractMetaTag
{
    /**
     * The tag attribute key.
     *
     * @var string
     */
    protected static $key = 'property';

    /**
     * The tag name.
     *
     * @var string
     */
    protected static $name = 'og:description';

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $insertTagParser = System::getContainer()->get('contao.insert_tag.parser');

        $content = parent::getContent();

        $content = StringUtil::decodeEntities($content);
        $content = $insertTagParser->replace($content);
        $content = strip_tags($content);
        $content = str_replace("\n", ' ', $content);
        $content = StringUtil::substr($content, 320);

        return sprintf(
            '<%s %s="%s" content="%s">',
            static::$tag,
            static::$key,
            static::$name,
            $this->escapeForHtmlAttribute($content)
        );
    }
}
