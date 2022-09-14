<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Helper;

use Contao\Controller;
use Contao\StringUtil;

class TagHelper
{
    public function prepareDescription(string $description): string
    {
        $description = Controller::replaceInsertTags($description);
        $description = strip_tags($description);
        $description = str_replace(["\n", "\r", '"'], [' ', '', ''], $description);
        $description = StringUtil::substr($description, 320);

        return $description;
    }
}
