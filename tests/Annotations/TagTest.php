<?php

namespace TheCodingMachine\Funky\Annotations;

use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function testDefaults()
    {
        $tag = new Tag([
            'name' => 'foo'
        ]);

        $this->assertSame('foo', $tag->getName());
        $this->assertSame(0.0, $tag->getPriority());
    }
}
