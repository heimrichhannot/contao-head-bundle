<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
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
     * Get the generated tags as array
     * @return array
     */
    public function getTags()
    {
        $tags = [];

        foreach ($this->tags as $tag) {
            if (!$tag->hasContent()) {
                continue;
            }

            $tags[] = $tag->generate();
        }

        return $tags;
    }
}