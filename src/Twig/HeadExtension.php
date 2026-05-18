<?php

namespace HeimrichHannot\HeadBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HeadExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('add_head_tag', [HeadRuntime::class, 'addHeadTag']),
            new TwigFunction('add_head_tags', [HeadRuntime::class, 'addHeadTags']),
            new TwigFunction('add_head_meta_tag', [HeadRuntime::class, 'addMetaTag']),
            new TwigFunction('get_head_news_tags', [IntegrationRuntime::class, 'getNewsTags']),
        ];
    }
}