<?php


namespace TheCodingMachine\Funky;

class UnknownTypeException extends \Exception
{
    public static function create(\ReflectionMethod $reflectionMethod): self
    {
        return new self(sprintf(
            'Cannot give a name to service of method %s::%s because it has no declared return type.',
            $reflectionMethod->getDeclaringClass()->getName(),
            $reflectionMethod->getName()
        ));
    }
}
