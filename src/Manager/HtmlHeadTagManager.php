<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Manager;

use HeimrichHannot\HeadBundle\HeadTag\BaseTag;
use HeimrichHannot\HeadBundle\Tag\Misc\Base;

class HtmlHeadTagManager
{
    private ?BaseTag   $baseTag = null;
    private TagManager $legacyTagManager;

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
        $this->setLegacyBaseTag($baseTag);

        return $this;
    }

    public function renderTags(array $options = []): string
    {
        $options = array_merge([
            'skip_tags' => [],
        ], $options);

        $buffer = '';

        if (!\in_array(BaseTag::NAME, $options['skip_tags']) && $this->getBaseTag()) {
            $buffer .= $this->baseTag->generate().'\n';
        }

        return $buffer.implode("\n", $this->legacyTagManager->getTags(array_merge(['base'], $options['skip_tags'])));
    }

    private function setLegacyBaseTag(BaseTag $baseTag = null): void
    {
        if (!$baseTag) {
            $this->legacyTagManager->removeTag(BaseTag::LEGACY_NAME);
        } elseif (!$this->legacyTagManager->hasTag(BaseTag::LEGACY_NAME)) {
            $legacyBaseTag = new Base($this->legacyTagManager);
            $this->legacyTagManager->registerTag($legacyBaseTag);
        }
    }
}
