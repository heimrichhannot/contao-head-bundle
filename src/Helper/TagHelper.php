<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Helper;

use Contao\Controller;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

class TagHelper
{
    private Utils $utils;

    public function __construct(Utils $utils)
    {
        $this->utils = $utils;
    }

    public function prepareDescription(string $description): string
    {
        $description = Controller::replaceInsertTags($description);
        $description = strip_tags($description);
        $description = str_replace(["\n", "\r", '"'], [' ', '', ''], $description);
        $description = StringUtil::substr($description, 320);

        return $description;
    }

    public function getContaoTitleTag(PageModel $pageModel = null): string
    {
        if (null === $pageModel) {
            $pageModel = $this->utils->request()->getCurrentPageModel();
        }

        $pageModel->loadDetails();

        $layoutModel = LayoutModel::findByPk($pageModel->layout);

        if (!$layoutModel || !$layoutModel->titleTag) {
            $titleTag = $this->getFallbackPageTitle($pageModel);
        } else {
            $titleTag = $layoutModel->titleTag;
        }

        return $titleTag;
    }

    public function getFallbackPageTitle(PageModel $pageModel = null): string
    {
        if (null === $pageModel) {
            $pageModel = $this->utils->request()->getCurrentPageModel();
        }

        $titleTag = '{{page::pageTitle}} - {{page::rootPageTitle}}';

        if ($this->utils->request()->isIndexPage($pageModel) && !$pageModel->pageTitle) {
            $titleTag = '{{page::rootPageTitle}}';
        }

        return Controller::replaceInsertTags($titleTag);
    }
}
