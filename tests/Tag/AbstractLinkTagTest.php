<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Test\Tag;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\Head\AbstractLinkTag;
use HeimrichHannot\HeadBundle\Manager\TagManager;

class AbstractLinkTagTest extends ContaoTestCase
{
    /**
     * test tag generation.
     */
    public function testGenerate()
    {
        $manager = new TagManager();
        $abstractTag = $this->getMockForAbstractClass(AbstractLinkTag::class, [$manager]);

        $this->assertSame('<link rel="" href="'.$abstractTag->getContent().'">', $abstractTag->generate());
    }
}
