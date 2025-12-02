<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Head;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\HeadBundle\Manager\TagManager;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
abstract class AbstractTag implements TagInterface
{
    /**
     * The tag type.
     *
     * @var string
     */
    protected static $tag;

    /**
     * The tag.
     *
     * @var string
     */
    protected static $key;

    /**
     * The tag name.
     *
     * @var string
     */
    protected static $name;

    /**
     * The tag value.
     *
     * @var string
     */
    protected $content;

    /**
     * @var TagManager
     */
    protected $manager;

    /**
     * initialize the object.
     */
    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
        $this->manager->registerTag($this);
    }

    /**
     * The tag content value.
     *
     * @param string
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * Check if content is set.
     *
     * @return bool
     */
    public function hasContent()
    {
        return !empty($this->getContent());
    }

    /**
     * Get the tag content value.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Generate the tag output.
     *
     * @return string
     */
    abstract public function generate();

    /**
     * Escapes double quotes.
     *
     * @param $content
     *
     * @return mixed
     */
    public function escapeForHtmlAttribute($content)
    {
        $insertTagParser = System::getContainer()->get('contao.insert_tag.parser');
        return str_replace('"', '&quot;', StringUtil::stripInsertTags($insertTagParser->replace($content)));
    }
}
