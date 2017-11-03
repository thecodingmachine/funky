<?php

namespace TheCodingMachine\Funky;


use PHPUnit\Framework\TestCase;
use \ReflectionMethod;
use TheCodingMachine\Funky\Annotations\Extension;
use TheCodingMachine\Funky\Fixtures\TestServiceProvider;

class ExtensionDefinitionTest extends TestCase
{
    private function getSp(): ServiceProvider
    {
        return new class extends ServiceProvider {
            /**
             * @Extension
             */
            public function notStatic() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }

            /**
             * @Extension
             */
            private static function notPublic() : \DateTimeInterface
            {
                return new \DateTimeImmutable();
            }

            /**
             * @Extension
             */
            public static function noType()
            {
                return new \DateTimeImmutable();
            }

        };
    }

    public function testNotPublic()
    {
        $this->expectException(BadModifierException::class);
        $refMethod = new ReflectionMethod($this->getSp(), 'notPublic');
        new ExtensionDefinition($refMethod, new Extension([]));
    }

    public function testNotStatic()
    {
        $this->expectException(BadModifierException::class);
        $refMethod = new ReflectionMethod($this->getSp(), 'notStatic');
        new ExtensionDefinition($refMethod, new Extension([]));
    }

    public function testNoType()
    {
        $this->expectException(UnknownTypeException::class);
        $refMethod = new ReflectionMethod($this->getSp(), 'noType');
        new ExtensionDefinition($refMethod, new Extension([]));
    }

    public function testBuildExtension()
    {
        $refMethod = new ReflectionMethod(TestServiceProvider::class, 'testExtension');
        $extensionDefinition = new ExtensionDefinition($refMethod, new Extension([]));

        $code = $extensionDefinition->buildExtensionCode('foo');

        $this->assertSame(<<<EOF
    public static function foo(ContainerInterface \$container, string \$previous): string
    {
        return \TheCodingMachine\Funky\Fixtures\TestServiceProvider::testExtension(\$previous, \$container->get('Psr\\\\Log\\\\LoggerInterface'), \$container, \$container->get('foo'), \$container->has('bar')?\$container->get('bar'):null);
    }
    
EOF
            , $code);
    }

    public function testBuildExtension2()
    {
        $refMethod = new ReflectionMethod(TestServiceProvider::class, 'testExtension2');
        $extensionDefinition = new ExtensionDefinition($refMethod, new Extension([]));

        $code = $extensionDefinition->buildExtensionCode('foo');

        $this->assertSame(<<<EOF
    public static function foo(ContainerInterface \$container, \DateTimeInterface \$previous = NULL): \DateTimeInterface
    {
        return \TheCodingMachine\Funky\Fixtures\TestServiceProvider::testExtension2(\$previous);
    }
    
EOF
            , $code);
    }
}
