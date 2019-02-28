<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Manager;

use Contao\Controller;
use Contao\StringUtil;
use HeimrichHannot\HeadBundle\Head\TagInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class TagManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var TagInterface[]
     */
    private $tags = [];

    public function registerTag(TagInterface $tag)
    {
        $services = $this->container->getParameter('huh.head.tags');
        $className = \get_class($tag);

        if (!isset($services[$className])) {
            return;
        }

        $this->tags[$services[$className]] = $tag;
    }

    /**
     * Get the generated tags as array.
     *
     * @param array $skip List of service ids that should be skipped
     *
     * @return array
     */
    public function getTags(array $skip = [])
    {
        $tags = [];

        foreach ($this->tags as $service => $tag) {
            if (!$tag->hasContent()) {
                continue;
            }

            if (!empty($skip) && in_array($service, $skip)) {
                continue;
            }

            $tags[] = StringUtil::stripInsertTags(Controller::replaceInsertTags($tag->generate()));
        }

        return $tags;
    }
}
