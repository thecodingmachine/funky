<?php

namespace TheCodingMachine\Funky;


use Doctrine\Common\Annotations\AnnotationException;
use Interop\Container\Factories\Alias;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Simplex\Container;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\Fixtures\TestServiceProvider;
use TheCodingMachine\Funky\Utils\FileSystem;

class ServiceProviderTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        // Let's purge the cache
        FileSystem::rmdir(sys_get_temp_dir().'/funky_cache');
    }

    public function testFactoriesByType()
    {
        $sp = new class extends ServiceProvider {
            /**
             * @Factory
             */
            public static function createDateTime() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }
        };

        $factories = $sp->getFactories();

        $this->assertArrayHasKey(\DateTimeInterface::class, $factories);
        $this->assertSame([get_class($sp), 'createDateTime'], $factories[\DateTimeInterface::class]);
    }

    public function testFactoriesByMethodName()
    {
        $sp = new class extends ServiceProvider {
            /**
             * @Factory(nameFromMethodName=true)
             */
            public static function now() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }
        };

        $factories = $sp->getFactories();

        $this->assertArrayHasKey('now', $factories);
        $this->assertSame([get_class($sp), 'now'], $factories['now']);
    }

    public function testFactoriesByName()
    {
        $sp = new class extends ServiceProvider {
            /**
             * @Factory(name="foobar")
             */
            public static function now() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }
        };

        $factories = $sp->getFactories();

        $this->assertArrayHasKey('foobar', $factories);
        $this->assertSame([get_class($sp), 'now'], $factories['foobar']);
    }

    public function testFactoriesBadAnnotation()
    {
        $sp = new class extends ServiceProvider {
            /**
             * @Factory(name="foobar", nameFromType=true)
             */
            public static function now() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }
        };

        $this->expectException(AnnotationException::class);
        $sp->getFactories();
    }

    public function testFactoriesAliases()
    {
        $sp = new class extends ServiceProvider {
            /**
             * @Factory(name="foobar",
             *     aliases={"baz"})
             */
            public static function now() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }
        };

        $factories = $sp->getFactories();

        $this->assertArrayHasKey('foobar', $factories);
        $this->assertSame([get_class($sp), 'now'], $factories['foobar']);
        $this->assertArrayHasKey('baz', $factories);
        $this->assertEquals(new Alias('foobar'), $factories['baz']);
    }

    public function testDump()
    {
        $sp = new TestServiceProvider();

        $factories = $sp->getFactories();

        $simplex = new Container();
        $simplex->set(LoggerInterface::class, function() { return new NullLogger(); });
        $simplex->set('foo', 12);
        $simplex->set('bar', 42);

        $this->assertArrayHasKey('testFactory', $factories);
        $result = $factories['testFactory']($simplex);

        $this->assertSame(NullLogger::class.'1242', $result);

        $extensions = $sp->getExtensions();

        $this->assertArrayHasKey('testExtension', $extensions);
        $result = $extensions['testExtension']($simplex, 42);

        $this->assertSame('42'.NullLogger::class.'1242', $result);

    }
}
