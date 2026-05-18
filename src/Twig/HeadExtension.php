<?php

namespace HeimrichHannot\HeadBundle\Twig;

use Contao\NewsModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HeadExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        $functions = [
            new TwigFunction('add_head_tag', [HeadRuntime::class, 'addHeadTag']),
            new TwigFunction('add_head_tags', [HeadRuntime::class, 'addHeadTags']),
            new TwigFunction('add_head_meta_tag', [HeadRuntime::class, 'addMetaTag']),
        ];

        if (class_exists(NewsModel::class)) {
            $functions[] = new TwigFunction('get_head_news_tags', [IntegrationRuntime::class, 'getNewsTags']);
        }

        return $functions;
    }
}
