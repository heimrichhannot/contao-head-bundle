<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Test\Tag;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\Head\AbstractMetaTag;
use HeimrichHannot\HeadBundle\Manager\TagManager;

class AbstractMetaTagTest extends ContaoTestCase
{
    /**
     * test tag generation.
     */
    public function testGenerate()
    {
        $manager = new TagManager();
        $abstractTag = $this->getMockForAbstractClass(AbstractMetaTag::class, [$manager]);

        $this->assertSame('<meta name="" content="'.$abstractTag->getContent().'">', $abstractTag->generate());
    }
}
