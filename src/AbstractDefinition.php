<?php


namespace TheCodingMachine\Funky;

use Psr\Container\ContainerInterface;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\Annotations\Tag;
use TheCodingMachine\Funky\Injections\ContainerInjection;
use TheCodingMachine\Funky\Injections\Injection;
use TheCodingMachine\Funky\Injections\ServiceInjection;

abstract class AbstractDefinition
{
    /**
     * @var ReflectionMethod
     */
    protected $reflectionMethod;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Tag[]
     */
    protected $tags = [];

    public function __construct(ReflectionMethod $reflectionMethod)
    {
        $this->reflectionMethod = $reflectionMethod;
    }

    /**
     * Returns a list of services to be injected.
     *
     * @return Injection
     */
    protected function mapParameterToInjection(ReflectionParameter $reflectionParameter): Injection
    {
        $type = $reflectionParameter->getType();
        // No type? Let's inject by parameter name.
        if ($type === null || $type->isBuiltin()) {
            return new ServiceInjection($reflectionParameter->getName(), !$reflectionParameter->allowsNull());
        }
        if (((string)$type) === ContainerInterface::class) {
            return new ContainerInjection();
        }
        return new ServiceInjection((string)$type, !$reflectionParameter->allowsNull());
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
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
