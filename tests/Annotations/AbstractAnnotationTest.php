<?php

namespace TheCodingMachine\Funky\Annotations;

use Doctrine\Common\Annotations\AnnotationException;
use PHPUnit\Framework\TestCase;

class AbstractAnnotationTest extends TestCase
{
    public function testExceptions()
    {
        $this->expectException(AnnotationException::class);
        new Extension([
            'nameFromType' => true,
            'nameFromMethodName' => true,
        ]);
    }
}
