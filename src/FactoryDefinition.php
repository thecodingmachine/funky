<?php


namespace TheCodingMachine\Funky;

use Psr\Container\ContainerInterface;
use ReflectionMethod;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\Injections\Injection;

class FactoryDefinition extends AbstractDefinition
{
    private $aliases;

    public function __construct(ReflectionMethod $reflectionMethod, Factory $annotation)
    {
        if (!$reflectionMethod->isPublic()) {
            throw BadModifierException::mustBePublic($reflectionMethod, '@Factory');
        }
        if (!$reflectionMethod->isStatic()) {
            throw BadModifierException::mustBeStatic($reflectionMethod, '@Factory');
        }

        if ($annotation->isFromMethodName()) {
            $this->name = $reflectionMethod->getName();
        } elseif ($annotation->isFromType()) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType === null) {
                throw UnknownTypeException::create($reflectionMethod);
            }
            $this->name = (string) $returnType;
        } else {
            $this->name = (string) $annotation->getName();
        }

        parent::__construct($reflectionMethod);
        $this->aliases = $annotation->getAliases();
    }

    /**
     * Returns true if the signature of the reflection method is compatible with container-interop/service-provider
     * factories.
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
        return $parameter !== null && (string) $parameter->getType() === ContainerInterface::class;
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

        return sprintf(
            <<<EOF
    public static function %s(ContainerInterface \$container)%s
    {
        return %s::%s(%s);
    }
    
EOF
            ,
            $functionName,
            $returnTypeCode,
            '\\'.$this->reflectionMethod->getDeclaringClass()->getName(),
            $this->reflectionMethod->getName(),
            implode(', ', array_map(function (Injection $injection) {
                return $injection->getCode();
            }, $this->getInjections()))
        );
    }

    /**
     * Returns a list of services to be injected.
     *
     * @return Injection[]
     */
    private function getInjections(): array
    {
        return array_map([$this, 'mapParameterToInjection'], $this->getReflectionMethod()->getParameters());
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }
}
