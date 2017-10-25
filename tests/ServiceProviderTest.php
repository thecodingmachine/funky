<?php

namespace TheCodingMachine\Funky;


use Doctrine\Common\Annotations\AnnotationException;
use Interop\Container\Factories\Alias;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\Funky\Annotations\Factory;

class ServiceProviderTest extends TestCase
{
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
}
