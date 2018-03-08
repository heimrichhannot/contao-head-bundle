<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\HeadBundle\Manager;

use Contao\Controller;
use Contao\StringUtil;
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

            $tags[] = StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->generate()));
        }

        return $tags;
    }
}
