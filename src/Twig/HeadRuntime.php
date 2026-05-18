<?php

namespace HeimrichHannot\HeadBundle\Twig;

use Contao\CoreBundle\Image\Studio\Figure;
use HeimrichHannot\HeadBundle\HeadTag\AbstractHeadTag;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class HeadRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly HtmlHeadTagManager $tagManager,
        private readonly RequestStack $requestStack,
    ) {}

    public function addHeadTag(string|AbstractHeadTag $name, string|Figure|null $value = null): void
    {
        if ($name instanceof AbstractHeadTag) {
            $this->tagManager->addTag($name);
            return;
        }

        if ($value instanceof Figure) {
            $value = ($this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost() ?: '').'/'.$value->getImage()->getImageSrc(true);
        }

        $tag = $this->tagManager->getHeadTagFactory()->createTagByName($name, $value);
        if ($tag instanceof AbstractHeadTag) {
            $this->tagManager->addTag($tag);
        }
    }

    public function addHeadTags(array $tags): void
    {
        foreach ($tags as $key => $value) {
            if ($value instanceof AbstractHeadTag) {
                $this->addHeadTag($value);
                continue;
            }
            $this->addHeadTag($key, $value);
        }
    }

    public function addMetaTag(string $name, string|Figure|null $value = null): void
    {
        $this->addHeadTag('meta_' . $name, $value);
    }
}