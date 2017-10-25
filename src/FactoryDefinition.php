<?php


namespace TheCodingMachine\Funky;


use Psr\Container\ContainerInterface;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\Injections\ContainerInjection;
use TheCodingMachine\Funky\Injections\Injection;
use TheCodingMachine\Funky\Injections\ServiceInjection;

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

    public function buildFactoryCode(string $functionName) : string
    {
        $returnTypeCode = '';
        $returnType = $this->reflectionMethod->getReturnType();
        if ($returnType) {
            if ($returnType->isBuiltin()) {
                $returnTypeCode = ': '.$this->reflectionMethod->getReturnType();
            } else {
                $returnTypeCode = ': \\'.$this->reflectionMethod->getReturnType();
            }
        }

        return sprintf(<<<EOF
    public static function %s(ContainerInterface \$container)%s
    {
        return %s::%s(%s);
    }
    
EOF
            , $functionName,
            $returnTypeCode,
            $this->reflectionMethod->getDeclaringClass()->getName(),
            $this->reflectionMethod->getName(),
            implode(', ', array_map(function(Injection $injection) {return $injection->getCode();}, $this->getInjections()))
        );
    }

    /**
     * Returns a list of services to be injected.
     *
     * @return Injection[]
     */
    private function getInjections(): array
    {
        return array_map(function(ReflectionParameter $reflectionParameter) {
            $type = $reflectionParameter->getType();
            // No type? Let's inject by parameter name.
            if ($type === null || $type->isBuiltin()) {
                return new ServiceInjection($reflectionParameter->getName(), !$reflectionParameter->allowsNull());
            }
            if (((string)$type) === ContainerInterface::class) {
                return new ContainerInjection();
            }
            return new ServiceInjection((string)$type, !$reflectionParameter->allowsNull());
        }, $this->getReflectionMethod()->getParameters());
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