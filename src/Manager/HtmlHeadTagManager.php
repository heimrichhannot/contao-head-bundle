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
use HeimrichHannot\HeadBundle\HeadTag\Link\CanonicalLink;
use HeimrichHannot\HeadBundle\HeadTag\LinkTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\HeadTag\TitleTag;
use HeimrichHannot\HeadBundle\Helper\LegacyHelper;
use HeimrichHannot\HeadBundle\Helper\TagHelper;

class HtmlHeadTagManager
{
    private ?BaseTag $baseTag = null;
    private TagManager $legacyTagManager;
    /** @var MetaTag[] */
    private array $metaTags = [];
    private array $linkTags = [];
    private HeadTagFactory $headTagFactory;
    private ?TitleTag $titleTag = null;
    private TagHelper $tagHelper;

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

            return;
        }

        if ($tag instanceof MetaTag) {
            $this->addMetaTag($tag);

            return;
        }

        if ($tag instanceof LinkTag) {
            $this->addLinkTag($tag);

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

    public function getLinkTag(string $name): ?LinkTag
    {
        return $this->linkTags[$name] ?? null;
    }

    public function removeLinkTag(string $name): void
    {
        if (isset($this->linkTags[$name])) {
            unset($this->linkTags[$name]);
        }
    }

    /**
     * Set canonical url. If null is passed, the canonical tag will be removed. If canonical tag already exists, it will be overwritten.
     *
     * @return $this
     */
    public function setCanonical(?string $url): self
    {
        if (null === $url) {
            $this->removeLinkTag('canonical');

            return $this;
        }

        $this->addLinkTag(new CanonicalLink($url));

        return $this;
    }

    /**
     * @return LinkTag|CanonicalLink|null
     */
    public function getCanonical(): ?LinkTag
    {
        return $this->getLinkTag('canonical');
    }

    /**
     * Render head tags.
     *
     * Options:
     * - skip_tags: (array) Name of tags to skip. For meta tags, prefix name with meta_, for link tags, prefix name with link_ (except canonical).
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

        foreach ($this->linkTags as $linkTag) {
            if (\in_array('link_'.$linkTag->getName(), $options['skip_tags']) || ('canonical' === $linkTag->getName() && \in_array('canonical', $options['skip_tags']))) {
                unset($options['skip_tags']['link_'.$linkTag->getName()]);

                continue;
            }

            if (\in_array(LegacyHelper::mapTagToService('link_'.$linkTag->getName()), $options['skip_tags'])) {
                unset($options['skip_tags'][LegacyHelper::mapTagToService('link_'.$linkTag->getName())]);

                continue;
            }

            $buffer .= $linkTag->generate()."\n";
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
     *
     * @internal May be removed in a minor version when contao 4.13 is required
     */
    public function inputEncodedToPlainText(string $val, bool $removeInsertTags = false): string
    {
        if ($removeInsertTags) {
            $val = StringUtil::stripInsertTags($val);
        } else {
            $val = Controller::replaceInsertTags($val);
        }

        $val = strip_tags($val);
        $val = $this->revertInputEncoding($val);

        return str_replace(['{{', '}}'], ['[{]', '[}]'], $val);
    }

    /**
     * Convert an input-encoded string back to the raw UTF-8 value it originated from.
     *
     * It handles all Contao input encoding specifics like basic entities and encoded entities.
     *
     * Same as StringUtil::revertInputEncoding() of contao 4.13.
     * Should be replaced with contao core version when moving to 4.13+
     *
     * @internal May be removed in a minor version when contao 4.13 is required
     */
    public function revertInputEncoding(string $strValue): string
    {
        $strValue = StringUtil::restoreBasicEntities($strValue);
        $strValue = StringUtil::decodeEntities($strValue);

        // Ensure valid UTF-8
        if (1 !== preg_match('//u', $strValue)) {
            $substituteCharacter = mb_substitute_character();
            mb_substitute_character(0xFFFD);

            $strValue = mb_convert_encoding($strValue, 'UTF-8', 'UTF-8');

            mb_substitute_character($substituteCharacter);
        }

        return $strValue;
    }

    private function addLinkTag(LinkTag $tag)
    {
        $this->linkTags[$tag->getName()] = $tag;
    }
}
