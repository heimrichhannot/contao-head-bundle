<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Helper;

use Contao\StringUtil;

class MetaTagHelper
{
    public static function prepareDescription($content): string
    {
        $content = StringUtil::decodeEntities($content);
        $content = strip_tags($content);
        $content = str_replace("\n", ' ', $content);
        $content = StringUtil::substr($content, 320);

        return $content;
    }
}
