<?php

namespace HeimrichHannot\HeadBundle\Twig;

use Contao\CoreBundle\Image\Studio\Figure;
use HeimrichHannot\HeadBundle\Manager\HtmlHeadTagManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class HeadRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly HtmlHeadTagManager $tagManager,
        private readonly RequestStack $requestStack,
    ) {}

    public function addHeadTag(string $name, string|Figure|null $value = null): void
    {
        if ($value instanceof Figure) {
            $value = ($this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost() ?: '').'/'.$value->getImage()->getImageSrc(true);
        }

        $this->tagManager->addTag(
            $this->tagManager->getHeadTagFactory()->createTagByName($name, $value)
        );
    }

    public function addMetaTag(string $name, string|Figure|null $value = null): void
    {
        $this->addHeadTag('meta_' . $name, $value);
    }
}