<?php

namespace HeimrichHannot\HeadBundle\Twig;

use Contao\CoreBundle\Filesystem\Dbafs\UnableToResolveUuidException;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\NewsModel;
use HeimrichHannot\HeadBundle\HeadTag\Meta\PropertyMetaTag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Twig\Extension\RuntimeExtensionInterface;

readonly class IntegrationRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private HtmlDecoder         $htmlDecoder,
        private ContentUrlGenerator $contentUrlGenerator,
        private VirtualFilesystemInterface $filesStorage
    ) {}

    public function getNewsTags(NewsModel|int $model, mixed $figure = null): array
    {
        if (is_int($model)) {
            $model = NewsModel::findById($model);
            if (null === $model) {
                return [];
            }
        }
        $headTags = [
            new PropertyMetaTag('og:title', $this->htmlDecoder->inputEncodedToPlainText((string)$model->headline)),
            new PropertyMetaTag('og:description', $this->htmlDecoder->inputEncodedToPlainText((string)$model->teaser)),
            new PropertyMetaTag('og:url', $this->contentUrlGenerator->generate($model, referenceType: UrlGeneratorInterface::ABSOLUTE_URL)),
        ];

        if ($figure instanceof Figure) {
            $headTags['meta_og:image'] = $figure;
        } elseif ($model->addImage && $model->singleSRC) {
            try {
                $uuid = Uuid::fromBinary($model->singleSRC);
                $headTags[] = new PropertyMetaTag('og:image', (string)$this->filesStorage->generatePublicUri($uuid));
            } catch (\InvalidArgumentException | UnableToResolveUuidException) {
            }
        }

        return $headTags;
    }
}