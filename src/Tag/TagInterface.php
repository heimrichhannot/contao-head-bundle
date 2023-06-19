<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Head;

/**
 * @deprecated Use HtmlHeadTagManager service instead
 */
interface TagInterface
{
    /**
     * Generate the head tag.
     *
     * @return string
     */
    public function generate();

    /**
     * Set the tag content value.
     *
     * @param string
     */
    public function setContent($content);

    /**
     * Get the tag content value.
     *
     * @return string
     */
    public function getContent();

    /**
     * Check if content is set.
     *
     * @return bool
     */
    public function hasContent();
}
