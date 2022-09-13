<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Manager;

use HeimrichHannot\HeadBundle\HeadTag\BaseTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\Helper\LegacyHelper;

class HtmlHeadTagManager
{
    private ?BaseTag   $baseTag = null;
    private TagManager $legacyTagManager;
    /** @var MetaTag[] */
    private array $metaTags = [];

    public function __construct(TagManager $legacyTagManager)
    {
        $this->legacyTagManager = $legacyTagManager;
    }

    public function getBaseTag(): ?BaseTag
    {
        return $this->baseTag;
    }

    /**
     * @param BaseTag|string|null $baseTag
     */
    public function setBaseTag($baseTag): self
    {
        if (\is_string($baseTag)) {
            $baseTag = new BaseTag($baseTag);
        }

        if (null !== $baseTag && !($baseTag instanceof BaseTag)) {
            throw new \InvalidArgumentException('Method only allow properties of type BaseTag, string or null.');
        }
        $this->baseTag = $baseTag;

        return $this;
    }

    public function addMetaTag(MetaTag $metaTag): self
    {
        $this->metaTags[$metaTag->getName()] = $metaTag;

        return $this;
    }

    public function getMetaTag(string $name): ?MetaTag
    {
        return $this->metaTags[$name] ?? null;
    }

    public function removeMetaTag(string $name): void
    {
        if (isset($this->metaTags[$name])) {
            unset($this->metaTags[$name]);
        }
    }

    /**
     * Render head tags.
     *
     * Options:
     * - skip_tags: (array) Name of tags to skip. For meta tags, prefix name with meta_
     */
    public function renderTags(array $options = []): string
    {
        $options = array_merge([
            'skip_tags' => [],
        ], $options);

        $buffer = '';

        if (!\in_array(BaseTag::NAME, $options['skip_tags']) && $this->getBaseTag()) {
            $buffer .= $this->baseTag->generate()."\n";
        }

        foreach ($this->metaTags as $metaTag) {
            if (\in_array('meta_'.$metaTag->getName(), $options['skip_tags'])) {
                unset($options['skip_tags']['meta_'.$metaTag->getName()]);

                continue;
            }

            if (\in_array(LegacyHelper::mapTagToService('meta_'.$metaTag->getName()), $options['skip_tags'])) {
                unset($options['skip_tags'][LegacyHelper::mapTagToService('meta_'.$metaTag->getName())]);

                continue;
            }
            $buffer .= $metaTag->generate()."\n";
        }

        return $buffer.implode("\n", $this->legacyTagManager->getTags(array_merge(
            array_keys(LegacyHelper::SERVICE_MAP),
            $options['skip_tags']
        )));
    }
}
