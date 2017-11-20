<?php

/*
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\HeadBundle\Manager;

use HeimrichHannot\HeadBundle\Head\TagInterface;

class TagManager
{
    /**
     * @var TagInterface[]
     */
    private $tags = [];

    public function registerTag(TagInterface $tag)
    {
        $this->tags[get_class($tag)] = $tag;
    }

    /**
     * Get the generated tags as array.
     *
     * @return array
     */
    public function getTags()
    {
        $tags = [];

        foreach ($this->tags as $tag) {
            if (!$tag->hasContent()) {
                continue;
            }

            $tags[] = \Contao\StringUtil::stripInsertTags(\Contao\Controller::replaceInsertTags($tag->generate()));
        }

        return $tags;
    }
}
