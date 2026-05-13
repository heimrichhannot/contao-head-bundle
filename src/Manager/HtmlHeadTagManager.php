<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Manager;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\String\HtmlAttributes;
use Contao\StringUtil;
use HeimrichHannot\HeadBundle\Exception\UnsupportedTagException;
use HeimrichHannot\HeadBundle\HeadTag\AbstractHeadTag;
use HeimrichHannot\HeadBundle\HeadTag\BaseTag;
use HeimrichHannot\HeadBundle\HeadTag\HeadTagFactory;
use HeimrichHannot\HeadBundle\HeadTag\Link\CanonicalLink;
use HeimrichHannot\HeadBundle\HeadTag\LinkTag;
use HeimrichHannot\HeadBundle\HeadTag\MetaTag;
use HeimrichHannot\HeadBundle\HeadTag\TitleTag;

class HtmlHeadTagManager
{
    private ?BaseTag $baseTag = null;
    /**
     * @var MetaTag[]
     */
    private array $metaTags = [];
    private array $linkTags = [];
    private ?TitleTag $titleTag = null;

    public function __construct(
        private readonly HeadTagFactory $headTagFactory,
        private readonly InsertTagParser $insertTagParser,
        private readonly ResponseContextAccessor $responseContextAccessor,
    ) {
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

    public function setBaseTag(BaseTag|string|null $baseTag): self
    {
        if (\is_string($baseTag)) {
            $baseTag = new BaseTag($baseTag);
        }

        $this->baseTag = $baseTag;

        return $this;
    }

    public function getTitleTag(): ?TitleTag
    {
        $headBag = $this->getHtmlHeadBag();
        if (null !== $headBag) {
            if ('' === $headBag->getTitle()) {
                return null;
            }
            $titleTag = new TitleTag($headBag->getTitle());
            return $titleTag;
        }

        return $this->titleTag;
    }

    public function setTitleTag(string|TitleTag|null $title): self
    {
        $headBag = $this->getHtmlHeadBag();
        if (null !== $headBag) {
            if (null === $title) {
                $title = '';
            }
            $headBag->setTitle(is_string($title) ? $title : $title->getTitle());
            return $this;
        }

        if (\is_string($title)) {
            $title = $this->headTagFactory->createTitleTag($title);
        }

        $this->titleTag = $title;

        return $this;
    }

    public function addMetaTag(MetaTag $metaTag): self
    {
        $headBag = $this->getHtmlHeadBag();

        if ($headBag && 'description' === $metaTag->getName()) {
            $headBag->setMetaDescription($metaTag->getContent());
            return $this;
        }
        if ($headBag && 'robots' === $metaTag->getName()) {
            $headBag->setMetaRobots($metaTag->getContent());
            return $this;
        }

        $this->metaTags[$metaTag->getName()] = $metaTag;

        return $this;
    }

    public function getMetaTag(string $name): ?MetaTag
    {
        $headBag = $this->getHtmlHeadBag();

        if ($headBag && 'description' === $name) {
            return new MetaTag('description', $headBag->getMetaDescription());
        }
        if ($headBag && 'robots' === $name) {
            return new MetaTag('robots', $headBag->getMetaRobots());
        }

        return $this->metaTags[$name] ?? null;
    }

    public function removeMetaTag(string $name): self
    {
        $headBag = $this->getHtmlHeadBag();

        if ($headBag && 'description' === $name) {
            $headBag->setMetaDescription('');
            return $this;
        }
        if ($headBag && 'robots' === $name) {
            $headBag->setMetaRobots('');
            return $this;
        }

        if (isset($this->metaTags[$name])) {
            unset($this->metaTags[$name]);
        }

        return $this;
    }


    public function addLinkTag(LinkTag $tag): self
    {
        if ($tag instanceof CanonicalLink) {
            $headBag = $this->getHtmlHeadBag();
            if (null !== $headBag) {
                $headBag->setCanonicalUri($tag->getHref());
                return $this;
            }
        }

        $this->linkTags[$tag->getName()] = $tag;
        return $this;
    }

    public function getLinkTag(string $name): ?LinkTag
    {
        $headBag = $this->getHtmlHeadBag();

        if ($headBag && 'canonical' === $name) {
            return new CanonicalLink($headBag->getCanonicalUri());
        }

        return $this->linkTags[$name] ?? null;
    }

    public function removeLinkTag(string $name): self
    {
        $headBag = $this->getHtmlHeadBag();

        if ($headBag && 'canonical' === $name) {
            $headBag->setCanonicalUri('');
            return $this;
        }

        if (isset($this->linkTags[$name])) {
            unset($this->linkTags[$name]);
        }

        return $this;
    }

    /**
     * Render head tags.
     *
     * Options:
     * - skip_tags: (array) Name of tags to skip. For meta tags, prefix name with meta_, for link tags, prefix name with link_ (except canonical).
     */
    public function renderTags(array $options = []): string
    {
        $htmlBag = $this->getHtmlHeadBag();

        $options = array_merge([
            'skip_tags' => [],
        ], $options);

        $buffer = '';

        if (!\in_array(BaseTag::NAME, $options['skip_tags']) && $this->getBaseTag()) {
            $buffer .= $this->baseTag->generate()."\n";
        }

        if (!$htmlBag && !\in_array(TitleTag::NAME, $options['skip_tags']) && $this->getTitleTag()) {
            $buffer .= $this->getTitleTag()->generate()."\n";
        }

        foreach ($this->metaTags as $metaTag) {
            if (\in_array('meta_'.$metaTag->getName(), $options['skip_tags'])) {
                unset($options['skip_tags']['meta_'.$metaTag->getName()]);

                continue;
            }

            $buffer .= $metaTag->generate()."\n";
        }

        foreach ($this->linkTags as $linkTag) {
            if (\in_array('link_'.$linkTag->getName(), $options['skip_tags']) || ('canonical' === $linkTag->getName() && \in_array('canonical', $options['skip_tags']))) {
                unset($options['skip_tags']['link_'.$linkTag->getName()]);

                continue;
            }

            $buffer .= $linkTag->generate()."\n";
        }

        return $buffer;
    }

    public function getHeadTagFactory(): HeadTagFactory
    {
        return $this->headTagFactory;
    }

    private function getHtmlHeadBag(): ?HtmlHeadBag
    {
        if ($this->responseContextAccessor->getResponseContext()->has(HtmlHeadBag::class)) {
            return $this->responseContextAccessor->getResponseContext()->get(HtmlHeadBag::class);
        }

        return null;
    }
}
