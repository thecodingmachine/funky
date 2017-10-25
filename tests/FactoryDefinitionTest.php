<?php

namespace TheCodingMachine\Funky;


use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use \ReflectionMethod;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\Fixtures\TestServiceProvider;

class FactoryDefinitionTest extends TestCase
{
    private function getSp(): ServiceProvider
    {
        return new class extends ServiceProvider {
            /**
             * @Factory
             */
            public function notStatic() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }

            /**
             * @Factory
             */
            private static function notPublic() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }

            /**
             * @Factory
             */
            public static function noType()
            {
                return new \DateTimeImmutable();
            }

            /**
             * @Factory
             */
            public static function notAPsrFactory($container) : \DateTimeInterface
            {

            }

            /**
             * @Factory
             */
            public static function notAPsrFactory2(ContainerInterface $container, $anotherParameter) : \DateTimeInterface
            {

            }

            /**
             * @Factory
             */
            public static function isAPsrFactory(ContainerInterface $container) : \DateTimeInterface
            {

            }

            /**
             * @Factory
             */
            public static function isAPsrFactory2() : \DateTimeInterface
            {

            }
        };
    }

    public function testNotPublic()
    {
        $this->expectException(BadModifierException::class);
        $refMethod = new ReflectionMethod($this->getSp(), 'notPublic');
        new FactoryDefinition($refMethod, new Factory([]));
    }

    public function testNotStatic()
    {
        $this->expectException(BadModifierException::class);
        $refMethod = new ReflectionMethod($this->getSp(), 'notStatic');
        new FactoryDefinition($refMethod, new Factory([]));
    }

    public function testNoType()
    {
        $this->expectException(UnknownTypeException::class);
        $refMethod = new ReflectionMethod($this->getSp(), 'noType');
        new FactoryDefinition($refMethod, new Factory([]));
    }

    /**
     * @dataProvider factoryTestProvider
     */
    public function testPsrFactoryDetection($methodName, $result)
    {
        $refMethod = new ReflectionMethod($this->getSp(), $methodName);
        $factoryDefinition = new FactoryDefinition($refMethod, new Factory([]));
        $this->assertSame($result, $factoryDefinition->isPsrFactory());
    }

    public function factoryTestProvider()
    {
        return [
            ['notAPsrFactory', false],
            ['notAPsrFactory2', false],
            ['isAPsrFactory', true],
            ['isAPsrFactory2', true],
        ];
    }

    public function testBuildFactory()
    {
        $refMethod = new ReflectionMethod(TestServiceProvider::class, 'testFactory');
        $factoryDefinition = new FactoryDefinition($refMethod, new Factory([]));

        $code = $factoryDefinition->buildFactoryCode('foo');

        $this->assertSame(<<<EOF
    public static function foo(ContainerInterface \$container): \DateTimeInterface
    {
        return TheCodingMachine\Funky\Fixtures\TestServiceProvider::testFactory(\$container->get('Psr\\\\Log\\\\LoggerInterface'), \$container, \$container->get('foo'), \$container->has('bar')?\$container->get('bar'):null);
    }
    
EOF
            , $code);
    }
}
