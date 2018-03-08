<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

namespace HeimrichHannot\HeadBundle\Test\Tag\Misc;

use HeimrichHannot\HeadBundle\Manager\TagManager;
use HeimrichHannot\HeadBundle\Tag\Misc\Title;

class TitleTest
{
    public function testGenerate()
    {
        $manager = new TagManager();
        $tag = new Title($manager);

        $this->assertSame('<title>'.$this->getContent().'</title>', $tag->generate());
    }
}
