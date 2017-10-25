<?php
declare(strict_types=1);

namespace TheCodingMachine\Funky;


use \ReflectionMethod;

class BadModifierException extends \Exception
{

    public static function mustBePublic(ReflectionMethod $reflectionMethod, string $annotation): self
    {
        return new self(sprintf('Method %s::%s annotated with annotation %s must be public.',
            $reflectionMethod->getDeclaringClass()->getName(),
            $reflectionMethod->getName(),
            $annotation));
    }

    public static function mustBeStatic(ReflectionMethod $reflectionMethod, string $annotation): self
    {
        return new self(sprintf('Method %s::%s annotated with annotation %s must be static.',
            $reflectionMethod->getDeclaringClass()->getName(),
            $reflectionMethod->getName(),
            $annotation));
    }
}