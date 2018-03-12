<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HeadBundle\Test\Tag\Misc;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HeadBundle\Manager\TagManager;
use HeimrichHannot\HeadBundle\Tag\Misc\Title;

class TitleTest extends ContaoTestCase
{
    /**
     * test tag generation.
     */
    public function testGenerate()
    {
        $manager = new TagManager();
        $tag = new Title($manager);

        $this->assertSame('<title>'.$tag->getContent().'</title>', $tag->generate());
    }
}
