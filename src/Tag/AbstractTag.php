<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Head;

use HeimrichHannot\HeadBundle\Manager\TagManager;

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
     *
     * @param TagManager $manager
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
    public function setContent($content)
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
        return '' !== $this->getContent();
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
}
