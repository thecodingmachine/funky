<?php


namespace TheCodingMachine\Funky;


use Psr\Container\ContainerInterface;
use ReflectionMethod;
use TheCodingMachine\Funky\Annotations\Factory;

class FactoryDefinition
{
    /**
     * @var ReflectionMethod
     */
    private $reflectionMethod;
    private $name;
    private $aliases;

    public function __construct(ReflectionMethod $reflectionMethod, Factory $annotation)
    {
        if (!$reflectionMethod->isPublic()) {
            throw BadModifierException::mustBePublic($reflectionMethod, '@Factory');
        }
        if (!$reflectionMethod->isStatic()) {
            throw BadModifierException::mustBeStatic($reflectionMethod, '@Factory');
        }

        $this->reflectionMethod = $reflectionMethod;
        if ($annotation->isFromMethodName()) {
            $this->name = $reflectionMethod->getName();
        } elseif ($annotation->isFromType()) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType === null) {
                throw UnknownTypeException::create($reflectionMethod);
            }
            $this->name = (string) $returnType;
        } else {
            $this->name = $annotation->getName();
        }
        $this->aliases = $annotation->getAliases();
    }

    /**
     * Returns true if the signature of the reflection method is compatible with container-interop/service-provider factories.
     */
    public function isPsrFactory(): bool
    {
        $numberOfParameters = $this->reflectionMethod->getNumberOfParameters();
        if ($numberOfParameters > 1) {
            return false;
        }
        if ($numberOfParameters === 0) {
            return true;
        }
        $parameter = $this->reflectionMethod->getParameters()[0];
        if ($parameter !== null && (string) $parameter->getType() === ContainerInterface::class) {
            return true;
        }
        return false;
    }

    /**
     * Returns a list of services to be injected.
     */
    private function getInjections()
    {

    }

    /**
     * @return ReflectionMethod
     */
    public function getReflectionMethod(): ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }
}