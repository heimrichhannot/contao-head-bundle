<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Manager;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\StringUtil;
use HeimrichHannot\HeadBundle\Head\TagInterface;
use Psr\Container\ContainerInterface;

/**
 * @deprecated Use HeadTagManager instead. Will be removed in next major version.
 */
class TagManager
{
    /**
     * @var TagInterface[]
     */
    private $tags = [];
    /**
     * @var ContainerInterface
     */
    private $container;
    private InsertTagParser $insertTagParser;

    /**
     * TagManager constructor.
     */
    public function __construct(ContainerInterface $container, InsertTagParser $insertTagParser)
    {
        $this->container = $container;
        $this->insertTagParser = $insertTagParser;
    }

    public function registerTag(TagInterface $tag): void
    {
        $services = $this->container->getParameter('huh.head.tags');

        $className = get_class($tag);

        if (!isset($services[$className])) {
            return;
        }

        $this->tags[$services[$className]] = $tag;
    }

    public function hasTag(string $name): bool
    {
        return isset($this->tags[$name]);
    }

    public function getTagInstance(string $name): ?TagInterface
    {
        return $this->tags[$name] ?? null;
    }

    public function removeTag(string $name): void
    {
        if (isset($this->tags[$name])) {
            unset($this->tags[$name]);
        }
    }

    public function loadTagFromService(string $name): ?TagInterface
    {
        $services = $this->container->getParameter('huh.head.tags');

        if (!in_array($name, $services)) {
            return null;
        }

        if ($this->container->has($name)) {
            return $this->container->get($name);
        }

        return null;
    }

    /**
     * Get the generated tags as array.
     *
     * @param array $skip List of service ids that should be skipped
     *
     * @return array
     *
     * @deprecated Use HeadTagManager::renderTags instead
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

            $tags[] = StringUtil::stripInsertTags($this->insertTagParser->replace($tag->generate()));
        }

        return $tags;
    }
}
