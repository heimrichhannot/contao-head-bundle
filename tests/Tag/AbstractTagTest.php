<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Test\Tag;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\Head\AbstractTag;
use HeimrichHannot\HeadBundle\Manager\TagManager;

class AbstractTagTest extends ContaoTestCase
{
    public function testHasContent()
    {
        $manager = new TagManager();

        $abstractTag = $this->getMockForAbstractClass(AbstractTag::class, [$manager]);

        $abstractTag->setContent('content');
        $this->assertTrue($abstractTag->hasContent());

        $abstractTag->setContent('');
        $this->assertFalse($abstractTag->hasContent());
    }
}
