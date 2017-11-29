<?php


namespace TheCodingMachine\Funky\Fixtures;


use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use TheCodingMachine\Funky\Annotations\Extension;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\Annotations\Tag;
use TheCodingMachine\Funky\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    /**
     * @Factory(nameFromMethodName=true)
     */
    public static function testFactory(LoggerInterface $logger, ContainerInterface $container, int $foo, $bar) : string
    {
        return get_class($logger).$foo.$bar;
    }

    /**
     * @Extension(nameFromMethodName=true)
     */
    public static function testExtension(string $previous, LoggerInterface $logger, ContainerInterface $container, int $foo, $bar) : string
    {
        return $previous.get_class($logger).$foo.$bar;
    }

    /**
     * @Extension(nameFromMethodName=true)
     */
    public static function testExtension2(\DateTimeInterface $previous = null) : \DateTimeInterface
    {
        return $previous;
    }

    /**
     * @Factory(
     *     name="fooService",
     *     tags={@Tag(name="mytag1", priority=1.0), @Tag(name="mytag2")}
     * )
     */
    public static function taggedService() : string
    {
        return 'foo';
    }

    /**
     * @Factory(
     *     name="barService",
     *     tags={@Tag(name="mytag1", priority=2.0)}
     * )
     */
    public static function taggedService2() : string
    {
        return 'baz';
    }

    /**
     * @Extension(
     *     name="barService",
     *     tags={@Tag(name="mytag3")}
     * )
     */
    public static function taggedService3() : string
    {
        return 'bar';
    }

    /**
     * @Extension(
     *     name="extendNonExistent"
     * )
     */
    public static function extendNotExistent(string $value = 'foo') : string
    {
        return $value;
    }
}
