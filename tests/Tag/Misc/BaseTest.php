<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Test\Tag\Misc;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use HeimrichHannot\HeadBundle\Tag\Misc\Base;

class BaseTest extends ContaoTestCase
{
    /**
     * test tag generation.
     */
    public function testGenerate()
    {
        $manager = new TagManager();
        $tag = new Base($manager);

        $this->assertSame('<base href="'.$tag->getContent().'">', $tag->generate());
    }
}
