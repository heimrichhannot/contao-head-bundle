<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Manager;

use Contao\Controller;
use Contao\StringUtil;
use HeimrichHannot\HeadBundle\Exception\UnsupportedTagException;
use HeimrichHannot\HeadBundle\HeadTag\AbstractHeadTag;
use HeimrichHannot\HeadBundle\HeadTag\BaseTag;
use HeimrichHannot\HeadBundle\HeadTag\HeadTagFactory;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\HeadTag\TitleTag;
use HeimrichHannot\HeadBundle\Helper\LegacyHelper;
use HeimrichHannot\HeadBundle\Helper\TagHelper;

class HtmlHeadTagManager
{
    private ?BaseTag   $baseTag = null;
    private TagManager $legacyTagManager;
    /** @var MetaTag[] */
    private array          $metaTags = [];
    private HeadTagFactory $headTagFactory;
    private ?TitleTag      $titleTag = null;
    private TagHelper      $tagHelper;

    public function __construct(TagManager $legacyTagManager, HeadTagFactory $headTagFactory, TagHelper $tagHelper)
    {
        $this->legacyTagManager = $legacyTagManager;
        $this->headTagFactory = $headTagFactory;
        $this->tagHelper = $tagHelper;
    }

    public function getTag(string $name): ?AbstractHeadTag
    {
        if ('base' === $name) {
            return $this->getBaseTag();
        }

        if ('title' === $name) {
            return $this->getTitleTag();
        }

        if (str_starts_with($name, 'meta_')) {
            return $this->getMetaTag(substr($name, 5));
        }

        return null;
    }

    public function addTag(AbstractHeadTag $tag): void
    {
        if ($tag instanceof BaseTag) {
            $this->setBaseTag($tag);

            return;
        }

        if ($tag instanceof TitleTag) {
            $this->setTitleTag($tag);
        }

        if ($tag instanceof MetaTag) {
            $this->addMetaTag($tag);

            return;
        }

        throw new UnsupportedTagException('Tag with attributes '.$tag->generateAttributeString().' is currently not supported by HtmlHeadTagManager!');
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

    public function getTitleTag(): ?TitleTag
    {
        return $this->titleTag;
    }

    public function setTitleTag($title): self
    {
        if (\is_string($title)) {
            $title = $this->headTagFactory->createTitleTag($title);
        }

        if (null !== $title && !($title instanceof TitleTag)) {
            throw new \InvalidArgumentException('Method only allow properties of type TitleTag, string or null.');
        }

        $this->titleTag = $title;

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

        if (!\in_array(TitleTag::NAME, $options['skip_tags']) && $this->getTitleTag()) {
            $buffer .= $this->titleTag->generate()."\n";
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

    public function getHeadTagFactory(): HeadTagFactory
    {
        return $this->headTagFactory;
    }

    public function getLegacyTagManager(): TagManager
    {
        return $this->legacyTagManager;
    }

    /**
     * Converts an input-encoded string to plain text UTF-8.
     *
     * Strips or replaces insert tags, strips HTML tags, decodes entities, escapes insert tag braces.
     *
     * Same as HtmlDecoder::inputEncodedToPlainText() of contao 4.13 (with 4.9 adjustments).
     * Should be replaced with contao core version when moving to 4.13+
     *
     * @see StringUtil::revertInputEncoding()
     *
     * @param bool $removeInsertTags True to remove insert tags instead of replacing them
     */
    public function inputEncodedToPlainText(string $val, bool $removeInsertTags = false): string
    {
        if ($removeInsertTags) {
            $val = StringUtil::stripInsertTags($val);
        } else {
            $val = Controller::replaceInsertTags($val);
        }

        $val = strip_tags($val);
        $val = StringUtil::revertInputEncoding($val);

        return str_replace(['{{', '}}'], ['[{]', '[}]'], $val);
    }
}
