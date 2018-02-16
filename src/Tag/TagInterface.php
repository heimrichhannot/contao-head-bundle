<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\HeadBundle\Head;


interface TagInterface
{
    /**
     * Generate the head tag
     * @return string
     */
    public function generate();

    /**
     * Set the tag content value
     * @param string
     */
    public function setContent($content);

    /**
     * Get the tag content value
     * @return string
     */
    public function getContent();

    /**
     * Check if content is set
     * @return bool
     */
    public function hasContent();
}