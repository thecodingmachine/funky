<?php


namespace TheCodingMachine\Funky;

use ReflectionMethod;
use TheCodingMachine\Funky\Annotations\Extension;
use TheCodingMachine\Funky\Injections\Injection;

class ExtensionDefinition extends AbstractDefinition
{
    public function __construct(ReflectionMethod $reflectionMethod, Extension $annotation)
    {
        if (!$reflectionMethod->isPublic()) {
            throw BadModifierException::mustBePublic($reflectionMethod, '@Extension');
        }
        if (!$reflectionMethod->isStatic()) {
            throw BadModifierException::mustBeStatic($reflectionMethod, '@Extension');
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

        $this->tags = $annotation->getTags();

        parent::__construct($reflectionMethod);
    }

    public function buildExtensionCode(string $functionName) : string
    {
        $returnTypeCode = '';
        $returnType = $this->reflectionMethod->getReturnType();
        if ($returnType) {
            $allowsNull = $returnType->allowsNull() ? '?':'';
            if ($returnType->isBuiltin()) {
                $returnTypeCode = ': '.$allowsNull.$returnType;
            } else {
                $returnTypeCode = ': '.$allowsNull.'\\'.$returnType;
            }
        }

        $parameters = $this->reflectionMethod->getParameters();

        $previousParameterCode = '';
        $previousTypeCode = '';
        if (count($parameters) >= 1) {
            $previousParameter = $this->reflectionMethod->getParameters()[0];
            $previousType = $previousParameter->getType();
            if ($previousType) {
                if ($previousType->isBuiltin()) {
                    $previousTypeCode = (string) $previousType.' ';
                } else {
                    $previousTypeCode = '\\'.(string) $previousType.' ';
                }
                if ($previousParameter->allowsNull()) {
                    $previousTypeCode = '?'.$previousTypeCode;
                }
            }
            $previousParameterCode = ', '.$previousTypeCode.'$previous';
            if ($previousParameter->isDefaultValueAvailable()) {
                $previousParameterCode .= ' = '.var_export($previousParameter->getDefaultValue(), true);
            } elseif ($previousParameter->allowsNull()) {
                // If a first argument has no default null value but is nullable (because of ?), we still put the null default value.
                $previousParameterCode .= ' = null';
            }
        }


        return sprintf(
            <<<EOF
    public static function %s(ContainerInterface \$container%s)%s
    {
        return %s::%s(%s%s);
    }
    
EOF
            ,
            $functionName,
            $previousParameterCode,
            $returnTypeCode,
            '\\'.$this->reflectionMethod->getDeclaringClass()->getName(),
            $this->reflectionMethod->getName(),
            $previousParameterCode ? '$previous': '',
            implode('', array_map(function (Injection $injection) {
                return ', '.$injection->getCode();
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
        $parameters = $this->getReflectionMethod()->getParameters();
        array_shift($parameters);
        return array_map([$this, 'mapParameterToInjection'], $parameters);
    }
}
