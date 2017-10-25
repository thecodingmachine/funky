<?php
declare(strict_types=1);

namespace TheCodingMachine\Funky;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Interop\Container\Factories\Alias;
use Interop\Container\ServiceProviderInterface;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\Funky\Annotations\Factory;

class ServiceProvider implements ServiceProviderInterface
{

    private static $annotationReader;

    private static function getAnnotationReader() : AnnotationReader
    {
        if (self::$annotationReader === null) {
            self::$annotationReader = new AnnotationReader();
        }
        return self::$annotationReader;
    }

    /**
     * @return FactoryDefinition[]
     * @throws \TheCodingMachine\Funky\BadModifierException
     */
    private function getFactoryDefinitions(): array
    {
        $refClass = new ReflectionClass($this);
        $factories = [];

        foreach ($refClass->getMethods() as $method) {
            $factoryAnnotation = self::getAnnotationReader()->getMethodAnnotation($method, Factory::class);
            if ($factoryAnnotation) {
                $factories[] = new FactoryDefinition($method, $factoryAnnotation);
            }
        }

        return $factories;
    }

    /**
     * Returns a list of all container entries registered by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the entry, aka the **factory**
     *
     * Factories have the following signature:
     *        function(\Psr\Container\ContainerInterface $container)
     *
     * @return callable[]
     */
    public function getFactories()
    {
        $factories = [];
        foreach ($this->getFactoryDefinitions() as $definition) {
            $factories[$definition->getName()] = [static::class, $definition->getReflectionMethod()->getName()];
            foreach ($definition->getAliases() as $alias) {
                $factories[$alias] = new Alias($definition->getName());
            }
        }

        return $factories;
    }

    /**
     * Returns a list of all container entries extended by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the modified entry
     *
     * Callables have the following signature:
     *        function(Psr\Container\ContainerInterface $container, $previous)
     *     or function(Psr\Container\ContainerInterface $container, $previous = null)
     *
     * About factories parameters:
     *
     * - the container (instance of `Psr\Container\ContainerInterface`)
     * - the entry to be extended. If the entry to be extended does not exist and the parameter is nullable, `null` will be passed.
     *
     * @return callable[]
     */
    public function getExtensions()
    {
        // TODO: Implement getExtensions() method.
    }
}